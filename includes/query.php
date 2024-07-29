<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

add_filter( 'request', 'stm_listings_query_vars' );

function stm_listings_query_vars( $query_vars ) {
	if ( ! empty( $query_vars['post_type'] ) && 'product' === $query_vars['post_type'] ) {
		return $query_vars;
	}

	$is_listing = isset( $query_vars['post_type'] ) && in_array( apply_filters( 'stm_listings_post_type', 'listings' ), (array) $query_vars['post_type'], true );

	/* Include search */
	$include_search = stm_listings_search_inventory();
	if ( true === $include_search && ! empty( $_GET['s'] ) ) {
		$is_listing = true;
	}

	if ( isset( $query_vars['pagename'] ) ) {
		$listing_id = stm_listings_user_defined_filter_page();
		if ( $listing_id ) {
			$requested = get_page_by_path( $query_vars['pagename'] );
			if ( ! empty( $requested ) && $is_listing && $listing_id === $requested->ID ) {
				unset( $query_vars['pagename'] );
			}
		}
	}

	if ( ! empty( $_GET['ajax_action'] ) && 'listings-result' === $_GET['ajax_action'] ) {
		unset( $query_vars['pagename'] );
		unset( $query_vars['page_id'] );
		$is_listing = true;
	}

	if ( $is_listing && ! is_admin() && ! isset( $query_vars['listings'] ) ) {
		$query_vars = apply_filters( 'stm_listings_query_vars', _stm_listings_build_query_args( $query_vars ) );
	}

	return $query_vars;
}

add_action( 'template_redirect', 'stm_listings_template_redirect', 0 );

function stm_listings_template_redirect() {
	if ( is_feed() ) {
		return;
	}

	if ( stm_listings_user_defined_filter_page() === get_the_ID() ) {
		if ( is_post_type_archive( 'listings' ) ) {
			$GLOBALS['listings_query'] = $GLOBALS['wp_the_query'];
			$query                     = new WP_Query( 'pagename=' . get_page_uri( get_the_ID() ) );
			$GLOBALS['wp_query']       = $query;
			$GLOBALS['wp_the_query']   = $query;
			$GLOBALS['wp']->register_globals();

			if ( apply_filters( 'stm_is_magazine', false ) ) {
				add_filter( 'body_class', 'stm_listing_magazine_body_class' );
			}
		}
	}
}

/**
 * Get current listings query
 *
 * @return WP_Query
 */
function stm_listings_query( $source = null ) {
	$new_query = '';
	if ( isset( $GLOBALS['listings_query'] ) && is_null( $source ) ) {
		$new_query = $GLOBALS['listings_query'];
	} else {
		$query_attr = _stm_listings_build_query_args(
			array(
				'paged' => stm_listings_paged_var(),
			),
			$source
		);

		if ( ! empty( $source ) ) {
			foreach ( $source as $k => $val ) {
				$query_attr[ $k ] = $val;
			}
		}

		$new_query = new WP_Query( $query_attr );

		$GLOBALS['listings_query'] = $new_query;
	}

	return $new_query;
}

add_filter( 'stm_listings_query', 'stm_listings_query', 10, 1 );


add_filter( 'posts_clauses_request', 'stm_listings_posts_clauses', 100, 2 );
add_filter( 'stm_listings_clauses_filter', 'stm_theme_clauses_filter', 10, 2 );

