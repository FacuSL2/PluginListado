<?php
/**
 * @var $location
 * @var $taxonomy
 * */

if ( empty( $options ) ) {
	return;
}

/*Get min and max value*/
reset( $options );
asort( $options );

$start_value = 0;

end( $options );
$end_value = key( $options );

/*Current slug*/
$slug = $taxonomy['slug'];

if ( 'search_radius' === $slug ) {
	$affix = '';
	if ( apply_filters( 'stm_is_motorcycle', false ) ) {
		$affix = '&nbsp;' . apply_filters( 'stm_distance_measure_unit', '' );
	}
}

$max_value = $end_value;

if ( ! empty( $_GET[ 'max_' . $slug ] ) ) {
	$max_value = intval( $_GET[ 'max_' . $slug ] );
}

$vars = array(
	'slug'        => $slug,
	'affix'       => $affix,
	'js_slug'     => str_replace( '-', 'stmdash', $slug ),
	'start_value' => $start_value,
	'end_value'   => $end_value,
	'max_value'   => $max_value,
);

$label_affix = $vars['max_value'] . $affix;

if ( apply_filters( 'stm_is_listing_price_field', false, $slug ) ) {
	$label_affix = apply_filters( 'stm_filter_price_view', '', $vars['max_value'] );
}

$vars['label'] = wp_kses_stripslashes( $label_affix );

$style = ( empty( $location ) ) ? 'display: none;' : '';
?>

<div class="col-md-12 col-sm-12">
	<div class="filter-<?php echo esc_attr( $vars['slug'] ); ?> stm-slider-filter-type-unit" style="<?php echo esc_attr( $style ); ?>">
		<div class="clearfix">
			<h5 class="pull-left"><?php echo esc_html( $taxonomy['single_name'] ); ?></h5>
			<div class="stm-current-slider-labels"><?php echo esc_html( $vars['label'] ); ?></div>
		</div>
		<div class="stm-price-range-unit">
			<div class="stm-<?php echo esc_attr( $vars['slug'] ); ?>-range stm-filter-type-slider"></div>
		</div>
		<div class="row">
			<div class="col-md-12 col-sm-12">
				<input
						type="text"
						name="max_<?php echo esc_attr( $vars['slug'] ); ?>"
						id="stm_slide_filter_max_<?php echo esc_attr( $vars['slug'] ); ?>"
						class="form-control"
						aria-label="
						<?php
							printf(
								/* translators: %s label */
								esc_html__( 'Enter %s', 'stm_vehicles_listing' ),
								esc_html( $vars['label'] )
							)
							?>
						"
				/>
				<span class="stm_unit_measurement"><?php echo esc_html( apply_filters( 'stm_distance_measure_unit', '' ) ); ?></span>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
	var stmOptions_<?php echo esc_js( $vars['js_slug'] ); ?>;

	function stm_slide_filter() {
		let affix 		      = "<?php echo esc_js( $affix ); ?>",
			suffix		  	  = "<?php echo esc_js( $slug ); ?>",
			loadValue		  = parseInt( <?php echo ( isset( $_GET['max_search_radius'] ) ? absint( $_GET['max_search_radius'] ) : 0 ); ?> ),
			stmMaxRadiusValue = parseInt( <?php echo esc_js( $end_value ); ?> ),
			range 		  	  = '.stm-' + suffix + '-range',
			max_radius  	  = '#stm_slide_filter_max_' + suffix;

		if ( loadValue > stmMaxRadiusValue ) {
			loadValue = stmMaxRadiusValue;
		}

		stmOptions_<?php echo esc_js( $vars['js_slug'] ); ?> = {
			step: 1,
			min: parseInt( <?php echo esc_js( $start_value ); ?> ),
			max: stmMaxRadiusValue,
			value: loadValue,
			slide: function (event, ui) {
				jQuery( max_radius ).val( ui.value );

				jQuery('.filter-' + suffix + ' .stm-current-slider-labels').html( ui.value + '&nbsp;' + affix );
			}
		};

		jQuery( document ).on(
			'slidestop',
			range,
			function () {
				if ( typeof STMListings !== "undefined" && typeof STMListings.stm_disable_rest_filters !== "undefined" ) {
					STMListings.stm_disable_rest_filters( jQuery( this ), 'listings-binding' );
				}
			}
		);

		jQuery( range ).slider( stmOptions_<?php echo esc_js( $vars['js_slug'] ); ?> );

		if ( loadValue > 0 ) {
			jQuery( max_radius ).val( loadValue );
		} else {
			jQuery( max_radius ).attr('placeholder', loadValue);
		}

		jQuery( max_radius ).on('keyup focusout', function () {
			let $this = jQuery( this );

			if ( $this.val() > stmMaxRadiusValue ) {
				jQuery( range ).slider( "option", "value", stmMaxRadiusValue );
				$this.val( stmMaxRadiusValue );
			} else {
				jQuery( range ).slider( "option", "value", $this.val() );
			}
		});
	}

	(function ($) {
		$(document).ready(() => {
			let fieldLocation = $('#ca_location_listing_filter');

			if ( $(window).width() > 1024 && fieldLocation.length && fieldLocation.val().length ) {
				stm_slide_filter();
			}
		});
	})(jQuery);
</script>
