<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

function stm_listings_page_options() {
	$options = array(
		'single_name'                           => array(
			'label' => esc_html__( 'Singular name', 'stm_vehicles_listing' ),
			'value' => '',
			'type'  => 'text',
		),
		'plural_name'                           => array(
			'label' => esc_html__( 'Plural name', 'stm_vehicles_listing' ),
			'value' => '',
			'type'  => 'text',
		),
		'slug'                                  => array(
			'label'       => esc_html__( 'Slug', 'stm_vehicles_listing' ),
			'description' => esc_html__( 'Caution, you will not be able to change the link later', 'stm_vehicles_listing' ),
			'value'       => '',
			'type'        => 'text',
		),
		'font'                                  => array(
			'label' => esc_html__( 'Choose icon', 'stm_vehicles_listing' ),
			'value' => '',
			'type'  => 'icon',
		),
		'divider_1'                             => array(
			'type' => 'divider',
		),
		'required_filed'                        => array(
			'label' => esc_html__( 'Required', 'stm_vehicles_listing' ),
			'value' => '',
			'type'  => 'checkbox',
		),
		'numeric'                               => array(
			'label'       => esc_html__( 'Number field', 'stm_vehicles_listing' ),
			'description' => esc_html__( 'Numeric value will be compared in another way (useful for mileage or fuel economy)', 'stm_vehicles_listing' ),
			'value'       => '',
			'type'        => 'checkbox',
		),
		'number_field_affix'                    => array(
			'label'       => '',
			'description' => esc_html__( 'This affix will be shown after number. Example: mi, pcs, etc.', 'stm_vehicles_listing' ),
			'value'       => '',
			'dependency'  => array(
				'slug' => 'numeric',
				'type' => 'not_empty',
			),
			'attributes'  => array(
				'placeholder' => esc_html__( 'Number field affix', 'stm_vehicles_listing' ),
			),
			'type'        => 'text',
		),
		'slider_in_tabs'                        => array(
			'label'       => esc_html__( 'Display field as slider in tabs', 'stm_vehicles_listing' ),
			'description' => esc_html__( 'Only for number field', 'stm_vehicles_listing' ),
			'dependency'  => array(
				'slug' => 'numeric',
				'type' => 'not_empty',
			),
			'value'       => '',
			'type'        => 'checkbox',
		),
		'slider'                                => array(
			'label'       => esc_html__( 'Display field as slider (Only classic filter)', 'stm_vehicles_listing' ),
			'description' => esc_html__( 'Only for number field', 'stm_vehicles_listing' ),
			'dependency'  => array(
				'slug' => 'numeric',
				'type' => 'not_empty',
			),
			'value'       => '',
			'type'        => 'checkbox',
		),
		'slider_step'                           => array(
			'label'       => esc_html__( 'Slider step', 'stm_vehicles_listing' ),
			'description' => '',
			'dependency'  => array(
				array(
					'slug' => 'numeric',
					'type' => 'not_empty',
				),
				array(
					'slug' => 'slider',
					'type' => 'not_empty',
				),
			),
			'value'       => 1,
			'attributes'  => array(
				'placeholder' => esc_html__( 'Enter step number', 'stm_vehicles_listing' ),
			),
			'type'        => 'text',
		),
		'show_inputs'                                => array(
			'label'       => esc_html__( 'Show Inputs', 'stm_vehicles_listing' ),
			'dependency'  => array(
				'slug' => 'slider',
				'type' => 'not_empty',
			),
			'type'        => 'checkbox',
			'value'       => true,
		),
		'use_delimiter'                         => array(
			'label'      => esc_html__( 'Add delimiter', 'stm_vehicles_listing' ),
			'dependency' => array(
				'slug' => 'numeric',
				'type' => 'not_empty',
			),
			'value'      => '',
			'type'       => 'checkbox',
		),
		'listing_price_field'                   => array(
			'label'       => esc_html__( 'Listing price field', 'stm_vehicles_listing' ),
			'description' => esc_html__( 'This field will be determined as the price for listings. Only one field can be assigned as price field!', 'stm_vehicles_listing' ),
			'dependency'  => array(
				'slug' => 'numeric',
				'type' => 'not_empty',
			),
			'value'       => '',
			'type'        => 'checkbox',
		),
		'use_on_car_listing_page'               => array(
			'label'       => esc_html__( 'Use on item grid view', 'stm_vehicles_listing' ),
			'description' => esc_html__( 'Check if you want to see this category on car listing page (machine card)', 'stm_vehicles_listing' ),
			'value'       => '',
			'preview'     => 'grid.jpg',
			'type'        => 'checkbox',
		),
		'use_on_car_archive_listing_page'       => array(
			'label'       => esc_html__( 'Use on item list view', 'stm_vehicles_listing' ),
			'description' => esc_html__( 'Check if you want to see this category on car listing archive page with icon', 'stm_vehicles_listing' ),
			'value'       => '',
			'preview'     => 'list.jpg',
			'type'        => 'checkbox',
		),
		'use_on_single_car_page'                => array(
			'label'       => esc_html__( 'Use on single car page', 'stm_vehicles_listing' ),
			'description' => esc_html__( 'Check if you want to see this category on single car page', 'stm_vehicles_listing' ),
			'value'       => '',
			'preview'     => 'single_car_page.jpg',
			'type'        => 'checkbox',
		),
		'use_on_car_filter'                     => array(
			'label'       => esc_html__( 'Use on car filter', 'stm_vehicles_listing' ),
			'description' => esc_html__( 'Check if you want to see this category in filter', 'stm_vehicles_listing' ),
			'value'       => '',
			'type'        => 'checkbox',
		),
		'divider_2'                             => array(
			'type' => 'divider',
		),
		'is_multiple_select'                    => array(
			'label'       => esc_html__( 'Multiple select for filter', 'stm_vehicles_listing' ),
			'description' => esc_html__( 'Check to make select multiple on filter page', 'stm_vehicles_listing' ),
			'value'       => '',
			'type'        => 'checkbox',
		),
		'use_on_tabs'                           => array(
			'label'       => esc_html__( 'Use in tabs', 'stm_vehicles_listing' ),
			'description' => esc_html__( 'Check if you want to see this in Archive Page and Single Motorcycle Page', 'stm_vehicles_listing' ),
			'value'       => '',
			'preview'     => 'tabs.jpg',
			'type'        => 'checkbox',
		),
		'use_on_car_modern_filter'              => array(
			'label'       => esc_html__( 'Use on car modern filter', 'stm_vehicles_listing' ),
			'description' => esc_html__( 'Check if you want to see this category in modern filter', 'stm_vehicles_listing' ),
			'value'       => '',
			'preview'     => 'modern.png',
			'type'        => 'checkbox',
		),
		'use_on_car_modern_filter_view_images'  => array(
			'label'       => esc_html__( 'Use images for this category', 'stm_vehicles_listing' ),
			'description' => esc_html__( 'Check if you want to see this category with images', 'stm_vehicles_listing' ),
			'value'       => '',
			'type'        => 'checkbox',
		),
		'use_on_car_filter_links'               => array(
			'label'       => esc_html__( 'Use on car filter as block with links', 'stm_vehicles_listing' ),
			'description' => esc_html__( 'Be aware of using both as filter option', 'stm_vehicles_listing' ),
			'value'       => '',
			'preview'     => 'car-filter-as-block-with-links.jpg',
			'type'        => 'checkbox',
		),
		'filter_links_default_expanded'         => array(
			'label'       => esc_html__( 'Use on car filter as block with links', 'stm_vehicles_listing' ),
			'description' => esc_html__( 'Be aware of using both as filter option', 'stm_vehicles_listing' ),
			'value'       => '',
			'type'        => 'radio',
			'dependency'  => array(
				'slug' => 'use_on_car_filter_links',
				'type' => 'not_empty',
			),
			'choices'     => array(
				'open'  => esc_html__( 'Open box by default', 'stm_vehicles_listing' ),
				'close' => esc_html__( 'Close box by default', 'stm_vehicles_listing' ),
			),
		),
		'use_in_footer_search'                  => array(
			'label'   => esc_html__( 'Use in footer search', 'stm_vehicles_listing' ),
			'value'   => '',
			'preview' => 'footer.jpg',
			'type'    => 'checkbox',
		),
		'divider_3'                             => array(
			'type' => 'divider',
		),
		'use_on_directory_filter_title'         => array(
			'label'       => esc_html__( 'Use this category in generated Listing Filter title', 'stm_vehicles_listing' ),
			'description' => esc_html__( 'Enable this field, if you want to include category in Listing Filter title.', 'stm_vehicles_listing' ),
			'value'       => '',
			'preview'     => 'title.jpg',
			'type'        => 'checkbox',
		),
		'use_on_single_listing_page'            => array(
			'label'       => esc_html__( 'Use on single listing page', 'stm_vehicles_listing' ),
			'description' => esc_html__( 'Check if you want to see this category on single page', 'stm_vehicles_listing' ),
			'value'       => '',
			'type'        => 'checkbox',
		),
		'listing_taxonomy_parent'               => array(
			'label'   => esc_html__( 'Set parent taxonomy', 'stm_vehicles_listing' ),
			'value'   => '',
			'type'    => 'select',
			'choices' => stm_listings_parent_choice(),
		),
		'terms_filters_sort_by'                 => array(
			'label'   => esc_html__( 'Sort by filters', 'stm_vehicles_listing' ),
			'value'   => 'count_asc',
			'type'    => 'select',
			'choices' => array(
				'count_asc'  => esc_html__( 'Count ASC', 'stm_vehicles_listing' ),
				'count_desc' => esc_html__( 'Count DESC', 'stm_vehicles_listing' ),
				'name_asc'   => esc_html__( 'Name ASC', 'stm_vehicles_listing' ),
				'name_desc'  => esc_html__( 'Name DESC', 'stm_vehicles_listing' ),
			),
		),
		'listing_rows_numbers_enable'           => array(
			'label'       => esc_html__( 'Use on listing archive as checkboxes', 'stm_vehicles_listing' ),
			'description' => esc_html__( 'Use as checkboxes with images 1 or 2 columns', 'stm_vehicles_listing' ),
			'value'       => '',
			'preview'     => 'column.png',
			'type'        => 'checkbox',
		),
		'listing_rows_numbers'                  => array(
			'label'       => esc_html__( 'Use on listing archive as checkboxes', 'stm_vehicles_listing' ),
			'description' => esc_html__( 'Use as checkboxes with images 1 or 2 columns', 'stm_vehicles_listing' ),
			'value'       => '',
			'type'        => 'radio',
			'dependency'  => array(
				'slug' => 'listing_rows_numbers_enable',
				'type' => 'not_empty',
			),
			'choices'     => array(
				'one_col'  => esc_html__( 'Use as 1 column per row', 'stm_vehicles_listing' ),
				'two_cols' => esc_html__( 'Use as 2 columns per row', 'stm_vehicles_listing' ),
			),
		),
		'enable_checkbox_button'                => array(
			'label'       => esc_html__( 'Add submit button to this checkbox area', 'stm_vehicles_listing' ),
			'description' => esc_html__( 'AJAX filter will be triggered after clicking on button', 'stm_vehicles_listing' ),
			'value'       => '',
			'type'        => 'checkbox',
			'dependency'  => array(
				'slug' => 'listing_rows_numbers_enable',
				'type' => 'not_empty',
			),
		),
		'listing_rows_numbers_default_expanded' => array(
			'label'      => esc_html__( 'Use on listing archive as checkboxes', 'stm_vehicles_listing' ),
			'value'      => '',
			'type'       => 'radio',
			'dependency' => array(
				'slug' => 'listing_rows_numbers_enable',
				'type' => 'not_empty',
			),
			'choices'    => array(
				'open'  => esc_html__( 'Open box by default', 'stm_vehicles_listing' ),
				'close' => esc_html__( 'Close box by default', 'stm_vehicles_listing' ),
			),
		),
		'show_in_admin_column'                  => array(
			'label'       => esc_html__( 'Show in admin column table', 'stm_vehicles_listing' ),
			'description' => esc_html__( 'This column will be shown in admin', 'stm_vehicles_listing' ),
			'value'       => '',
			'type'        => 'checkbox',
			'preview'     => 'admin_table.png',
		),
	);

	if ( apply_filters( 'is_listing', array() ) ) {
		$options = array_merge(
			$options,
			array(
				'use_on_map_page' => array(
					'label'       => esc_html__( 'Use on map page', 'stm_vehicles_listing' ),
					'description' => esc_html__( 'Check if you want to see this category on map page', 'stm_vehicles_listing' ),
					'value'       => '',
					'preview'     => 'map_infowindow.png',
					'type'        => 'checkbox',
				),
			)
		);
	}

	if ( apply_filters( 'stm_is_aircrafts', false ) ) {
		$options = array_merge(
			$options,
			array(
				'use_on_single_header_search' => array(
					'label'   => esc_html__( 'Use on Single Listing Header Search', 'stm_vehicles_listing' ),
					'value'   => '',
					'preview' => 'header_search.jpg',
					'type'    => 'checkbox',
				),
			)
		);
	}

	// remove "Listing price field" if multilisting is deactivated OR current post type is the default one
	if ( ! stm_is_multilisting() || ( isset( $_GET['post_type'] ) && apply_filters( 'stm_listings_post_type', 'listings' ) === $_GET['post_type'] ) ) {
		unset( $options['listing_price_field'] );
	}

	// rename all "car"s to "listing"s if multilisting is active
	if ( stm_is_multilisting() || ( isset( $_GET['post_type'] ) && apply_filters( 'stm_listings_post_type', 'listings' ) !== $_GET['post_type'] ) ) {
		foreach ( $options as $slug => $arr ) {
			if ( ! empty( $arr['label'] ) && strpos( $arr['label'], 'car' ) !== false ) {
				$options[ $slug ]['label'] = esc_html( str_replace( 'car', 'listing', $arr['label'] ) );
			} elseif ( ! empty( $arr['description'] ) && strpos( $arr['description'], 'car' ) !== false ) {
				$options[ $slug ]['description'] = esc_html( str_replace( 'car', 'listing', $arr['description'] ) );
			}
		}
	}

	return apply_filters( 'stm_listings_page_options_filter', $options );
}