function stm_listings_posts_clauses( $clauses, WP_Query $query ) {
	if ( ! $query->get( 'filter_location' ) || ! apply_filters( 'stm_listings_input', null, 'stm_lat' ) || ! apply_filters( 'stm_listings_input', null, 'stm_lng' ) ) {
		return $clauses;
	}

	$enable_location = apply_filters( 'stm_enable_location', false );
	$enable_distance = apply_filters( 'stm_me_get_nuxy_mod', true, 'enable_distance_search' );

	if ( $enable_location && ( $enable_distance || $query->get( 'enable_distance_search' ) ) ) {
		$formula = '6371 *
		ACOS(COS(RADIANS(:lng)) * COS(RADIANS(stm_lng_prefix.meta_value)) *
		COS(RADIANS(stm_lat_prefix.meta_value) - RADIANS(:lat)) + SIN(RADIANS(:lng)) *
		SIN(RADIANS(stm_lng_prefix.meta_value))) * :convert_km_to_miles';
		$formula = strtr(
			$formula,
			array(
				':lat'                 => floatval( apply_filters( 'stm_listings_input', null, 'stm_lat' ) ),
				':lng'                 => floatval( apply_filters( 'stm_listings_input', null, 'stm_lng' ) ),
				':convert_km_to_miles' => ( 'miles' === apply_filters( 'stm_distance_measure_unit_value', '' ) ) ? 0.621371 : 1,
			)
		);

		$clauses['fields'] .= ", ($formula) AS stm_distance";

		global $wpdb;
		$table_prefix = $wpdb->prefix;

		$clauses['join'] .= " INNER JOIN {$table_prefix}postmeta stm_lat_prefix ON ({$table_prefix}posts.ID = stm_lat_prefix.post_id AND stm_lat_prefix.meta_key = 'stm_lat_car_admin')";
		$clauses['join'] .= " INNER JOIN {$table_prefix}postmeta stm_lng_prefix ON ({$table_prefix}posts.ID = stm_lng_prefix.post_id AND stm_lng_prefix.meta_key = 'stm_lng_car_admin') ";

		if ( 'stm_distance' === $query->get( 'orderby' ) ) {
			$clauses['orderby'] = 'stm_distance ASC, ' . $clauses['orderby'];
		}
	}

	return apply_filters( 'stm_listings_clauses_filter', $clauses, $query );
}

if ( ! function_exists( 'stm_theme_clauses_filter' ) ) {
	function stm_theme_clauses_filter( $clauses, $query ) {
		$enable_location = apply_filters( 'stm_enable_location', false );
		$enable_distance = apply_filters( 'stm_me_get_nuxy_mod', true, 'enable_distance_search' );

		if ( $enable_location && ( $enable_distance || $query->get( 'enable_distance_search' ) ) ) {
			$radius = 0.01;

			if ( ! $enable_distance ) {
				$radius = apply_filters( 'stm_distance_search_value', 100 );
				$radius = ( ! empty( $radius ) ) ? $radius : 100;
			} else {
				if ( isset( $_GET['max_search_radius'] ) ) {
					$radius = sanitize_text_field( $_GET['max_search_radius'] );
					$radius = ( ! empty( $radius ) ) ? $radius : 0.01;
				}
			}

			if ( ! empty( $radius ) ) {
				global $wpdb;
				if ( trim( $clauses['groupby'] ) === '' ) {
					$clauses['groupby'] = $wpdb->posts . '.ID';
				}

				$distance            = floatval( $radius );
				$clauses['groupby'] .= " HAVING stm_distance <= $distance";
			}
		}

		return $clauses;
	}
}

