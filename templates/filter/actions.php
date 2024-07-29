<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>

<div class="stm-sort-by-options clearfix">
	<span><?php esc_html_e( 'Sort by:', 'stm_vehicles_listing' ); ?></span>
	<select name="sort_order">
		<?php echo wp_kses_post( apply_filters( 'stm_get_sort_options_html', '' ) ); ?>
	</select>
</div>
