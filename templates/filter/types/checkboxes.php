<?php
$checkboxes = stm_get_car_filter_checkboxes();

if ( stm_is_multilisting() ) {
	if ( ! empty( $post_type ) && apply_filters( 'stm_listings_post_type', 'listings' ) !== $post_type ) {
		$checkboxes = stm_get_car_filter_checkboxes( $post_type );
	}
}

$selected_options = array();

if ( ! empty( $checkboxes ) ) {
	foreach ( $checkboxes as $checkbox ) {

		$listing_rows_numbers_default_expanded = 'false';
		if ( isset( $checkbox['listing_rows_numbers_default_expanded'] ) && 'open' === $checkbox['listing_rows_numbers_default_expanded'] ) {
			$listing_rows_numbers_default_expanded = 'true';
		}

		if ( ! empty( $_GET[ $checkbox['slug'] ] ) ) {
			$selected_options = sanitize_text_field( $_GET[ $checkbox['slug'] ] );
			if ( ! is_array( $selected_options ) ) {
				$selected_options = array( '0' => $selected_options );
			}
		}

		if ( class_exists( \MotorsVehiclesListing\FriendlyUrl::class ) ) {
			$args = \MotorsVehiclesListing\FriendlyUrl::$for_filter;
			if ( ! empty( $args[ $checkbox['slug'] ] ) ) {
				$selected_options = $args[ $checkbox['slug'] ];
				if ( ! is_array( $selected_options ) ) {
					$selected_options = array( '0' => $selected_options );
				}
			}
		}

		if ( ! empty( $checkbox['enable_checkbox_button'] ) && 1 === $checkbox['enable_checkbox_button'] ) {
			$stm_checkbox_ajax_button = 'stm-ajax-checkbox-button';
		} else {
			$stm_checkbox_ajax_button = 'stm-ajax-checkbox-instant';
		}
		?>

		<?php
		$terms_args = array(
			'orderby'    => 'name',
			'order'      => 'ASC',
			'hide_empty' => true,
			'fields'     => 'all',
			'pad_counts' => false,
		);
		?>
		<div
			class="stm-accordion-single-unit stm-listing-directory-checkboxes stm-<?php echo esc_attr( $checkbox['listing_rows_numbers'] . ' ' . $stm_checkbox_ajax_button ); ?>">
			<a class="title
			<?php
			echo ( wp_is_mobile() || 'false' === esc_attr( $listing_rows_numbers_default_expanded ) ) ? 'collapsed' : ''; ?> " data-toggle="collapse" href="#accordion-<?php echo esc_attr( $checkbox['slug'] ); //phpcs:ignore?>"
			aria-expanded="<?php echo esc_attr( $listing_rows_numbers_default_expanded ); ?>">
				<h5><?php echo esc_html( $checkbox['single_name'] ); ?></h5>
				<span class="minus"></span>
			</a>
			<div class="stm-accordion-content">
				<div class="collapse content
				<?php
				echo ( ! wp_is_mobile() && 'true' === esc_attr( $listing_rows_numbers_default_expanded ) ) ? 'in' : ''; ?>" id="accordion-<?php echo esc_attr( $checkbox['slug'] ); //phpcs:ignore?>">
					<div class="stm-accordion-content-wrapper stm-accordion-content-padded">
						<div class="stm-accordion-inner">
							<?php
							$terms = get_terms( $checkbox['slug'], $terms_args );

							if ( ! empty( $terms ) ) {
								foreach ( $terms as $img_term ) {
									$image = get_term_meta( $img_term->term_id, 'stm_image', true );
									if ( ! empty( $image ) ) :
										?>
										<label class="stm-option-label">
										<?php
										$image = wp_get_attachment_image_src( $image, 'stm-img-190-132' );

										if ( ! empty( $image ) ) :
											?>
											<div class="stm-option-image">
												<img src="<?php echo esc_url( $image[0] ); ?>"/>
											</div>
											<?php
										endif;
										?>
										<input type="checkbox" name="<?php echo esc_attr( $checkbox['slug'] ); ?>[]"
											value="<?php echo esc_attr( $img_term->slug ); ?>"
											<?php echo ( in_array( $img_term->slug, $selected_options, true ) ) ? 'checked' : ''; ?>
										/>
										<span><?php echo esc_attr( $img_term->name ); ?></span>
									<?php endif; ?>
									</label>
									<?php
								}

								foreach ( $terms as $rest_term ) {
									$image = get_term_meta( $rest_term->term_id, 'stm_image', true );
									if ( empty( $image ) ) :
										?>
										<label class="stm-option-label">
										<input type="checkbox" name="<?php echo esc_attr( $checkbox['slug'] ); ?>[]"
											value="<?php echo esc_attr( $rest_term->slug ); ?>"
											<?php echo ( in_array( $rest_term->slug, $selected_options, true ) ) ? 'checked' : ''; ?>
										/>
										<span><?php echo esc_attr( $rest_term->name ); ?></span>
									<?php endif; ?>
									</label>
									<?php
								}
							}

							if ( ! empty( $checkbox['enable_checkbox_button'] ) && 1 === $checkbox['enable_checkbox_button'] ) :
								?>
								<div class="clearfix"></div>
								<div class="stm-checkbox-submit">
									<a class="button" href="#"><?php echo esc_html_e( 'Apply', 'stm_vehicles_listing' ); ?></a>
								</div>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
}