function stm_listings_categories_admin_enqueue() {
	if ( ! wp_script_is( 'stm-theme-multiselect' ) ) {
		wp_enqueue_script( 'stm-theme-multiselect' );
	}

	if ( ! wp_script_is( 'stm-listings-js' ) ) {
		wp_enqueue_script( 'stm-listings-js' );
	}
}

add_action( 'admin_enqueue_scripts', 'stm_listings_categories_admin_enqueue' );


function stm_add_listing_theme_menu_item() {
	add_submenu_page(
		'edit.php?post_type=listings',
		__( 'Listing Categories', 'stm_vehicles_listing' ),
		__( 'Listing Categories', 'stm_vehicles_listing' ),
		'manage_options',
		'listing_categories',
		'stm_listings_vehicle_listing_settings_page'
	);
}

add_action( 'admin_menu', 'stm_add_listing_theme_menu_item' );

function stm_listings_vehicle_listing_settings_page() {
	/*Get all stored options*/
	$options = stm_listings_get_my_options_list();
	/*Get options to show*/
	$options_list = stm_listings_page_options();
	?>
	<div class="stm_vehicles_listing_categories">
		<div class="image-preview">
			<div class="overlay"></div>
		</div>
		<div class="stm_start"><?php esc_html_e( 'Vehicle listing Settings', 'stm_vehicles_listing' ); ?></div>
		<?php if ( ! empty( $_GET['after_patch'] ) ) : ?>
			<div class="stm-admin-message" style="padding: 20px; margin-bottom: 20px; clear: both;">
				<p style="font-size: 20px;">NOTE!!!</p>
				Listing Categories list has been recreated, but you have to set Options manually such:<br />
				<ul style="margin: 0 0 20px 0; padding: 0; line-height: 20px;">
					<li style="margin: 0; padding: 0;">- Icon;</li>
					<li style="margin: 0; padding: 0;">- Use on Inventory or Single pages;</li>
					<li style="margin: 0; padding: 0;">- Numeric field;</li>
					<li style="margin: 0; padding: 0;">- Display options; etc.</li>
				</ul>

				You can follow this setup manual - <a href="https://support.stylemixthemes.com/manuals/motors/#inventory_categories" style="background: #fff; padding: 3px;">https://support.stylemixthemes.com/manuals/motors/#inventory_categories</a><br />

				So sorry for inconveniences!<br />
			</div>
		<?php endif; ?>
		<div class="stm_import_export">
			<div class="export_settings">

			</div>
		</div>

		<div class="stm_vehicles_listing_content">

			<table class="wp-list-table widefat listing_categories listing_categories_edit">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Singular', 'stm_vehicles_listing' ); ?></th>
						<th><?php esc_html_e( 'Plural', 'stm_vehicles_listing' ); ?></th>
						<th><?php esc_html_e( 'Slug', 'stm_vehicles_listing' ); ?></th>
						<th><?php esc_html_e( 'Numeric', 'stm_vehicles_listing' ); ?></th>
						<th><?php esc_html_e( 'Manage', 'stm_vehicles_listing' ); ?></th>
						<th><?php esc_html_e( 'Edit', 'stm_vehicles_listing' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php if ( ! empty( $options ) ) : ?>
						<?php foreach ( $options as $option_key => $option ) : ?>
							<tr class="stm_listings_settings_head" data-tr="<?php echo esc_attr( $option_key ); ?>">
								<td class="highlited">
									<?php echo esc_html( $option['single_name'] ); ?>
								</td>
								<td>
									<?php echo esc_html( $option['plural_name'] ); ?>
								</td>
								<td>
									<?php echo esc_html( $option['slug'] ); ?>
								</td>
								<td><?php $option['numeric'] ? esc_html_e( 'Yes', 'stm_vehicles_listing' ) : esc_html_e( 'No', 'stm_vehicles_listing' ); ?></td>
								<td class="manage"><i class="fas fa-list-ul" data-url="<?php echo esc_url( get_site_url() ) . '/wp-admin/edit-tags.php?taxonomy=' . esc_attr( $option['slug'] ) . '&post_type=listings'; ?>"></i></td>
								<td><i class="fas fa-pencil-alt"></i></td>
							</tr>
							<tr class="stm_listings_settings_tr" data-tr="<?php echo esc_attr( $option_key ); ?>">
								<td colspan="7">
									<form action="" method="post">
										<div class="stm_vehicles_listing_option_meta">
											<div class="stm_vehicles_listing_row_options">
												<div class="stm_listings_col_4">
													<div class="inner">
														<input name="stm_vehicle_listing_row_position" type="hidden" value="<?php echo esc_attr( $option_key ); ?>" />
														<?php foreach ( $options_list as $option_name => $option_settings ) : ?>

															<?php stm_vehicles_listings_show_field( $option_name, $option_settings, $option ); ?>

														<?php endforeach; ?>
													</div>
												</div>
											</div>
											<div class="stm_vehicles_listing_row_actions">
												<a href="#save" class="button button-primary button-large"><?php esc_html_e( 'Save', 'stm_vehicles_listing' ); ?></a>
												<div class="stm_response_message"></div>

												<a href="#cancel" class="button button-secondary button-large"><?php esc_html_e( 'Cancel', 'stm_vehicles_listing' ); ?></a>
												<a href="#delete" class="button button-secondary button-large">
													<i class="fas fa-trash"></i>
													<?php esc_html_e( 'Delete', 'stm_vehicles_listing' ); ?>
												</a>
											</div>
										</div>
									</form>
								</td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>

				</tbody>
			</table>

			<div class="stm_vehicles_add_new">
				<div class="stm_vehicles_listings_add_new_row">
					<i class="fas fa-plus"></i>
					<?php esc_html_e( 'Add new', 'stm_vehicles_listing' ); ?>
				</div>
				<table class="wp-list-table widefat listing_categories listing_categories_add_new">
					<tbody>
						<tr class="stm_listings_settings_tr">
							<td colspan="7">
								<form action="" method="post">
									<div class="stm_vehicles_listing_option_meta">
										<div class="stm_vehicles_listing_row_options">
											<div class="stm_listings_col_4">
												<div class="inner">
													<?php foreach ( $options_list as $option_name => $option_settings ) : ?>

														<?php stm_vehicles_listings_show_field( $option_name, $option_settings, array() ); ?>

													<?php endforeach; ?>
												</div>
											</div>
										</div>
										<div class="stm_vehicles_listing_row_actions">
											<a href="#add_new" class="button button-primary button-large"><?php esc_html_e( 'Save', 'stm_vehicles_listing' ); ?></a>
											<div class="stm_response_message"></div>

											<a href="#delete" class="button button-secondary button-large">
												<i class="fas fa-trash"></i>
												<?php esc_html_e( 'Delete', 'stm_vehicles_listing' ); ?>
											</a>
										</div>
									</div>
								</form>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>
	<?php
	if ( function_exists( 'stm_vehicles_listing_get_icons_html' ) ) {
		stm_vehicles_listing_get_icons_html();
	}
}

function stm_vehicles_listings_show_field( $name, $settings, $values ) {
	$type = 'stm_vehicle_listings_field_text';
	if ( ! empty( $settings['type'] ) ) {
		$type = 'stm_vehicle_listings_field_' . $settings['type'];
	}

	call_user_func( $type, $name, $settings, $values );
}

/**
 * @used-by stm_vehicles_listings_show_field
 * @uses stm_vehicle_listings_field_text to get the html of the text field
 * @param $name
 * @param $settings
 * @param $values
 *
 * @return void
 */
function stm_vehicle_listings_field_text( $name, $settings, $values ) {
	$value      = ( ! empty( $values[ $name ] ) ) ? $values[ $name ] : '';
	$atts       = ( ! empty( $settings['attributes'] ) ) ? $settings['attributes'] : array();
	$input_atts = '';
	if ( ! empty( $atts ) ) {
		foreach ( $atts as $key => $att ) {
			$input_atts .= $key . '="' . esc_attr( $att ) . '" ';
		}
	}
	?>
	<div class="stm_form_wrapper stm_form_wrapper_<?php echo esc_attr( $settings['type'] ); ?> stm_form_wrapper_<?php echo esc_attr( $name ); ?> <?php stm_vehicles_listing_has_preview( $settings ); ?>" <?php stm_vehicles_listing_show_dependency( $settings ); ?>>
		<label>
			<span><?php echo esc_html( $settings['label'] ); ?></span>
			<input <?php echo wp_kses_post( apply_filters( 'stm_vl_atts_filter', $input_atts ) ); ?> type="<?php echo esc_attr( $settings['type'] ); ?>" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $value ); ?>" />
		</label>
		<?php stm_vehicles_listings_preview( $settings ); ?>
	</div>
	<?php
}

