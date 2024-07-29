<?php
$enable_features_search = apply_filters( 'stm_me_get_nuxy_mod', false, 'enable_features_search', false );
if ( $enable_features_search && ! empty( $taxonomy ) && taxonomy_exists( $taxonomy ) ) :
	$features = get_terms(
		array(
			'taxonomy'               => $taxonomy,
			'hide_empty'             => true,
			'update_term_meta_cache' => false,
		)
	);

	if ( ! is_wp_error( $features ) && ! empty( $features ) ) :
		$selected        = array();
		$search_features = $_GET['stm_features'] ?? '';

		if ( ! empty( $search_features ) ) {
			if ( is_array( $search_features ) ) {
				$selected = array_map( 'sanitize_text_field', $search_features );
			} else {
				$selected[] = sanitize_text_field( $search_features );
			}
		}
		?>
		<div class="col-md-12 col-sm-12 stm_additional_features">
			<div class="form-group type-select">
				<select multiple="multiple"
						data-placeholder="<?php esc_attr_e( 'Additional features', 'stm_vehicles_listing' ); ?>"
						class="filter-select stm-multiple-select"
						aria-label="<?php esc_attr_e( 'Select additional features', 'stm_vehicles_listing' ); ?>"
						name="stm_features[]">
					<?php foreach ( $features as $feature ) : ?>
						<option value="<?php echo esc_attr( $feature->slug ); ?>"
							<?php echo ( in_array( $feature->slug, $selected, true ) ) ? 'selected' : ''; ?>>
							<?php echo esc_html( $feature->name ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>
	<?php endif; ?>
<?php endif; ?>
