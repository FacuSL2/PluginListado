<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// Compare
$show_compare    = apply_filters( 'stm_me_get_nuxy_mod', false, 'show_listing_compare' );
$cars_in_compare = apply_filters( 'stm_get_compared_items', array() );

if ( ! empty( $show_compare ) && $show_compare ) : ?>
	<div class="stm_compare_unit">
		<?php if ( in_array( get_the_ID(), $cars_in_compare, true ) ) : ?>
			<a
				href="#"
				class="add-to-compare active"
				title="<?php esc_html_e( 'Remove from compare', 'stm_vehicles_listing' ); ?>"
				data-id="<?php echo esc_attr( get_the_ID() ); ?>"
				data-title="<?php echo esc_attr( get_the_title() ); ?>">
				<i class="fas fa-plus"></i>
			</a>
		<?php else : ?>
			<a
				href="#"
				class="add-to-compare"
				data-post-type="<?php echo esc_attr( get_post_type( get_the_ID() ) ); ?>"
				title="<?php esc_html_e( 'Add to compare', 'stm_vehicles_listing' ); ?>"
				data-id="<?php echo esc_attr( get_the_ID() ); ?>"
				data-title="<?php echo esc_attr( get_the_title() ); ?>">
				<i class="fas fa-plus"></i>
			</a>
		<?php endif; ?>
	</div>
<?php endif; ?>
