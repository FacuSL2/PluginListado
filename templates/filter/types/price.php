<?php
if ( empty( $options ) ) {
	return;
}

/*Get min and max value*/
reset( $options );
asort( $options );

$start_value = null;

foreach ( $options as $v => $k ) {
	if ( empty( $start_value ) && ( 0 === $v || ! empty( $v ) ) ) {
		$start_value = $v;
		break;
	}
}

if ( strstr( $start_value, '-' ) !== false ) {
	$exploded    = explode( '-', $start_value );
	$start_value = $exploded[1];

	$options = array_splice( $options, 0, count( $options ) - 3 );
	end( $options );

	$explode_end = explode( '-', key( $options ) );
	$end_value   = $explode_end[1];
} else {
	end( $options );
	$end_value = key( $options );
}

/*Current slug*/
$slug = 'price';

$info = apply_filters( 'stm_vl_get_all_by_slug', array(), $slug );

$slider_step = ( ! empty( $info['slider'] ) && ! empty( $info['slider_step'] ) ) ? $info['slider_step'] : 100;

$label_affix = $start_value . ' — ' . $end_value;

$min_value = $start_value;
$max_value = $end_value;

if ( isset( $_COOKIE['stm_current_currency'] ) ) {
	$cookie      = explode( '-', $_COOKIE['stm_current_currency'] );
	$start_value = ( $start_value * $cookie[1] );
	$end_value   = ( $end_value * $cookie[1] );
	$min_value   = $start_value;
	$max_value   = $end_value;
}

if ( ! empty( $_GET[ 'min_' . $slug ] ) ) {
	$min_value = intval( $_GET[ 'min_' . $slug ] );
}

if ( ! empty( $_GET[ 'max_' . $slug ] ) ) {
	$max_value = intval( $_GET[ 'max_' . $slug ] );
}

$vars = array(
	'slug'        => $slug,
	'js_slug'     => str_replace( '-', 'stmdash', $slug ),
	'label'       => stripslashes( $label_affix ),
	'start_value' => $start_value,
	'end_value'   => $end_value,
	'min_value'   => $min_value,
	'max_value'   => $max_value,
	'slider_step' => $slider_step,
);

?>

<div class="stm-filter-listing-directory-price">
	<div class="stm-accordion-single-unit price">
		<a class="title" data-toggle="collapse" href="#price" aria-expanded="true">
			<h5><?php echo esc_html__( 'Select Price', 'stm_vehicles_listing' ); ?></h5>
			<span class="minus"></span>
		</a>
		<div class="stm-accordion-content collapse in content" id="price">
			<div class="stm-accordion-content-wrapper">
				<div class="stm-price-range-unit">
					<div class="stm-price-range"></div>
				</div>
				<div class="row">
					<div class="col-md-6 col-sm-6 col-md-wider-right">
						<input type="text" name="min_price" id="stm_filter_min_price"/>
					</div>
					<div class="col-md-6 col-sm-6 col-md-wider-left">
						<input type="text" name="max_price" id="stm_filter_max_price"/>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<!--Init slider-->
<?php do_action( 'stm_listings_load_template', 'filter/types/slider-js', $vars ); ?>