function _stm_listings_build_query_args( $args = null, $source = null ) {
	$listing_atts = stm_listings_attributes( array( 'key_by' => 'slug' ) );
	$sanitized    = filter_var_array( $_GET, FILTER_SANITIZE_FULL_SPECIAL_CHARS );

	if ( ! empty( $sanitized['taxonomy'] ) && ! empty( $listing_atts[ $sanitized['taxonomy'] ] ) ) {
		$sanitized[ $sanitized['taxonomy'] ] = $sanitized['term'];
		unset( $sanitized['taxonomy'] );
		unset( $sanitized['term'] );
	}

	if ( empty( $source ) ) {
		$source = $sanitized;
	} else {
		if ( ! empty( $sanitized ) ) {
			$source = array_merge( $source, $sanitized );
		}
	}

	$args['post_type'] = apply_filters( 'stm_listings_post_type', 'listings' );

	$args['order']   = 'DESC';
	$args['orderby'] = 'date';

	foreach ( $listing_atts as $attribute => $filter_option ) {

		if ( $filter_option['numeric'] ) {
			// Compatibility for min_
			if ( ! empty( $source[ 'min_' . $attribute ] ) ) {
				$source[ $attribute ] = array( 'min' => $source[ 'min_' . $attribute ] );
			}

			// Compatibility for max_
			if ( ! empty( $source[ 'max_' . $attribute ] ) ) {
				$maxArr               = array( 'max' => $source[ 'max_' . $attribute ] );
				$source[ $attribute ] = ( isset( $source[ $attribute ]['min'] ) ) ? array_merge( $source[ $attribute ], $maxArr ) : $maxArr;
			}
		}

		if ( empty( $source[ $attribute ] ) ) {
			continue;
		}

		$_value = $source[ $attribute ];

		if ( ! is_array( $_value ) && $filter_option['numeric'] ) {
			if ( strpos( trim( $_value, '-' ), '-' ) !== false ) {
				$_value = explode( '-', $_value );
				$_value = array(
					'min' => $_value[0],
					'max' => $_value[1],
				);
			} elseif ( strpos( $_value, '>' ) === 0 ) {
				$_value = array(
					'min' => str_replace( '>', '', $_value ),
				);
			} elseif ( strpos( $_value, '<' ) === 0 ) {
				$_value = array(
					'max' => str_replace( '<', '', $_value ),
				);
			}
		}

		if ( ! is_array( $_value ) ) {
			// Exact value
			$args['tax_query'][] = array(
				'taxonomy' => $attribute,
				'field'    => 'slug',
				'terms'    => (array) $_value,
			);
			continue;
		}

		if ( ! empty( $_value['min'] ) || ! empty( $_value['max'] ) ) {
			$between = array( 0, 9999999999 );

			if ( 'price' === $attribute || ( isset( $filter_option['listing_price_field'] ) && ! empty( $filter_option['listing_price_field'] ) ) ) {
				if ( isset( $_value['min'] ) ) {
					$between[0] = stm_convert_to_normal_price( $_value['min'] );
				}
				if ( isset( $_value['max'] ) ) {
					$between[1] = stm_convert_to_normal_price( $_value['max'] );
				}

				$args['meta_query'][] = array(
					array(
						'key'     => 'stm_genuine_price',
						'value'   => $between,
						'type'    => 'DECIMAL',
						'compare' => 'BETWEEN',
					),
				);

				continue;
			}

			if ( isset( $_value['min'] ) ) {
				$between[0] = $_value['min'];
			}
			if ( isset( $_value['max'] ) ) {
				$between[1] = $_value['max'];
			}

			// Range condition
			$args['meta_query'][] = array(
				'key'     => $attribute,
				'value'   => $between,
				'type'    => 'DECIMAL',
				'compare' => 'BETWEEN',
			);

		} elseif ( array_filter( $_value ) ) {
			// Multiple values
			$args['tax_query'][] = array(
				'taxonomy' => $attribute,
				'terms'    => $_value,
				'field'    => 'slug',
			);
		}
	}

	$enable_distance = apply_filters( 'stm_me_get_nuxy_mod', true, 'enable_distance_search' );

	if ( ! $enable_distance ) {
		$location_field_key = 'stm_location_address';

		if ( ! isset( $source[ $location_field_key ] ) && isset( $_COOKIE[ $location_field_key ] ) ) {
			if ( ! empty( $source['ca_location'] ) && ! empty( $source['stm_lat'] ) && ! empty( $source['stm_lng'] ) ) {
				$source[ $location_field_key ] = $_COOKIE[ $location_field_key ];
			}
		}

		if ( isset( $source[ $location_field_key ] ) && ! isset( $source['enable_distance_search'] ) ) {
			$location_address = stm_sanitize_location_address( $source[ $location_field_key ] );

			if ( ! empty( $location_address ) ) {
				$meta_query = array();

				foreach ( $location_address as $_item ) {
					$meta_query[] = array(
						'key'   => sanitize_key( 'stm_listing_' . $_item['key'] ),
						'value' => sanitize_text_field( $_item['value'] ),
					);
				}

				if ( isset( $args['meta_query'] ) && count( $args['meta_query'] ) > 1 ) {
					$args['meta_query'] = array_merge( $meta_query, $args['meta_query'] );
				} else {
					$args['meta_query'] = $meta_query;
				}
			}
		}
	}

	if ( isset( $args['meta_query'] ) && count( $args['meta_query'] ) > 1 ) {
		$args['meta_query'] = array_merge( array( 'relation' => 'AND' ), $args['meta_query'] );
	}

	if ( ! empty( $source['popular'] ) && 'true' === $source['popular'] ) {
		$args['order']    = 'DESC';
		$args['orderby']  = 'meta_value_num';
		$args['meta_key'] = 'stm_car_views';
	}

	$metaKey = '';

	$default_sort = apply_filters( 'stm_get_default_sort_option', 'date_high' );
	$sort_by      = ( ! empty( $source['sort_order'] ) ) ? $source['sort_order'] : $default_sort;

	if ( sort_distance_nearby() ) {
		$sort_by = 'distance_nearby';
	}

	$custom_sort_order  = '';
	$custom_meta_key    = '';
	$custom_price_field = false;

	if ( strpos( $sort_by, '_high' ) !== false ) {
		$custom_sort_order = 'DESC';
		$custom_meta_key   = str_replace( '_high', '', $sort_by );
		$custom_suffix     = 'high';
	} else {
		$custom_sort_order = 'ASC';
		$custom_meta_key   = str_replace( '_low', '', $sort_by );
		$custom_suffix     = 'low';
	}

	if ( stm_is_multilisting() && ! empty( $custom_meta_key ) ) {
		$current_slug = STMMultiListing::stm_get_current_listing_slug();
		if ( ! empty( $current_slug ) ) {
			$data = (array) get_option( "stm_{$current_slug}_options", array() );
			if ( ! empty( $data ) ) {
				foreach ( $data as $key => $arr ) {
					if ( $custom_meta_key === $arr['slug'] && true === $arr['listing_price_field'] ) {
						$sort_by = 'price_' . $custom_suffix;
					}
				}
			}
		}
	}

	if ( ! empty( $sort_by ) ) {
		switch ( $sort_by ) {
			case 'price_low':
				$metaKey          = 'stm_genuine_price';
				$args['meta_key'] = 'stm_genuine_price';
				$args['orderby']  = 'meta_value_num';
				$args['order']    = 'ASC';
				break;
			case 'price_high':
				$metaKey          = 'stm_genuine_price';
				$args['meta_key'] = 'stm_genuine_price';
				$args['orderby']  = 'meta_value_num';
				$args['order']    = 'DESC';
				break;
			case 'date_low':
				$args['order']   = 'ASC';
				$args['orderby'] = 'date';
				break;
			case 'date_high':
				$args['order']   = 'DESC';
				$args['orderby'] = 'date';
				break;
			case 'mileage_low':
				$metaKey          = 'mileage';
				$args['order']    = 'ASC';
				$args['orderby']  = 'meta_value_num';
				$args['meta_key'] = 'mileage';
				break;
			case 'mileage_high':
				$metaKey          = 'mileage';
				$args['order']    = 'DESC';
				$args['orderby']  = 'meta_value_num';
				$args['meta_key'] = 'mileage';
				break;
			case 'distance_nearby':
				$args['order']   = 'ASC';
				$args['orderby'] = 'stm_distance';
				break;
			default:
				$args['meta_key'] = $custom_meta_key;
				$args['orderby']  = 'meta_value_num';
				$args['order']    = $custom_sort_order;
		}
	}

	$args['sold_car'] = 'off';

	if ( apply_filters( 'stm_sold_status_enabled', false ) ) {
		$_sold_meta_key   = 'car_mark_as_sold';
		$_sold_meta_query = array(
			array(
				'key'     => $_sold_meta_key,
				'value'   => 'on',
				'compare' => '=',
			),
		);
		$_listing_status  = ( ! empty( $source['listing_status'] ) ) ? $source['listing_status'] : '';
		$show_sold        = apply_filters( 'stm_me_get_nuxy_mod', false, 'show_sold_listings' );

		if ( ! empty( $source['sold_car'] ) ) {
			$args['sold_car'] = 'on';
		} elseif ( 'active' === $_listing_status || empty( $_listing_status ) ) {
			$_sold_meta_query = reset( $_sold_meta_query );

			$_sold_meta_query['value'] = '';

			$_sold_meta_query = array(
				'relation' => 'OR',
				$_sold_meta_query,
				array(
					'key'         => $_sold_meta_key,
					'compare_key' => 'NOT EXISTS',
				),
			);
		}

		if ( $show_sold && empty( $_listing_status ) && empty( $source['sold_car'] ) ) {
			$_sold_meta_query = array();
		}

		if ( ! empty( $_sold_meta_query ) ) {
			$args['meta_query'][] = $_sold_meta_query;
		}
	}

	$args['meta_query_count'][] = ( isset( $args['meta_query'] ) ) ? $args['meta_query'] : array();

	if ( ! empty( $source['listing_type'] ) && 'with_review' === $source['listing_type'] ) {
		$args['meta_query'][] = array(
			array(
				'key'     => 'has_review_car',
				'compare' => 'EXISTS',
			),
		);
	}

	if ( ! empty( $source['posts_per_page'] ) ) {
		$args['posts_per_page'] = $source['posts_per_page'];
	}

	if ( ! empty( $source['offset'] ) && ! empty( $source['posts_per_page'] ) ) {
		$args['offset'] = $source['offset'] * $source['posts_per_page'];
	}

	// Enables adding location conditions
	$args['filter_location'] = true;

	$blog_id = get_current_blog_id();

	// later used in STM Inventory Search Results shortcode

	// search results back link
	$link_get = $sanitized;

	if ( isset( $link_get['ajax_action'] ) && ! empty( $link_get['ajax_action'] ) ) {
		unset( $link_get['ajax_action'] );
	}

	$inventory_link = add_query_arg( $link_get, get_the_permalink( apply_filters( 'stm_get_listing_archive_page_id', 0 ) ) );

	if ( isset( $_COOKIE[ 'stm_visitor_' . $blog_id ] ) ) {
		$fake_id = $_COOKIE[ 'stm_visitor_' . $blog_id ];
		set_transient( 'stm_last_query_args_' . $fake_id, $args, HOUR_IN_SECONDS );
		set_transient( 'stm_last_query_link_' . $fake_id, $inventory_link, HOUR_IN_SECONDS );
	}

	$args['stm_keywords']         = sanitize_text_field( apply_filters( 'stm_listings_input', null, 'stm_keywords' ) );
	$args['stm_location_address'] = sanitize_text_field( apply_filters( 'stm_listings_input', null, 'stm_location_address' ) );

	return apply_filters( 'stm_listings_build_query_args', $args, $source );
}

