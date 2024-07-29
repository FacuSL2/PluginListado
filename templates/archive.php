<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
get_header();

do_action( 'stm_listings_load_template', 'filter/inventory/main' );

get_footer();