/**
 * @used-by stm_vehicles_listings_show_field
 * @uses stm_vehicle_listings_field_select to get the html of the select field
 * @param $name
 * @param $settings
 * @param $values
 *
 * @return void
 */
function stm_vehicle_listings_field_select( $name, $settings, $values ) {
	?>
	<div class="stm_form_wrapper stm_form_wrapper_<?php echo esc_attr( $settings['type'] ); ?>" <?php stm_vehicles_listing_show_dependency( $settings ); ?>>
		<span><?php echo esc_html( $settings['label'] ); ?></span>
		<select aria-label="<?php esc_attr( $settings['label'] ); ?>" name="<?php echo esc_attr( $name ); ?>">
			<?php
			foreach ( $settings['choices'] as $value => $label ) :
				$selected = ( ! empty( $values[ $name ] ) && $values[ $name ] === $value ) ? 'selected' : '';
				?>
				<option value="<?php echo esc_attr( $value ); ?>" <?php echo esc_attr( $selected ); ?>><?php echo esc_html( $label ); ?></option>
			<?php endforeach; ?>
		</select>
	</div>
	<?php
}

/**
 * @used-by stm_vehicles_listings_show_field
 * @uses stm_vehicle_listings_field_radio to get the html of the radio field
 * @param $name
 * @param $settings
 * @param $values
 *
 * @return void
 */
