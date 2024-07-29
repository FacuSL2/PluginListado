<?php
defined( 'ABSPATH' ) || exit;

function stm_google_places_enable_script( $status = 'registered', $only_google_load = false ) {
	/* Google places */
	$status         = empty( $status ) ? 'registered' : $status;
	$google_api_key = apply_filters( 'stm_me_get_nuxy_mod', '', 'google_api_key' );

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

	if ( ! wp_script_is( 'stm_gmap', 'registered' ) ) {
		wp_register_script( 'stm_gmap', $google_api_map, null, '1.0', true );
	}

	if ( ! wp_script_is( 'stm-google-places' ) && ! $only_google_load ) {
		wp_register_script( 'stm-google-places', STM_LISTINGS_URL . '/assets/js/frontend/stm-google-places.js', array( 'jquery', 'stm_gmap' ), STM_LISTINGS_V, true );
	}

	if ( 'enqueue' === $status ) {
		wp_enqueue_script( 'stm_gmap' );

		if ( ! $only_google_load ) {
			wp_enqueue_script( 'stm-google-places' );
		}
	}
}

add_action( 'stm_google_places_script', 'stm_google_places_enable_script' );

function stm_listings_enqueue_scripts_styles() {
	wp_enqueue_style( 'owl.carousel', STM_LISTINGS_URL . '/assets/css/frontend/owl.carousel.css', array(), STM_LISTINGS_V );
	wp_enqueue_style( 'listings-frontend', STM_LISTINGS_URL . '/assets/css/frontend/frontend_styles.css', array(), STM_LISTINGS_V );
	wp_enqueue_style( 'listings-add-car', STM_LISTINGS_URL . '/assets/css/frontend/add_a_car.css', array(), STM_LISTINGS_V );
	wp_enqueue_style( 'light-gallery', STM_LISTINGS_URL . '/assets/css/frontend/lightgallery.min.css', array(), STM_LISTINGS_V );

	wp_enqueue_script( 'jquery-cookie', STM_LISTINGS_URL . '/assets/js/frontend/jquery.cookie.js', array( 'jquery' ), STM_LISTINGS_V, true );
	wp_enqueue_script( 'owl.carousel', STM_LISTINGS_URL . '/assets/js/frontend/owl.carousel.js', array( 'jquery' ), STM_LISTINGS_V, true );
	wp_enqueue_script( 'light-gallery', STM_LISTINGS_URL . '/assets/js/frontend/lightgallery-all.js', array( 'jquery' ), STM_LISTINGS_V, true );
	wp_enqueue_script( 'listings-add-car', STM_LISTINGS_URL . '/assets/js/frontend/add_a_car.js', array( 'jquery', 'jquery-ui-droppable' ), STM_LISTINGS_V, true );
	wp_enqueue_script( 'listings-init', STM_LISTINGS_URL . '/assets/js/frontend/init.js', array( 'jquery', 'jquery-ui-slider' ), STM_LISTINGS_V, true );
	wp_enqueue_script( 'listings-filter', STM_LISTINGS_URL . '/assets/js/frontend/filter.js', array( 'listings-init' ), STM_LISTINGS_V, true );

	do_action( 'stm_google_places_script' );

	if ( defined( 'ELEMENTOR_VERSION' ) ) {
		if ( Elementor\Plugin::$instance->editor->is_edit_mode() || Elementor\Plugin::$instance->preview->is_preview_mode() ) {
			wp_add_inline_script( 'listings-init', 'var stm_elementor_editor_mode = true' );
		}
	}
}

add_action( 'wp_enqueue_scripts', 'stm_listings_enqueue_scripts_styles' );
