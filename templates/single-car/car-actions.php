<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$cars_in_compare       = apply_filters( 'stm_get_compared_items', array() );
$stock_number          = get_post_meta( get_the_id(), 'stock_number', true );
$car_brochure          = get_post_meta( get_the_ID(), 'car_brochure', true );
$certified_logo_1      = get_post_meta( get_the_ID(), 'certified_logo_1', true );
$certified_logo_2      = get_post_meta( get_the_ID(), 'certified_logo_2', true );
$show_stock            = apply_filters( 'stm_me_get_nuxy_mod', false, 'show_stock' );
$show_compare          = apply_filters( 'stm_me_get_nuxy_mod', false, 'show_compare' );
$show_pdf              = apply_filters( 'stm_me_get_nuxy_mod', false, 'show_pdf' );
$show_certified_logo_1 = apply_filters( 'stm_me_get_nuxy_mod', false, 'show_certified_logo_1' );
$show_certified_logo_2 = apply_filters( 'stm_me_get_nuxy_mod', false, 'show_certified_logo_2' );

?>

<div class="single-car-actions">
	<ul class="list-unstyled clearfix">
		<!--Stock num-->
		<?php if ( ! empty( $stock_number ) && ! empty( $show_stock ) && $show_stock ) : ?>
			<li>
				<div class="stock-num heading-font"><span><?php echo esc_html__( 'stock', 'stm_vehicles_listing' ); ?>
						# </span><?php echo esc_attr( $stock_number ); ?></div>
			</li>
		<?php endif; ?>

		<!--Compare-->
		<?php if ( ! empty( $show_compare ) && $show_compare ) : ?>
			<li>
				<?php if ( in_array( get_the_ID(), $cars_in_compare, true ) ) : ?>
					<a
						href="#"
						class="car-action-unit add-to-compare active"
						title="<?php esc_html_e( 'Remove from compare', 'stm_vehicles_listing' ); ?>"
						data-id="<?php echo esc_attr( get_the_ID() ); ?>"
						data-title="<?php echo esc_attr( get_the_title() ); ?>"
						data-post-type="<?php echo esc_attr( get_post_type( get_the_ID() ) ); ?>"
						>
						<?php esc_html_e( 'Remove from compare', 'stm_vehicles_listing' ); ?>
					</a>
				<?php else : ?>
					<a
						href="#"
						class="car-action-unit add-to-compare"
						data-post-type="<?php echo esc_attr( get_post_type( get_the_ID() ) ); ?>"
						title="<?php esc_html_e( 'Add to compare', 'stm_vehicles_listing' ); ?>"
						data-id="<?php echo esc_attr( get_the_ID() ); ?>"
						data-title="<?php echo esc_attr( get_the_title() ); ?>">
						<?php esc_html_e( 'Add to compare', 'stm_vehicles_listing' ); ?>
					</a>
				<?php endif; ?>
				<script type="text/javascript">
					var stm_label_add = "<?php esc_html_e( 'Add to compare', 'stm_vehicles_listing' ); ?>";
					var stm_label_remove = "<?php esc_html_e( 'Remove from compare', 'stm_vehicles_listing' ); ?>";
				</script>
			</li>
		<?php endif; ?>

		<!--PDF-->
		<?php if ( ! empty( $show_pdf ) && $show_pdf ) : ?>
			<?php if ( ! empty( $car_brochure ) ) : ?>
				<li>
					<a
						href="<?php echo esc_url( wp_get_attachment_url( $car_brochure ) ); ?>"
						class="car-action-unit stm-brochure"
						title="<?php esc_html_e( 'Download brochure', 'stm_vehicles_listing' ); ?>"
						download>
						<?php esc_html_e( 'Car brochure', 'stm_vehicles_listing' ); ?>
					</a>
				</li>
			<?php endif; ?>
		<?php endif; ?>

		<!--Certified Logo 1-->
		<?php if ( ! empty( $certified_logo_1 ) && ! empty( $show_certified_logo_1 ) && $show_certified_logo_1 ) : ?>
			<?php
			$certified_logo_1 = wp_get_attachment_image_src( $certified_logo_1, 'full' );
			if ( ! empty( $certified_logo_1[0] ) ) {
				$certified_logo_1 = $certified_logo_1[0];
			}
			?>
			<li class="certified-logo-1">
				<img src="<?php echo esc_url( $certified_logo_1 ); ?>"
					alt="<?php esc_html_e( 'Logo 1', 'stm_vehicles_listing' ); ?>"/>
			</li>
		<?php endif; ?>

		<!--Certified Logo 2-->
		<?php if ( ! empty( $certified_logo_2 ) && ! empty( $show_certified_logo_2 ) && $show_certified_logo_2 ) : ?>
			<?php
			$certified_logo_2 = wp_get_attachment_image_src( $certified_logo_2, 'full' );
			if ( ! empty( $certified_logo_2[0] ) ) {
				$certified_logo_2 = $certified_logo_2[0];
			}
			?>
			<li class="certified-logo-2">
				<img src="<?php echo esc_url( $certified_logo_2 ); ?>"
					alt="<?php esc_html_e( 'Logo 2', 'stm_vehicles_listing' ); ?>"/>
			</li>
		<?php endif; ?>

	</ul>
</div>