function stm_vehicle_listings_field_radio( $name, $settings, $values ) {
	if ( empty( $values[ $name ] ) ) {
		$first_key       = array_keys( $settings['choices'] );
		$values[ $name ] = $first_key[0];
	}
	?>
	<div class="stm_form_wrapper stm_form_wrapper_<?php echo esc_attr( $settings['type'] ); ?>" <?php stm_vehicles_listing_show_dependency( $settings ); ?>>
		<?php foreach ( $settings['choices'] as $value => $label ) : ?>
			<label>
				<input type="radio" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $value ); ?>" <?php checked( $values[ $name ], $value ); ?> />
				<span><?php echo esc_html( $label ); ?></span>
			</label>
		<?php endforeach; ?>
	</div>
	<?php
}

/**
 * @used-by stm_vehicles_listings_show_field
 * @uses stm_vehicle_listings_field_checkbox to get the html of the checkbox field
 * @param $name
 * @param $settings
 * @param $values
 *
 * @return void
 */
function stm_vehicle_listings_field_checkbox( $name, $settings, $values ) {
	$selected = ( ! empty( $values[ $name ] ) ) ? 'checked' : '';
	?>
	<div class="stm_form_wrapper stm_form_wrapper_<?php echo esc_attr( $settings['type'] ); ?>  stm_form_wrapper_<?php echo esc_attr( $name ); ?> <?php stm_vehicles_listing_has_preview( $settings ); ?>" <?php stm_vehicles_listing_show_dependency( $settings ); ?>>
		<label>
			<input type="<?php echo esc_attr( $settings['type'] ); ?>" name="<?php echo esc_attr( $name ); ?>" <?php echo esc_attr( $selected ); ?> />
			<span><?php echo esc_html( $settings['label'] ); ?></span>
		</label>
		<?php stm_vehicles_listings_preview( $settings ); ?>
	</div>
	<?php
}

