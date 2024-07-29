<?php
/**
 * Extract from vars variable
 * @var $js_slug
 * @var $min_value
 * @var $max_value
 * @var $slider_step
 * @var $slug
 * */

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
				stmStartValue = parseInt( <?php echo esc_js( $start_value ); ?> ),
				stmEndValue   = parseInt( <?php echo esc_js( $end_value ); ?> ),
				stmMinValue   = parseInt( <?php echo esc_js( $min_value ); ?> ),
				stmMaxValue   = parseInt( <?php echo esc_js( $max_value ); ?> ),
				is_price	  = Boolean( <?php echo esc_js( apply_filters( 'stm_is_listing_price_field', false, $slug ) ); ?> ),
				element_min   = '#stm_filter_min_' + suffix,
				element_max	  = '#stm_filter_max_' + suffix,
				range 		  = '.stm-' + suffix + '-range';

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
				create: function () {
					if ( stmStartValue === stmMinValue ) {
						$( element_min ).attr( 'placeholder', stmMinValue );
					} else {
						$( element_min ).val( stmMinValue );
					}

					if ( stmEndValue === stmMaxValue ) {
						$( element_max ).attr( 'placeholder', stmMaxValue );
					} else {
						$( element_max ).val( stmMaxValue );
					}
				},
				slide: function (event, ui) {
					let stmText,
						min = ui.values.shift(),
						max = ui.values.shift();

					$( element_min ).val( min );
					$( element_max ).val( max );

					if ( is_price ) {
						let stmCurrency    = "<?php echo esc_js( stm_get_price_currency() ); ?>",
							stmPriceDel    = "<?php echo esc_js( apply_filters( 'stm_me_get_nuxy_mod', ' ', 'price_delimeter' ) ); ?>",
							stmCurrencyPos = "<?php echo esc_js( apply_filters( 'stm_me_get_nuxy_mod', 'left', 'price_currency_position' ) ); ?>",
							startValue 	   = ( 'left' === stmCurrencyPos ) ? stmCurrency + numberWithSpaces( min ) : numberWithSpaces( min ) + stmCurrency,
							endValue       = ( 'left' === stmCurrencyPos ) ? stmCurrency + numberWithSpaces( max ) : numberWithSpaces( max ) + stmCurrency;

						stmText = stm_get_price_view( startValue, stmCurrency, stmCurrencyPos, stmPriceDel ) + ' - ' + stm_get_price_view( endValue, stmCurrency, stmCurrencyPos, stmPriceDel );
					} else {
						stmText = min + affix + ' — ' + max + affix;
					}

					$( '.filter-' + suffix + ' .stm-current-slider-labels' ).html( stmText );
				},
			};

			if ( typeof stm_elementor_editor_mode === "undefined" ) {
				$(document).on(
					'slidestop',
					range,
					function () {
						if (typeof STMListings !== "undefined" && typeof STMListings.stm_disable_rest_filters !== "undefined") {
							STMListings.stm_disable_rest_filters($(this), 'listings-binding');
						}

						$(this).closest('form').trigger('submit');
					}
				);

				let old_min_value = ( stmStartValue !== stmMinValue ) ? stmMinValue : 0,
					old_max_value = ( stmEndValue !== stmMaxValue ) ? stmMaxValue : 0;

				$( document ).on('focusout', element_min, function () {
					let $this = $(this),
						value = parseInt( $this.val() );

					if ( isNaN( value ) ) {
						value = stmStartValue;
					}

					if ( ( value < stmStartValue || value > stmEndValue ) ) {
						$( range ).slider( 'values', 0, stmStartValue );
						$this.val( stmStartValue );
					} else {
						$( range ).slider( 'values', 0, value );
					}

					if ( old_min_value !== value ) {
						$this.closest( 'form' ).trigger( 'submit' );
					}

					old_min_value = value;
				});

				$( document ).on('keyup', element_min, function (e) {
					let $this   = $(this),
						value   = parseInt( $this.val() ),
						keyCode = e.keyCode || e.charCode;

					if ( $this.is(':focus') && ( e.key === "Enter" || keyCode === 13 ) ) {
						$( range ).slider( 'values', 0, value );

						if ( old_min_value !== value ) {
							$this.closest( 'form' ).trigger( 'submit' );
						}

						old_min_value = value;
					}
				});

				$( document ).on('focusout', element_max, function () {
					let $this = $(this),
						value = parseInt( $this.val() );

					if ( isNaN( value ) ) {
						value = stmMaxValue;
					}

					if ( value.toString().length > stmEndValue.toString().length && ( value > stmEndValue || value < stmStartValue ) ) {
						$( range ).slider( 'values', 1, stmEndValue );
						$this.val( stmEndValue );
					} else {
						$( range ).slider( 'values', 1, value );
					}

					if ( old_max_value !== value ) {
						$this.closest( 'form' ).trigger( 'submit' );
					}

					old_max_value = value;
				});

				$( document ).on('keyup', element_max, function (e) {
					let $this   = $(this),
						value   = parseInt( $this.val() ),
						keyCode = e.keyCode || e.charCode;

					if ( $this.is(':focus') && ( e.key === "Enter" || keyCode === 13 ) ) {
						$( range ).slider( 'values', 1, value );

						if ( old_max_value !== value ) {
							$this.closest( 'form' ).trigger( 'submit' );
						}

						old_max_value = value;
					}
				});
			}

		}

		function stm_filter_range_slider_<?php echo esc_js( $js_slug ); ?>() {
			let suffix = "<?php echo esc_js( $slug ); ?>",
				range  = '.stm-' + suffix + '-range';

			$( range ).slider( window.stm_options_<?php echo esc_js( $js_slug ); ?> );
		}

		$(document).ready(function () {
			stm_init_range_slider_<?php echo esc_js( $js_slug ); ?>();
			stm_filter_range_slider_<?php echo esc_js( $js_slug ); ?>();
		});
	})(jQuery);
</script>
