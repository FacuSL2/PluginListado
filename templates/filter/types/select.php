<?php
/**
 * @var $name
 * @var $multiple
 * @var $placeholder
*/

$multiple = $multiple ?? false;
$sel_name = ( ( isset( $maxify ) && $maxify ) ) ? 'max_' . $name : $name;
$class    = 'filter-select';
if ( $multiple ) {
	$sel_name         = $sel_name . '[]';
	$placeholder      = array_shift( $options );
	$data_placeholder = 'data-placeholder=" ' . esc_attr( $placeholder['label'] ) . '"';
	$class            = $class . ' stm-multiple-select';
}

$aria_label = '';
if ( $multiple ) {
	$aria_label = $placeholder['label'];
} elseif ( ! empty( $options ) ) {
	$option     = reset( $options );
	$aria_label = $option['label'];
}

$aria_label = sprintf(
	/* translators: %s label */
	__( 'Select %s', 'stm_vehicles_listing' ),
	strtolower( $aria_label )
);
?>
<select aria-label="<?php echo esc_attr( $aria_label ); ?>" <?php echo $multiple ? 'multiple="multiple"' : ''; ?>
	<?php echo $multiple ? 'data-placeholder="' . esc_attr( $placeholder['label'] ) . '"' : ''; ?>
		name="<?php echo esc_attr( $sel_name ); ?>"
		class="<?php echo esc_attr( $class ); ?>" >
	<?php
	if ( ! empty( $options ) ) :
		foreach ( $options as $value => $option ) :
			$parent_attr = ( ! empty( $option['parent'] ) ) ? $option['parent'] : '';
			$value_attr  = ( ! empty( $option['option'] ) ) ? $option['option'] : '';
			?>
			<option data-parent="<?php echo esc_attr( $parent_attr ); ?>" value="<?php echo esc_attr( $value_attr ); ?>" <?php selected( $option['selected'] ); ?> <?php disabled( $option['disabled'] ); ?>>
				<?php
				$label = $option['label'] ?? '';

				if ( apply_filters( 'stm_is_listing_price_field', false, $name ) ) {
					if ( ! empty( $option['option'] ) ) {
						echo esc_html( apply_filters( 'stm_filter_price_view', '', $option['option'] ) );
					} else {
						echo esc_html( apply_filters( 'stm_dynamic_string_translation', $label, 'Filter Option Label for ' . $label ) );
					}
				} else {
					echo esc_html( apply_filters( 'stm_dynamic_string_translation', $label, 'Filter Option Label for ' . $label ) );
				}
				?>
			</option>
			<?php
		endforeach;
	endif;
	?>
</select>