/**
 * @used-by stm_vehicles_listings_show_field
 * @uses stm_vehicle_listings_field_divider to get the html of the divider field
 * @param $name
 *
 * @return void
 */
function stm_vehicle_listings_field_divider( $name ) {
	echo '</div></div><div class="stm_listings_col_4 stm_' . esc_attr( $name ) . '"><div class="inner">';
}

/**
 * @used-by stm_vehicles_listings_show_field
 * @uses stm_vehicle_listings_field_icon to get the html of the icon field
 * @param $name
 * @param $settings
 * @param $values
 *
 * @return void
 */
function stm_vehicle_listings_field_icon( $name, $settings, $values ) {
	$icon  = ( ! empty( $values[ $name ] ) ) ? $values[ $name ] : '';
	$value = ( ! empty( $values[ $name ] ) ) ? $values[ $name ] : '';
	?>
	<div class="stm_form_wrapper stm_form_wrapper_<?php echo esc_attr( $settings['type'] ); ?>">
		<span><?php echo esc_html( $settings['label'] ); ?></span>
		<input type="hidden" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $value ); ?>" />
		<div class="stm_vehicles_listing_icon">
			<div class="icon">
				<img src="<?php echo esc_url( STM_LISTINGS_URL . '/assets/images/plus.svg' ); ?>" alt="" class="stm-default-icon_<?php echo esc_attr( $value ); ?>" />
				<i class="<?php echo esc_attr( $icon ); ?>"></i>
			</div>
			<?php if ( empty( $value ) ) : ?>
				<div class="stm_change_icon"><?php esc_html_e( 'Add icon', 'stm_vehicles_listing' ); ?></div>
			<?php else : ?>
				<div class="stm_change_icon"><?php esc_html_e( 'Change icon', 'stm_vehicles_listing' ); ?></div>
			<?php endif; ?>
			<div class="stm_delete_icon"><?php esc_html_e( 'Delete icon', 'stm_vehicles_listing' ); ?></div>
		</div>
	</div>
	<?php
}

