<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


/**
 * Locate template in listings scope
 *
 * @param string|array $templates Single or array of template files
 *
 * @return string
 */
function stm_listings_locate_template( $templates ) {
	$located = false;

	/** get correct path for templates inside v8 */
	if ( 0 === strpos( $templates, STM_VEHICLE_NEW_VERSION ) ) {
		if ( substr( $templates, - 4 ) !== '.php' ) {
			$templates .= '.php';
		}
		$templates = str_replace( 'v8', '', $templates );
		$located   = STM_VEHICLE_PATH . '/templates/' . $templates;
	} else {
		foreach ( (array) $templates as $template ) {
			if ( substr( $template, - 4 ) !== '.php' ) {
				$template .= '.php';
			}

			$located = locate_template( 'listings/' . $template );

			if ( ! ( $located ) ) {
				$located = STM_LISTINGS_PATH . '/templates/' . $template;
			}

			if ( file_exists( $located ) ) {
				break;
			}
		}
	}

	return apply_filters( 'stm_listings_locate_template', $located, $templates );
}

/**
 * Load template
 *
 * @param $__template
 * @param array $__vars
 */
function stm_listings_load_template( $__template, $__vars = array() ) {
	extract( $__vars );
	include stm_listings_locate_template( $__template );
}

add_action( 'stm_listings_load_template', 'stm_listings_load_template', 10, 2 );

/**
 * Load a template part into a template.
 *
 * The same as core WordPress get_template_part(), but also includes listings scope
 *
 * @param string $template
 * @param string $name
 * @param array $vars
 */
function stm_listings_template_part( $template, $name = '', $vars = array() ) {
	$templates = array();
	$name      = (string) $name;
	if ( '' !== $name ) {
		$templates[] = "{$template}-{$name}.php";
	}

	$templates[] = "{$template}.php";

	$located = stm_listings_locate_template( $templates );
	if ( $located ) {
		stm_listings_load_template( $located, $vars );
	}
}

add_filter( 'archive_template', 'stm_listings_archive_template' );

function stm_listings_archive_template( $template ) {

	if ( is_post_type_archive( apply_filters( 'stm_listings_post_type', 'listings' ) ) ) {
		$located = stm_listings_locate_template( 'archive.php' );
		if ( $located ) {
			$template = $located;
		}
	}

	return $template;
}

add_filter( 'page_template', 'stm_listings_archive_page_template' );

function stm_listings_archive_page_template( $template ) {
	global $post;
	if ( isset( $post->ID ) && stm_listings_user_defined_filter_page() === $post->ID ) {
		$content = $post->content;
		if ( has_shortcode( $content, 'stm_classic_filter' ) ) {
			$located = stm_listings_locate_template( 'archive.php' );
			if ( $located ) {
				$template = $located;
			}
		}
	}

	return $template;
}

add_filter( 'single_template', 'stm_get_single_listing_template' );

function stm_get_single_listing_template( $template ) {
	$located = stm_listings_locate_template( 'single.php' );

	if ( is_singular( 'listings' ) && $located ) {
		$template = $located;
	}

	return $template;
}

function stm_listings_load_results( $source = null, $type = null, $navigation_type = null ) {
	$GLOBALS['wp_query'] = stm_listings_query( $source );
	stm_listings_load_template(
		'filter/results',
		array(
			'type'            => $type,
			'navigation_type' => $navigation_type,
			'custom_img_size' => ( ! empty( $source['custom_img_size'] ) ) ? $source['custom_img_size'] : null,
		)
	);
}

add_action( 'stm_listings_load_results', 'stm_listings_load_results', 10, 3 );



function stm_listings_load_items_results( $source = null, $type = null, $navigation_type = null ) {
	$GLOBALS['wp_query'] = stm_listings_query( $source );
	stm_listings_load_template(
		'filter/results-items',
		array(
			'type'            => $type,
			'navigation_type' => $navigation_type,
		)
	);
}

/*
 * Used in Visual Composer stm_listing_search_with_car_rating
 * */
function stm_listings_vc_modul_load_results( $attr ) {
	$GLOBALS['wp_query'] = stm_listings_query( $attr );
	stm_listings_load_template( 'filter/result_with_rating' );
	wp_reset_postdata();
}

function stm_listings_load_pagination() {
	$GLOBALS['wp_query'] = stm_listings_query();
	stm_listings_load_template( 'filter/pagination' );
	wp_reset_postdata();
}

function stm_add_listing_inventory( $atts ) {
	stm_listings_load_template( 'filter/inventory/main' );
}

add_shortcode( 'stm_add_listing_inventory', 'stm_add_listing_inventory' );

//Add a car
function stm_add_a_car_page( $atts ) {
	stm_listings_load_template( 'add-a-car' );
}

add_shortcode( 'stm_add_a_car_page', 'stm_add_a_car_page' );

//Login Register
function stm_add_login_page( $atts ) {
	stm_listings_load_template( 'login' );
}

add_shortcode( 'stm_add_login_page', 'stm_add_login_page' );

//Compare page
function stm_add_compare_page( $atts ) {
	stm_listings_load_template( 'compare/compare' );
}

add_shortcode( 'stm_add_compare_page', 'stm_add_compare_page' );

//Author
add_filter( 'template_include', 'stm_author_template_loader' );

function stm_author_template_loader( $template ) {

	if ( is_author() ) {

		$located = stm_listings_locate_template( 'author.php' );
		if ( $located ) {
			$template = $located;
		}
	}

	return $template;
}
