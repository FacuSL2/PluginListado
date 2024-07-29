<?php

namespace MotorsVehiclesListing;

use STMMultiListing;

class FriendlyUrl {

	public static $listing_archive = '';
	public static $for_filter      = array();
	public static $cpt_slugs       = array(); //All custom post types if enable multilisting

	public static function init() {
		add_action( 'init', array( __CLASS__, 'custom_rewrite_rules' ), 10, 1 );
		add_filter( 'query_vars', array( __CLASS__, 'query_vars_filter' ), 10, 1 );
		add_filter( 'request', array( __CLASS__, 'request_filter' ), 20, 1 );
		add_filter( 'stm_listings_current_url', array( __CLASS__, 'stm_fu_listings_current_url' ), 20, 1 );
		add_filter( 'stm_listings_build_query_args', array( __CLASS__, 'stm_fu_listings_build_query_args' ), 10, 1 );
		add_filter( 'stm_get_filter_badge_url', array( __CLASS__, 'stm_fu_get_filter_badge_url' ), 10, 3 );
	}

	public static function custom_rewrite_rules() {
		$listing_archive_id = stm_listings_user_defined_filter_page();
		$slug               = basename( get_permalink( $listing_archive_id ) );

		add_rewrite_rule( $slug . '/(.*)', 'index.php?page_id=' . $listing_archive_id . '&pagename=inventory&filterargs=$matches[1]', 'top' );

		if ( class_exists( 'STMMultiListing' ) ) {
			$custom_post_types = STMMultiListing::stm_get_listings();

			foreach ( $custom_post_types as $cpt ) {
				self::$cpt_slugs[] = $cpt['slug'];
				if ( ! empty( $cpt['inventory_page'] ) ) {
					$slug = basename( get_permalink( $cpt['inventory_page'] ) );
					add_rewrite_rule( $slug . '/(.*)', 'index.php?page_id=' . $cpt['inventory_page'] . '&pagename=' . $slug . '&posttype=' . $cpt['slug'] . '&filterargs=$matches[1]', 'top' );
				}
			}
		}

		flush_rewrite_rules();
	}

	public static function query_vars_filter( $query_vars ) {
		$query_vars[] = 'paged';
		$query_vars[] = 'filterargs';
		$query_vars[] = 'posttype';
		return $query_vars;
	}

	public static function request_filter( $query_vars ) {

		self::$for_filter = array();

		if ( ( ! empty( $query_vars['pagename'] ) && ! empty( $query_vars['filterargs'] ) ) || ( ! empty( $_GET['ajax_action'] ) && 'listings-result' === $_GET['ajax_action'] && ! empty( $query_vars['filterargs'] ) ) ) {
			$args = explode( '/', $query_vars['filterargs'] );

			if ( in_array( 'page', $args, true ) ) {
				$page_key            = array_search( 'page', $args, true );
				$query_vars['paged'] = $args[ $page_key + 1 ];
				unset( $args[ $page_key + 1 ] );
				unset( $args[ $page_key ] );
			}

			$for_filter = array();
			$posttype   = ( ! empty( $query_vars['posttype'] ) ) ? $query_vars['posttype'] : '';

			foreach ( $args as $arg ) {
				$for_filter = array_merge_recursive( $for_filter, self::get_taxonomy_by_term_slug( $arg, $posttype ) );
			}

			self::$for_filter = $for_filter;
		}

		return $query_vars;
	}

	public static function get_taxonomy_by_term_slug( $slug, $post_type = '' ) {
		global $wpdb;

		$sql = "SELECT t.slug as slug, t.term_id, t.name, tt.taxonomy as taxonomy
				FROM {$wpdb->prefix}terms AS t
				INNER JOIN {$wpdb->prefix}term_taxonomy AS tt ON t.term_id = tt.term_id 
				WHERE t.slug = '%1s'";

		$sql .= ( ! empty( $post_type ) ) ? " AND tt.taxonomy LIKE '$post_type%'" : '';

		if ( empty( $post_type ) && ! empty( self::$cpt_slugs ) ) {
			foreach ( self::$cpt_slugs as $cpt_slug ) {
				$sql .= " AND tt.taxonomy NOT LIKE '$cpt_slug%'";
			}
		}

		$sql .= " AND tt.taxonomy != 'category' AND tt.taxonomy != 'post_tag'";

		$result = $wpdb->get_row( $wpdb->prepare( $sql, $slug ) );//phpcs:ignore

		return ( ! empty( $result->taxonomy ) ) ? array( $result->taxonomy => $result->slug ) : array();
	}

	public static function stm_fu_listings_build_query_args( $args ) {
		if ( ! empty( self::$for_filter ) ) {
			$args['tax_query'] = array(
				'relation' => 'AND',
			);

			foreach ( self::$for_filter as $taxonomy => $term ) {
				$args['tax_query'][] = array(
					'taxonomy' => $taxonomy,
					'field'    => 'slug',
					'terms'    => $term,
				);
			}
		}

		return $args;
	}

	public static function stm_fu_listings_current_url( $url ) {
		$listing_archive_id = stm_listings_user_defined_filter_page();
		$current_page       = ( get_the_ID() === (int) $listing_archive_id ) ? get_permalink( $listing_archive_id ) : '';

		if ( class_exists( 'STMMultiListing' ) ) {
			$custom_post_types = STMMultiListing::stm_get_listings();

			foreach ( $custom_post_types as $cpt ) {
				$current_page = ( get_the_ID() === (int) $cpt['inventory_page'] ) ? get_permalink( $cpt['inventory_page'] ) : '';
			}
		}

		return ( ! empty( $current_page ) ) ? $current_page : $url;
	}

	public static function stm_fu_get_filter_badge_url( $uri, $badge_info, $remove_args ) {
		$filter_args = self::$for_filter;

		unset( $filter_args[ $badge_info['origin'][0] ] );

		$queries = ( count( $filter_args ) > 0 ) ? '?' . build_query( $filter_args ) : '';

		return self::stm_fu_listings_current_url( $uri ) . $queries;
	}
}

FriendlyUrl::init();