function stm_vehicles_listing_show_dependency( $settings ) {
	$dependency = '';

	if ( ! empty( $settings['dependency'] ) ) {
		$setting    = $settings['dependency'];
		$dependency = 'data-depended="true" ';

		foreach ( $setting as $key => $value ) {

			if ( is_array( $value ) ) {
				foreach ( $value as $slug => $_value ) {
					$dependency .= 'data-' . $slug . '="' . esc_attr( $_value ) . '"';
				}
			} else {
				$dependency .= 'data-' . $key . '="' . esc_attr( $value ) . '"';
			}
		}
	}

	echo wp_kses_post( apply_filters( 'stm_vl_depends_filter', $dependency ) );
}

function stm_vehicles_listing_has_preview( $settings ) {
	$class = '';
	if ( ! empty( $settings['preview'] ) ) {
		$class = 'stm-has-preview-image';
	}
	echo esc_attr( $class );
}

function stm_vehicles_listings_preview( $settings ) {
	if ( ! empty( $settings['preview'] ) ) :
		?>
		<a href="#" data-image="<?php echo esc_url( STM_LISTINGS_URL . '/assets/images/tmp/' . $settings['preview'] ); ?>">
			<?php esc_html_e( 'Preview', 'stm_vehicles_listing' ); ?>
		</a>
		<?php
	endif;
}