add_filter( '_stm_listings_build_query_args', '_stm_listings_build_query_args', 10, 2 );

if ( ! function_exists( 'stm_listings_posts_where' ) ) {
	function stm_listings_posts_where( $where, WP_Query $query ) {
		$stm_keywords = $query->get( 'stm_keywords' );

		if ( ! empty( $stm_keywords ) && in_array( $query->get( 'post_type' ), stm_listings_multi_type( true ), true ) ) {
			global $wpdb;

			$stm_keywords = sanitize_text_field( $stm_keywords );
			$n            = ! empty( $query->get( 'exact' ) ) ? '' : '%';

			if ( $query->get( 'sentence' ) ) {
				$search_terms = array( $n . $wpdb->esc_like( $stm_keywords ) . $n );
			} else {
				if ( preg_match_all( '/".*?("|$)|((?<=[\t ",+])|^)[^\t ",+]+/', $stm_keywords, $matches ) ) {
					$search_terms = stm_listings_parse_search_terms( $matches[0] );
					// If the search string has only short terms or stopwords, or is 10+ terms long, match it as sentence.
					if ( empty( $search_terms ) || count( $search_terms ) > 9 ) {
						$search_terms = array( $stm_keywords );
					}
				} else {
					$search_terms = array( $stm_keywords );
				}
			}

			$exclusion_prefix       = apply_filters( 'stm_wp_query_search_exclusion_prefix', apply_filters( 'wp_query_search_exclusion_prefix', '-' ) );
			$default_search_columns = array(
				'stm_pm.meta_value',
				$wpdb->posts . '.post_title',
				$wpdb->posts . '.post_content',
				$wpdb->posts . '.post_excerpt',
				'stm_terms.name',
			);

			$search_columns = (array) apply_filters( 'stm_listings_search_columns', $default_search_columns, $stm_keywords, $query );

			foreach ( $search_terms as $term ) {
				// If there is an $exclusion_prefix, terms prefixed with it should be excluded.
				$exclude = $exclusion_prefix && str_starts_with( $term, $exclusion_prefix );
				if ( $exclude ) {
					$like_op  = 'NOT LIKE';
					$andor_op = 'AND';
					$term     = substr( $term, 1 );
				} else {
					$like_op  = 'LIKE';
					$andor_op = 'OR';
				}

				$like = $n . $wpdb->esc_like( $term ) . $n;

				$search_columns_parts = array();
				foreach ( $search_columns as $search_db ) {
					$search_db = sprintf( '%s %s %%s', $search_db, $like_op );

					$search_columns_parts[ $search_db ] = $wpdb->prepare( $search_db, $like ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				}

				$where .= ' AND (' . implode( " $andor_op ", $search_columns_parts ) . ')';
			}
		}

		return apply_filters( 'stm_listings_where_query', $where, $stm_keywords );
	}
}

add_filter( 'posts_where', 'stm_listings_posts_where', 20, 2 );

if ( ! function_exists( 'stm_listings_posts_join' ) ) {
	function stm_listings_posts_join( $join, WP_Query $query ) {
		$stm_keywords = $query->get( 'stm_keywords' );

		if ( ! empty( $stm_keywords ) && in_array( $query->get( 'post_type' ), stm_listings_multi_type( true ), true ) ) {
			global $wpdb;

			$join .= " LEFT JOIN {$wpdb->postmeta} as stm_pm ON stm_pm.post_id = {$wpdb->posts}.ID";
			$join .= " LEFT JOIN {$wpdb->term_relationships} as stm_tr ON stm_tr.object_id = {$wpdb->posts}.ID";
			$join .= " LEFT JOIN {$wpdb->term_taxonomy} as stm_tt ON stm_tt.term_taxonomy_id = stm_tr.term_taxonomy_id";
			$join .= " LEFT JOIN {$wpdb->terms} as stm_terms ON stm_terms.term_id = stm_tt.term_id";
		}

		return apply_filters( 'stm_listings_join_query', $join, $stm_keywords );
	}
}

add_filter( 'posts_join', 'stm_listings_posts_join', 20, 2 );

if ( ! function_exists( 'stm_listings_posts_group_by' ) ) {
	function stm_listings_posts_group_by( $groupby, WP_Query $query ) {
		global $wpdb;

		$stm_keywords = $query->get( 'stm_keywords' );

		if ( empty( $groupby ) && $stm_keywords ) {
			$groupby = $wpdb->posts . '.ID';
		}

		return apply_filters( 'stm_listings_groupby_query', $groupby, $stm_keywords );
	}
}

add_filter( 'posts_groupby', 'stm_listings_posts_group_by', 10, 2 );

if ( ! function_exists( 'stm_listings_parse_search_terms' ) ) {
	function stm_listings_parse_search_terms( $terms ) {
		$strtolower = function_exists( 'mb_strtolower' ) ? 'mb_strtolower' : 'strtolower';
		$checked    = array();

		$stopwords = stm_listings_get_search_stopwords();

		foreach ( $terms as $term ) {
			// Keep before/after spaces when term is for exact match.
			if ( preg_match( '/^".+"$/', $term ) ) {
				$term = trim( $term, "\"'" );
			} else {
				$term = trim( $term, "\"' " );
			}

			// Avoid single A-Z and single dashes.
			if ( ! $term || ( 1 === strlen( $term ) && preg_match( '/^[a-z\-]$/i', $term ) ) ) {
				continue;
			}

			if ( in_array( call_user_func( $strtolower, $term ), $stopwords, true ) ) {
				continue;
			}

			$checked[] = $term;
		}

		return $checked;
	}
}

if ( ! function_exists( 'stm_listings_get_search_stopwords' ) ) {
	function stm_listings_get_search_stopwords() {
		// Translators: This is a comma-separated list of very common words that should be excluded from a search, like a, an, and the. These are usually called "stopwords". You should not simply translate these individual words into your language. Instead, look for and provide commonly accepted stopwords in your language.
		$words = explode(
			',',
			_x(
				'about,an,are,as,at,be,by,com,for,from,how,in,is,it,of,on,or,that,the,this,to,was,what,when,where,who,will,with,www',
				'Comma-separated list of search stopwords in your language'
			)
		);

		$stopwords = array();
		foreach ( $words as $word ) {
			$word = trim( $word, "\r\n\t " );
			if ( $word ) {
				$stopwords[] = $word;
			}
		}

		return $stopwords;
	}
}

// Location Filter hook.
if ( ! function_exists( 'stm_location_validates' ) ) {
	function stm_location_validates() {
		if ( isset( $_GET['stm_lng'] ) && isset( $_GET['stm_lat'] ) && ! empty( $_GET['ca_location'] ) ) {
			return true;
		} else {
			return false;
		}
	}
}

if ( ! function_exists( 'stm_theme_clauses_filter' ) ) {
	function stm_theme_clauses_filter( $clauses, $query ) {
		$enable_location = apply_filters( 'stm_enable_location', false );
		$enable_distance = apply_filters( 'stm_me_get_nuxy_mod', true, 'enable_distance_search' );

		if ( $enable_location && ( $enable_distance || $query->get( 'enable_distance_search' ) ) ) {
			$radius = 0.01;

			if ( ! $enable_distance ) {
				$radius = apply_filters( 'stm_me_get_nuxy_mod', 100, 'distance_search' );
				$radius = ( ! empty( $radius ) ) ? $radius : 100;
			} else {
				if ( isset( $_GET['max_search_radius'] ) ) {
					$radius = sanitize_text_field( $_GET['max_search_radius'] );
					$radius = ( ! empty( $radius ) ) ? $radius : 0.01;
				}
			}

			if ( ! empty( $radius ) ) {
				global $wpdb;
				if ( trim( $clauses['groupby'] ) === '' ) {
					$clauses['groupby'] = $wpdb->posts . '.ID';
				}

				$distance            = floatval( $radius );
				$clauses['groupby'] .= " HAVING stm_distance <= $distance";
			}
		}

		return $clauses;
	}
}

add_filter( 'stm_listings_clauses_filter', 'stm_theme_clauses_filter', 10, 2 );
