<?php
/**
 * Extract from vars variable
 * @var $js_slug
 * @var $min_value
 * @var $max_value
 * @var $slider_step
 * @var $slug
 * @var $tab_name
 * @var $affix
 * */

$slider_values = ( ! empty( $tab_name ) );

if ( empty( $affix ) ) {
	$affix = '';
}

if ( empty( $start_value ) ) {
	$start_value = 0;
}

if ( empty( $end_value ) ) {
	$end_value = 0;
}
?>

<script type="text/javascript">
	if ( typeof stm_range_slug === "undefined" ) {
		var stm_range_slug = [];
	}

	stm_range_slug.push( "<?php echo esc_js( $js_slug ); ?>" );
	window.stm_options_<?php echo esc_js( $js_slug ); ?>;

	(function ($) {
		function stm_init_range_slider_<?php echo esc_js( $js_slug ); ?>(){
			let affix 		  = "<?php echo esc_js( $affix ); ?>",
				suffix		  = "<?php echo esc_js( $slug ); ?>",
				tab_name	  = "<?php echo esc_js( $tab_name ); ?>",
				stmStartValue = parseInt( <?php echo esc_js( $start_value ); ?> ),
				stmEndValue   = parseInt( <?php echo esc_js( $end_value ); ?> ),
				stmMinValue   = parseInt( <?php echo esc_js( $min_value ); ?> ),
				stmMaxValue   = parseInt( <?php echo esc_js( $max_value ); ?> ),
				is_price	  = Boolean( <?php echo esc_js( apply_filters( 'stm_is_listing_price_field', false, $slug ) ); ?> ),
				slider_values = Boolean( <?php echo esc_js( $slider_values ); ?> ),
				element_min   = '#stm_filter_min_' + suffix + '_' + tab_name,
				element_max	  = '#stm_filter_max_' + suffix + '_' + tab_name,
				range 		  = '.stm-' + suffix + '_' + tab_name + '-range';

			function numberWithSpaces(x) {
				return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, " ");
			}

			window.stm_options_<?php echo esc_js( $js_slug ); ?> = {
				range: true,
				min: stmStartValue,
				max: stmEndValue,
				values: [ stmMinValue, stmMaxValue ],
				step: <?php echo esc_js( $slider_step ); ?>,
				disabled: ( typeof stm_elementor_editor_mode !== "undefined" ),
				create: function (event) {
					let $wrap = $( event.target ).closest( '.taxonomy_range_wrap' );

					if ( stmStartValue === stmMinValue ) {
						$( element_min, $wrap ).attr( 'placeholder', stmMinValue );
					} else {
						$( element_min, $wrap ).val( stmMinValue );
					}

					if ( stmEndValue === stmMaxValue ) {
						$( element_max, $wrap ).attr( 'placeholder', stmMaxValue );
					} else {
						$( element_max, $wrap ).val( stmMaxValue );
					}
				},
				slide: function (event, ui) {
					let stmText,
						min = ui.values.shift(),
						max = ui.values.shift(),
						$wrap = $( event.target ).closest( '.taxonomy_range_wrap' );

					$( element_min, $wrap ).val( min );
					$( element_max, $wrap ).val( max );

					if ( is_price ) {
						let stmCurrency    = "<?php echo esc_js( stm_get_price_currency() ); ?>",
							stmPriceDel    = "<?php echo esc_js( apply_filters( 'stm_me_get_nuxy_mod', ' ', 'price_delimeter' ) ); ?>",
							stmCurrencyPos = "<?php echo esc_js( apply_filters( 'stm_me_get_nuxy_mod', 'left', 'price_currency_position' ) ); ?>",
							startValue 	   = ( 'left' === stmCurrencyPos ) ? stmCurrency + numberWithSpaces( min ) : numberWithSpaces( min ) + stmCurrency,
							endValue       = ( 'left' === stmCurrencyPos ) ? stmCurrency + numberWithSpaces( max ) : numberWithSpaces( max ) + stmCurrency;

						stmText = stm_get_price_view( startValue, stmCurrency, stmCurrencyPos, stmPriceDel ) + affix + ' - ' + stm_get_price_view( endValue, stmCurrency, stmCurrencyPos, stmPriceDel ) + affix;
					} else {
						stmText = min + affix + ' - ' + max + affix;
					}

					$( '.filter-' + suffix + '_' + tab_name + ' .stm-current-slider-labels', $wrap ).html( stmText );
				},
			};

			if ( typeof stm_elementor_editor_mode === "undefined" ) {
				$(document).on(
					'slidestop',
					range,
					function () {
						if (typeof STMListings === "object" && !slider_values) {
							if (typeof STMListings.stm_disable_rest_filters === "function") {
								STMListings.stm_disable_rest_filters($(this), 'listings-binding');
							}

							$(this).closest('form').trigger('submit');
						}
					}
				);

				$(document).on('focusout', element_min, function () {
					let $this = $(this),
						value = parseInt($this.val());

					if (isNaN(value)) {
						value = stmMinValue;
					}

					$(range, $this.closest('form')).slider('values', 0, value);
				});

				$(document).on('keyup', element_min, function () {
					let $this = $(this),
						value = parseInt($this.val());

					if (value < stmMinValue) {
						$(range, $this.closest('form')).slider('values', 0, stmMinValue);
						$this.val(stmMinValue);
					}
				});

				$(document).on('focusout', element_max, function () {
					let $this = $(this),
						value = parseInt($this.val());

					if (isNaN(value)) {
						value = stmMaxValue;
					}

					$(range, $this.closest('form')).slider('values', 1, value);
				});

				$(document).on('keyup', element_max, function () {
					let $this = $(this),
						value = parseInt($this.val());

					if (value > stmMaxValue) {
						$(range, $this.closest('form')).slider('values', 1, stmMaxValue);
						$this.val(stmMaxValue);
					}
				});
			}
		}

		function stm_filter_range_slider_<?php echo esc_js( $js_slug ); ?>() {
			let tab_name = "<?php echo esc_js( $tab_name ); ?>",
				suffix	 = "<?php echo esc_js( $slug ); ?>",
				range 	 = '.stm-' + suffix + '_' + tab_name + '-range';

			$( range ).slider( window.stm_options_<?php echo esc_js( $js_slug ); ?> );
		}

		$(document).ready(function () {
			stm_init_range_slider_<?php echo esc_js( $js_slug ); ?>();
			stm_filter_range_slider_<?php echo esc_js( $js_slug ); ?>();
		});
	})(jQuery);
</script>