// showing features in admin columns
$post_types = array( apply_filters( 'stm_listings_post_type', 'listings' ) );
if ( stm_is_multilisting() ) {
	$slugs = STMMultiListing::stm_get_listing_type_slugs();
	if ( ! empty( $slugs ) ) {
		$post_types = array_merge( $post_types, $slugs );
	}
}

foreach ( $post_types as $post_type ) {
	add_action( 'manage_' . $post_type . '_posts_custom_column', 'stm_listings_display_posts_stickiness', 10, 2 );
	add_filter( 'manage_' . $post_type . '_posts_columns', 'stm_listings_add_sticky_column' );
}

function stm_listings_display_posts_stickiness( $column, $post_id ) {
	if ( 'stm_image' === $column ) {

		if ( has_post_thumbnail( $post_id ) ) {
			echo '<div class="attachment">';
			echo '<div class="attachment-preview">';
			echo '<div class="thumbnail">';
			echo '<div class="centered">';
			echo '<a href="' . esc_url( get_edit_post_link( $post_id ) ) . '">' . get_the_post_thumbnail( $post_id, 'medium' ) . '</a>';
			echo '</div>';
			echo '</div>';
			echo '</div>';
			echo '</div>';
		}
	}

	$user_columns = stm_get_numeric_admin_fields();
	if ( ! empty( $user_columns[ $column ] ) ) {
		$col = str_replace( 'stm-column-', '', $column );
		if ( 'price' === $col ) {
			$col = 'stm_genuine_price';
		}
		$value = get_post_meta( $post_id, $col, true );
		if ( empty( $value ) ) {
			$value = '—';
		} else {
			if ( function_exists( 'stm_listing_price_view' ) ) {
				if ( 'stm_genuine_price' === $col ) {
					$value = apply_filters( 'stm_filter_price_view', '', $value );
				}
			}
		}
		echo esc_attr( apply_filters( 'stm_vl_price_view_filter', $value ) );
	}
}

