<?php
/**
 * @var string $label
 * @var string $taxonomy
 * @var $slider_values
 * */

if ( empty( $options ) ) {
	return;
}

$tab_name = $tab_name ?? '';

$start_value = $options[0];
$end_value   = ( count( $options ) > 0 ) ? $options[ count( $options ) - 1 ] : 0;
$info        = apply_filters( 'stm_vl_get_all_by_slug', array(), $taxonomy );
$slider_step = ( ! empty( $info['slider'] ) && ! empty( $info['slider_step'] ) ) ? $info['slider_step'] : 1;
$affix       = $info['number_field_affix'];
$label_affix = $start_value . $affix . ' - ' . $end_value . $affix;
$min_value   = $start_value;
$max_value   = $end_value;

if ( apply_filters( 'stm_is_listing_price_field', false, $taxonomy ) ) {
	if ( isset( $_COOKIE['stm_current_currency'] ) ) {
		$cookie      = explode( '-', $_COOKIE['stm_current_currency'] );
		$start_value = ( $start_value * $cookie[1] );
		$end_value   = ( $end_value * $cookie[1] );
		$min_value   = $start_value;
		$max_value   = $end_value;
	}

	$label_affix = apply_filters( 'stm_filter_price_view', '', $start_value ) . $affix . ' - ' . apply_filters( 'stm_filter_price_view', '', $end_value ) . $affix;
}

if ( ! empty( $_GET[ 'min_' . $taxonomy ] ) ) {
	$min_value = intval( $_GET[ 'min_' . $taxonomy ] );
}

if ( ! empty( $_GET[ 'max_' . $taxonomy ] ) ) {
	$max_value = intval( $_GET[ 'max_' . $taxonomy ] );
}

$vars = array(
	'slug'        => $taxonomy,
	'js_slug'     => str_replace( '-', 'stmdash', $taxonomy . '_' . $tab_name ),
	'label'       => stripslashes( $label_affix ),
	'start_value' => $start_value,
	'end_value'   => $end_value,
	'min_value'   => $min_value,
	'max_value'   => $max_value,
	'slider_step' => $slider_step,
	'tab_name'    => $tab_name,
	'affix'       => $affix,
);

?>
<div class="taxonomy_range_wrap">
	<div class="filter-<?php echo esc_attr( $vars['slug'] . '_' . $vars['tab_name'] ); ?> vc_taxonomy mts_semeht_taxonomy">
		<?php if ( ! empty( $tab_name ) ) : ?>
			<div class="clearfix">
				<label class="pull-left"><?php echo esc_html( apply_filters( 'stm_dynamic_string_translation', $label, 'Label category ' . $label ) ); ?></label>
				<div class="stm-current-slider-labels"><?php echo esc_html( $vars['label'] ); ?></div>
			</div>
		<?php else : ?>
			<label><?php echo esc_html( apply_filters( 'stm_dynamic_string_translation', $label, 'Label category ' . $label ) ); ?></label>
		<?php endif ?>
		<div class="stm-taxonomy-range-unit">
			<div class="stm-<?php echo esc_attr( $taxonomy . '_' . $tab_name ); ?>-range ui-slider-wrap"></div>
		</div>
		<input type="hidden" name="min_<?php echo esc_attr( $taxonomy ); ?>" class="stm-min-value" id="stm_filter_min_<?php echo esc_attr( $taxonomy . '_' . $tab_name ); ?>"/>
		<input type="hidden" name="max_<?php echo esc_attr( $taxonomy ); ?>" class="stm-max-value" id="stm_filter_max_<?php echo esc_attr( $taxonomy . '_' . $tab_name ); ?>"/>
	</div>
</div>

<!--Init slider-->
<?php do_action( 'stm_listings_load_template', 'filter/types/vc_slider-js', $vars ); ?>
