<?php
/**
 * Image control class.  This control allows users to set an image.  It passes the attachment
 * ID the setting, so you'll need a custom control class if you want to store anything else,
 * such as the URL or other data.
 *
 * @package    ButterBean
 * @author     Justin Tadlock <justin@justintadlock.com>
 * @copyright  Copyright (c) 2015-2016, Justin Tadlock
 * @link       https://github.com/justintadlock/butterbean
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

/**
 * Image control class.
 *
 * @since  1.0.0
 * @access public
 */
class ButterBean_Control_Grouped_Checkboxes extends ButterBean_Control {

	/**
	 * The type of control.
	 *
	 * @since  1.0.0
	 * @access public
	 * @var    string
	 */
	public $type = 'grouped_checkboxes';

	/**
	 * Array of text labels to use for the media upload frame.
	 *
	 * @since  1.0.0
	 * @access public
	 * @var    string
	 */
	public $l10n = array();

	/**
	 * Creates a new control object.
	 *
	 * @since  1.0.0
	 * @access public
	 * @param  object $manager
	 * @param  string $name
	 * @param  array  $args
	 * @return void
	 */
	public function __construct( $manager, $name, $args = array() ) {
		parent::__construct( $manager, $name, $args );

		$this->l10n = wp_parse_args(
			$this->l10n,
			array(
				'add_feature' => esc_html__( 'Add new feature', 'stm_vehicles_listing' ),
				'add'         => esc_html__( 'Add', 'stm_vehicles_listing' ),
			)
		);
	}

	/**
	 * Adds custom data to the json array.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	public function to_json() {
		parent::to_json();

		$this->json['l10n'] = $this->l10n;

		$value = array_map( 'trim', explode( ',', $this->get_value() ) );

		$values = array();

		$feature_settings = apply_filters( 'stm_me_get_nuxy_mod', array(), 'fs_user_features' );
		$mlt_post_type    = get_post_type( get_the_ID() );

		if ( 'listings' !== $mlt_post_type ) {
			$mlt_theme_options = get_option( 'stm_motors_listing_types', array() );

			$feature_settings = ( ! empty( $mlt_theme_options[ $mlt_post_type . '_fs_user_features' ] ) ) ? $mlt_theme_options[ $mlt_post_type . '_fs_user_features' ] : array();
		}

		if ( ! empty( $feature_settings ) ) {

			foreach ( $feature_settings as $k => $group ) {
				$values[ $k ]['group_title'] = $group['tab_title_single'];

				$features = array();

				foreach ( $group['tab_title_selected_labels'] as $field ) {
					$features[] = array(
						'val'     => $field['label'],
						'checked' => in_array( $field['label'], $value, true ) ? true : false,
					);
				}

				$values[ $k ]['group_features'] = $features;
			}
		}

		$this->json['link']   = get_site_url() . '/wp-admin/edit-tags.php?taxonomy=stm_additional_features&post_type=' . get_post_type( get_the_ID() );
		$this->json['values'] = $values;

	}
}
