<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$user = wp_get_current_user();

$vars = get_queried_object();

if ( ! empty( $_GET['view-myself'] ) ) {//phpcs:ignore WordPress.Security.NonceVerification.Recommended
	do_action( 'stm_listings_load_template', 'user/public/user' );
} else {
	if ( $user->ID !== $vars->ID ) {
		do_action( 'stm_listings_load_template', 'user/public/user' );
	} else {
		do_action( 'stm_listings_load_template', 'user/private/user' );
	}
}
