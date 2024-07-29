<?php
$filter_links = stm_get_car_filter_links();

if ( ! empty( $filter_links ) && ! empty( $filter['options'] ) ) : ?>
	<div class="stm-filter-links">
		<?php
		foreach ( $filter_links as $filter_link ) :
			$filter_links_default_expanded = 'false';
			$options                       = $filter['options'];
			$tax_slug                      = $filter_link['slug'];

			if ( isset( $filter_link['filter_links_default_expanded'] ) && 'open' === $filter_link['filter_links_default_expanded'] ) {
				$filter_links_default_expanded = 'true';
			}

			if ( ! empty( $options[ $tax_slug ] ) ) :
				$filter_links_cats = $options[ $tax_slug ];

				if ( ! empty( $filter_links_cats ) ) :
					?>

					<style type="text/css">
						.stm-filter_<?php echo esc_attr( $tax_slug ); ?> {display: none;}
					</style>

					<div class="stm-accordion-single-unit" id="stm-filter-link-<?php echo esc_attr( $filter_link['slug'] ); ?>">
						<a class="title
						<?php
						echo ( wp_is_mobile() || 'false' === esc_attr( $filter_links_default_expanded ) ) ? 'collapsed' : ''; //phpcs:ignore
						?> " data-toggle="collapse" href="#<?php echo esc_attr( $filter_link['slug'] ); ?>" aria-expanded="<?php echo 'false'; //phpcs:ignore?>">
							<h5><?php echo esc_html( apply_filters( 'stm_dynamic_string_translation', $filter_link['single_name'], 'Filter Name ' . $filter_link['single_name'] ) ); ?></h5>
							<span class="minus"></span>
						</a>

						<div class="stm-accordion-content">
							<div class="collapsed collapse content
							<?php
							echo ( ! wp_is_mobile() && 'true' === esc_attr( $filter_links_default_expanded ) ) ? 'in' : ''; //phpcs:ignore ?> "
								id="<?php echo esc_attr( $filter_link['slug'] ); ?>">
								<ul class="list-style-3">
									<?php
									foreach ( $filter_links_cats as $key => $filter_links_cat ) :
										if ( empty( $key ) || empty( $filter_links_cat['label'] ) ) {
											continue;
										}

										$stm_term = get_term_by( 'slug', $key, $tax_slug );

										$count = '0';
										if ( ! empty( $stm_term ) && is_object( $stm_term ) && 0 < $stm_term->count ) {
											$count = $stm_term->count;
										}
										?>
										<li
											class="stm-single-filter-link"
											data-slug="<?php echo esc_attr( $filter_link['slug'] ); ?>"
											data-value="<?php echo esc_attr( $key ); ?>"
										>
											<a href="<?php echo esc_attr( apply_filters( 'stm_listings_current_url', '' ) . '?' . $filter_link['slug'] . '=' . $key ); ?>">
												<?php echo esc_html( $filter_links_cat['label'] ) . ' <span>(' . esc_html( $count ) . ')</span>'; ?>
											</a>
										</li>
									<?php endforeach; ?>
								</ul>
							</div>
						</div>
					</div>
					<?php
				endif;
			endif;
		endforeach;
		?>
	</div>
<?php endif; ?>
