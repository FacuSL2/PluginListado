<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

function stm_admin_google_places_enable_script( $status = 'registered', $only_google_load = false ) {
	/* Google places */
	$status         = empty( $status ) ? 'registered' : $status;
	$google_api_key = apply_filters( 'stm_me_get_nuxy_mod', '', 'google_api_key' );

	if ( ! empty( $google_api_key ) ) {
		$google_api_map = 'https://maps.googleapis.com/maps/api/js';
		$google_api_map = add_query_arg(
			array(
				'key'       => $google_api_key,
				'libraries' => 'places',
				'loading'   => 'async',
				'language'  => get_bloginfo( 'language' ),
			),
			$google_api_map
		);

		if ( stm_is_use_plugin( 'stm_motors_events/stm_motors_events.php' ) ) {
			$google_api_map = add_query_arg(
				array(
					'callback' => 'initGoogleScripts',
				),
				$google_api_map
			);
		}

		if ( ! wp_script_is( 'stm_gmap', 'registered' ) ) {
			wp_register_script( 'stm_gmap', $google_api_map, null, '1.0', true );
		}

		if ( ! wp_script_is( 'stm-google-places' ) && ! $only_google_load ) {
			wp_register_script( 'stm-google-places', STM_LISTINGS_URL . '/assets/js/stm-admin-places.js', array( 'jquery', 'stm_gmap' ), STM_LISTINGS_V, true );
		}

		if ( 'enqueue' === $status ) {
			wp_enqueue_script( 'stm_gmap' );

			if ( ! $only_google_load ) {
				wp_enqueue_script( 'stm-google-places' );
			}
		}
	}
}

add_action( 'stm_admin_google_places_script', 'stm_admin_google_places_enable_script' );

function stm_listings_admin_enqueue( $hook ) {
	wp_enqueue_style( 'wp-color-picker' );
	wp_enqueue_script( 'wp-color-picker' );

	wp_enqueue_style( 'stm-listings-datetimepicker', STM_LISTINGS_URL . '/assets/css/jquery.stmdatetimepicker.css', null, '1.0' );
	wp_enqueue_script( 'stm-listings-datetimepicker', STM_LISTINGS_URL . '/assets/js/jquery.stmdatetimepicker.js', array( 'jquery' ), '1.0', true );

	wp_enqueue_style( 'jquery-ui-datepicker-style', '//ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css', null, '1.0' );

	wp_enqueue_media();

	if ( 'product' === get_post_type() || in_array( get_post_type(), stm_listings_multi_type( true ), true ) || 'page' === get_post_type() || 'listings_page_listing_categories' === $hook ) {

		wp_register_script( 'stm-theme-multiselect', STM_LISTINGS_URL . '/assets/js/jquery.multi-select.js', array( 'jquery' ), STM_LISTINGS_V, true );

		wp_register_script(
			'stm-listings-js',
			STM_LISTINGS_URL . '/assets/js/vehicles-listing.js',
			array(
				'jquery',
				'jquery-ui-droppable',
				'jquery-ui-datepicker',
				'jquery-ui-sortable',
			),
			'6.5.8.1',
			true
		);

		/* Google places */
		do_action( 'stm_admin_google_places_script' );
	}

	wp_enqueue_style( 'stm_listings_listing_css', STM_LISTINGS_URL . '/assets/css/style.css', null, STM_LISTINGS_V );
}

add_action( 'admin_enqueue_scripts', 'stm_listings_admin_enqueue' );
