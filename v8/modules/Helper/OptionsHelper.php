<?php

namespace STM_Listing\Modules\Helper;

class OptionsHelper {

	/**
	 * string option_name in wp_options
	 */
	const LISTING_OPTION_NAME = 'stm_vehicle_listing_options';

	/**
	 * for now
	 * array key is name
	 * array value is type
	 * @var string[]
	 */
	const CATEGORY_OPTIONS = array(
		'single_name'                           => 'text',
		'plural_name'                           => 'text',
		'slug'                                  => 'text',
		'font'                                  => 'icon',
		'numeric'                               => 'checkbox',
		'number_field_affix'                    => 'text',
		'slider_in_tabs'                        => 'checkbox',
		'slider'                                => 'checkbox',
		'slider_step'                           => 'text',
		'use_delimiter'                         => 'checkbox',
		'listing_price_field'                   => 'checkbox',
		'use_on_car_listing_page'               => 'checkbox',
		'use_on_car_archive_listing_page'       => 'checkbox',
		'use_on_single_car_page'                => 'checkbox',
		'use_on_car_filter'                     => 'checkbox',
		'use_on_tabs'                           => 'checkbox',
		'use_on_car_modern_filter'              => 'checkbox',
		'use_on_car_modern_filter_view_images'  => 'checkbox',
		'use_on_car_filter_links'               => 'checkbox',
		'filter_links_default_expanded'         => 'radio',
		'use_in_footer_search'                  => 'checkbox',
		'use_on_directory_filter_title'         => 'checkbox',
		'use_on_single_listing_page'            => 'checkbox',
		'listing_taxonomy_parent'               => 'select',
		'listing_rows_numbers_enable'           => 'checkbox',
		'listing_rows_numbers'                  => 'radio',
		'enable_checkbox_button'                => 'checkbox',
		'listing_rows_numbers_default_expanded' => 'radio',
	);

	public static function get_category_option_names() {
		return array_keys( self::CATEGORY_OPTIONS );
	}

	public static function get_category_option_by_slug( $slug = '' ) {
		$result = array();

		if ( empty( $slug ) ) {
			return $result;
		}

		$all_categories = self::get_all_listing_categories();
		foreach ( $all_categories as $value ) {
			if ( $value['slug'] === $slug ) {
				$result = $value;
				break;
			}
		}

		return $result;
	}

	public static function get_category_option_slugs() {
		return array_column( self::get_all_listing_categories(), 'slug' );
	}

	public static function get_listing_options_from_request() {
		$all_categories = self::get_category_option_slugs();

		$friendly_url_params = array();
		/** get friendly url params */
		if ( class_exists( \MotorsVehiclesListing\FriendlyUrl::class ) && ! empty( \MotorsVehiclesListing\FriendlyUrl::$for_filter ) ) {
			$friendly_url_params = \MotorsVehiclesListing\FriendlyUrl::$for_filter;
		}

		$result = array_filter(
			$_REQUEST,
			function ( $key ) use ( $all_categories ) {
				return in_array( $key, $all_categories, true );
			},
			ARRAY_FILTER_USE_KEY
		);

		if ( ! empty( $_REQUEST['taxonomy'] ) && ! empty( $_REQUEST['term'] ) && in_array( $_REQUEST['taxonomy'], $all_categories, true ) ) {
			$result = array_merge( array( $_REQUEST['taxonomy'] => $_REQUEST['term'] ), $result );
		}

		$result = array_merge( $result, $friendly_url_params );
		return $result;
	}

	/**
	 * Get list of dynamic options (listing_categories)
	 * by it option values
	 * @return false|mixed|void
	 */
	public static function get_all_listing_categories_by_option( $options = array(), $use_slug_as_option_key = false ) {
		$all_categories = self::get_all_listing_categories();
		if ( false === $all_categories ) {
			return array();
		}

		if ( empty( $options ) ) {
			return $all_categories;
		}
		/** @var  $options remove option names which is not exist */
		$options = array_filter(
			$options,
			function ( $value, $key ) {
				return in_array( $key, self::get_category_option_names(), true );
			},
			ARRAY_FILTER_USE_BOTH
		);

		$result = array();
		foreach ( $all_categories as $category_key => $category_option ) {
			$passed = true;
			foreach ( $options as $option_name => $option_value ) {
				if ( ! array_key_exists( $option_name, $category_option ) ||
					( array_key_exists( $option_name, $category_option ) && filter_var( $category_option[ $option_name ], FILTER_VALIDATE_BOOLEAN ) !== filter_var( $option_value, FILTER_VALIDATE_BOOLEAN ) ) ) {
					$passed = false;
					break;
				}
			}

			if ( $passed && true === $use_slug_as_option_key && array_key_exists( 'slug', $category_option ) ) {
				$result[ $category_option['slug'] ] = $category_option;
			} elseif ( $passed ) {
				$result[] = $category_option;
			}
		}
		// get_all_listing_categories_by_option todo
		apply_filters( 'stm_listings_attributes', $result, $options, $use_slug_as_option_key );
		return $result;
	}

	/**
	 * Get full list of dynamic options (listing_categories)
	 * added by Admin
	 * on  Vehicle Listing Settings
	 * ( ...../post_type=listings&page=listing_categories) page
	 *
	 * @return false|mixed|void
	 */
	public static function get_all_listing_categories() {
		return get_option( self::LISTING_OPTION_NAME );
	}

}