/* Add custom column to post list */
function stm_listings_add_sticky_column( $columns ) {

	$column_date = $columns['date'];
	unset( $columns['author'], $columns['comments'], $columns['date'] );
	$new_columns              = array();
	$new_columns['cb']        = '<input type="checkbox" />';
	$new_columns['stm_image'] = __( 'Image', 'stm_vehicles_listing' );

	$user_columns = stm_get_numeric_admin_fields();
	if ( ! empty( $user_columns ) ) {
		foreach ( $user_columns as $key => $value ) {
			$columns[ $key ] = $value;
		}
	}

	$columns['date'] = $column_date;
	return array_merge( $new_columns, $columns );
}

// need to make this multilisting ready
function stm_get_numeric_admin_fields() {
	$cols = array();

	$options = get_option( 'stm_vehicle_listing_options' );

	if ( get_post_type( get_the_ID() ) !== apply_filters( 'stm_listings_post_type', 'listings' ) ) {
		$post_type = get_post_type( get_the_ID() );
		$options   = get_option( "stm_{$post_type}_options" );
	}

	if ( ! empty( $options ) ) {
		foreach ( $options as $option ) {
			if ( ! empty( $option['numeric'] ) && ! empty( $option['show_in_admin_column'] ) ) {
				$cols[ 'stm-column-' . $option['slug'] ] = esc_html( $option['single_name'] );
			}
		}
	}
	return $cols;
}
