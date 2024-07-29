<?php
/**
 * @var $attachment_id
 * @var $item_id
 * */

if ( ! isset( $attachment_id ) ) {
	$attachment_id = 0;
}

if ( ! isset( $item_id ) ) {
	$item_id = 0;
}

$classes     = 'stm-placeholder-native';
$image       = '';
$empty_image = false;
if ( ! empty( $attachment_id ) ) {
	$classes = 'stm-placeholder-generated stm-placeholder-generated-php';
	$image   = wp_get_attachment_image_src( $attachment_id, 'stm-img-796-466' );

	if ( $image && ! empty( $image[0] ) ) {
		$image = 'style=background:url("' . $image[0] . '")';
	}

	if ( empty( $image ) ) {
		$empty_image = true;
	}
}

if ( ! $empty_image ) :
	?>

	<div class="stm-placeholder <?php echo esc_attr( $classes ); ?>">
		<?php if ( ! empty( $image ) ) : ?>
			<div class="inner">
				<div class="stm-image-preview" data-media="<?php echo absint( $attachment_id ); ?>" data-id="<?php echo esc_attr( $item_id ); ?>" <?php echo esc_attr( $image ); ?>>
					<i class="fas fa-times fa-remove-loaded" data-id="<?php echo esc_attr( $item_id ); ?>" data-media="<?php echo absint( $attachment_id ); ?>"></i>
				</div>
			</div>
		<?php else : ?>
			<label for="stm_car_gallery_add" class="inner">
				<i class="stm-service-icon-photos"></i>
				<span class="stm-placeholder__text">
				<?php esc_html_e( 'Add image', 'stm_vehicles_listing' ); ?>
			</span>
			</label>
		<?php endif; ?>
	</div>
	<?php
endif;
