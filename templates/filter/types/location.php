<!--Location inputs-->
<?php
if ( apply_filters( 'stm_enable_location', false ) ) :
	$stm_location = apply_filters( 'stm_listings_input', null, 'ca_location' );
	if ( $stm_location ) {
		$stm_location = sanitize_text_field( $stm_location );
	}

	$stm_lng = apply_filters( 'stm_listings_input', null, 'stm_lng' );
	if ( $stm_lng ) {
		$stm_lng = sanitize_text_field( $stm_lng );
	}

	$stm_lat = apply_filters( 'stm_listings_input', null, 'stm_lat' );
	if ( $stm_lat ) {
		$stm_lat = sanitize_text_field( $stm_lat );
	}

	$enable_distance = apply_filters( 'stm_me_get_nuxy_mod', true, 'enable_distance_search' );
	if ( $enable_distance ) {
		$radius = apply_filters( 'stm_distance_search_value', 100 );
		$radius = ( ! empty( $radius ) ) ? $radius : 100;

		$radius_array = array();
		for ( $q = 1; $q <= $radius; $q++ ) {
			$radius_array[ $q ] = array( 'label' => $q );
		}
	}

	do_action( 'stm_google_places_script', 'enqueue' );
	?>
	<div class="col-md-12 col-sm-12">
		<div class="form-group boats-location">
			<div class="stm-location-search-unit">
				<input type="text"
						id="ca_location_listing_filter"
						autocomplete="Off"
						aria-label="<?php esc_attr_e( 'Search listings by location', 'stm_vehicles_listing' ); ?>"
						class="stm_listing_search_location <?php echo empty( $stm_location ) ? 'empty' : ''; ?>"
						name="ca_location"
						value="<?php echo esc_attr( $stm_location ); ?>"
						placeholder="<?php esc_attr_e( 'Any location', 'stm_vehicles_listing' ); ?>"
					/>
				<span class="stm-location-reset-field" style="display: none;">
					<svg viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M14 1.41L12.59 0L7 5.59L1.41 0L0 1.41L5.59 7L0 12.59L1.41 14L7 8.41L12.59 14L14 12.59L8.41 7L14 1.41Z"/>
					</svg>
				</span>
				<input type="hidden" name="stm_lat" value="<?php echo esc_attr( floatval( $stm_lat ) ); ?>">
				<input type="hidden" name="stm_lng" value="<?php echo esc_attr( floatval( $stm_lng ) ); ?>">
			<?php if ( ! $enable_distance ) : ?>
					<input type="hidden" name="stm_location_address">
				<?php endif; ?>
			</div>
		</div>
	</div>
	<?php

	if ( $enable_distance && apply_filters( 'stm_enable_location', false ) && ( apply_filters( 'is_listing', array() ) || apply_filters( 'stm_is_boats', false ) || apply_filters( 'stm_is_car_dealer', false ) || apply_filters( 'stm_is_motorcycle', false ) ) ) {
		do_action(
			'stm_listings_load_template',
			'filter/types/slide',
			array(
				'taxonomy' => array(
					'slug'        => 'search_radius',
					'single_name' => esc_html__( 'Search radius', 'stm_vehicles_listing' ),
				),
				'options'  => $radius_array,
				'location' => $stm_location,
			)
		);
	}
	?>
<?php endif; ?>
