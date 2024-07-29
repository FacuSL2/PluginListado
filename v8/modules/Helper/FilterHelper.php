<?php

namespace STM_Listing\Modules\Helper;

use STM_Listing\Modules\Term\TermsModel;

class FilterHelper {

	public $filters         = array();
	public $filter_terms    = array();
	public $request_options = array();

	public function __construct() {
		$this->filters = $this->get_use_on_car_filters();
	}

	public function get_option_childs_by_parent( $parent, $taxonomy ) {
		$parent_values = $this->request_options[ $parent ];
		if ( ! is_array( $parent_values ) ) {
			$parent_values = array( $parent_values );
		}

		$term_model = new TermsModel();
		$result     = $term_model->get_child_term_ids_by_parent( $taxonomy, $parent_values );

		return $result;
	}

	/**
	 * generate numeric,slider options
	 * @param $taxonomy
	 * @param $parent
	 *
	 * @return array
	 */
	public function generate_num_slider_filter_option( $taxonomy, $parent ) {
		$options = array();

		foreach ( $this->filter_terms as $term ) {
			if ( $term->taxonomy !== $taxonomy ) {
				continue;
			}
			$options[ intval( $term->slug ) ] = array(
				'label'    => $term->name,
				'selected' => apply_filters( 'stm_listings_input', null, $term->slug ) === $term->name,
				'disabled' => false,
				'count'    => $term->count,
				'parent'   => $parent,
				'value'    => intval( $term->slug ),
				'option'   => intval( $term->slug ),
			);
		}
		ksort( $options );
		return $options;
	}

	/**
	 * generate numeric options
	 * @param $taxonomy
	 * @param $parent
	 * @param $filter
	 *
	 * @return array
	 */
	public function generate_number_filter_option( $taxonomy, $parent, $filter ) {
		$options       = array();
		$request_value = false;

		if ( array_key_exists( $taxonomy, $this->request_options ) ) {
			$request_value = $this->request_options[ $taxonomy ];
		}

		$_prev  = null;
		$_affix = empty( $filter['affix'] ) ? '' : esc_html( $filter['affix'] );

		/** @var  $filter_terms get terms for that taxonomy */
		$filter_terms = array_filter(
			$this->filter_terms,
			function( $ft ) use ( $taxonomy ) {
				return ( $ft->taxonomy === $taxonomy );
			}
		);

		/** sort is neccesary */
		usort(
			$filter_terms,
			function( $f_term_1, $f_term_2 ) {
				return intval( $f_term_1->slug ) <=> intval( $f_term_2->slug );
			}
		);

		foreach ( $filter_terms as $term ) {
			if ( $term->taxonomy !== $taxonomy ) {
				continue;
			}

			if ( null === $_prev ) {
				$_value = '<' . intval( $term->slug );
				$_label = '< ' . $term->name . ' ' . $_affix;
			} else {
				$_value = $_prev . '-' . intval( $term->slug );
				$_label = $_prev . '-' . $term->name . ' ' . $_affix;
			}

			$options[ $_value ] = array(
				'label'    => $_label,
				'selected' => ( false !== $request_value && $request_value === $_value ),
				'disabled' => false,
				'count'    => $term->count,
				'parent'   => $parent,
				'value'    => intval( $term->slug ),
				'option'   => $_value,
			);

			$_prev = intval( $term->slug );
		}

		if ( $_prev ) {
			$_value             = '>' . $_prev;
			$options[ $_value ] = array(
				'label'    => '>' . $_prev . ' ' . $_affix,
				'selected' => apply_filters( 'stm_listings_input', null, $filter['slug'] ) === $_value,
				'disabled' => false,
				'parent'   => $parent,
				'value'    => intval( $term->slug ),
				'option'   => $_value,
			);
		}

		return $options;
	}

	/**
	 * generate not numeric options
	 * @param $taxonomy
	 * @param false $parent
	 *
	 * @return array
	 */
	public function generate_default_filter_option( $taxonomy, $parent = false ) {
		$options       = array();
		$request_value = false;

		/** set $request_value to check selected item  */
		if ( array_key_exists( $taxonomy, $this->request_options ) ) {
			$request_value = $this->request_options[ $taxonomy ];
		}

		$enabled_ids_by_parent = false;
		/** if have parent enable just connected childs */
		if ( $parent && array_key_exists( $parent, $this->request_options ) ) {
			$enabled_ids_by_parent = $this->get_option_childs_by_parent( $parent, $taxonomy );
		}

		foreach ( $this->filter_terms as $term ) {
			if ( $term->taxonomy !== $taxonomy ) {
				continue;
			}

			$disabled = ( 0 === $term->count );
			if ( false !== $enabled_ids_by_parent ) {
				$disabled = ! in_array( (string) $term->term_id, $enabled_ids_by_parent, true );
			}

			$selected = ( false !== $request_value && $request_value === $term->slug );
			if ( is_array( $request_value ) ) {
				$selected = in_array( $term->slug, $request_value, true );
			}

			$options[ $term->slug ] = array(
				'label'    => $term->name,
				'selected' => $selected,
				'disabled' => $disabled,
				'count'    => $term->count,
				'parent'   => $parent,
				'option'   => $term->slug,
			);
		}

		$sort_by = 'count_asc';
		if ( array_key_exists( 'terms_filters_sort_by', $this->filters[ $taxonomy ] ) ) {
			if ( $this->filters[ $taxonomy ]['terms_filters_sort_by'] ) {
				$sort_by = $this->filters[ $taxonomy ]['terms_filters_sort_by'];
			}
		}

		if ( in_array( $sort_by, array( 'name_asc', 'name_desc' ), true ) ) {
			$options = wp_list_sort( $options, 'label', ( 'name_asc' === $sort_by ? 'ASC' : 'DESC' ), true );
		} else {
			$options = wp_list_sort( $options, 'count', ( 'count_asc' === $sort_by ? 'ASC' : 'DESC' ), true );
		}

		return $options;
	}

	/**
	 * @param false $hide_empty_terms
	 *
	 * @return mixed|void
	 */
	public function get_all_filter_data_with_options( $hide_empty_terms = false ) {

		$this->request_options = OptionsHelper::get_listing_options_from_request();

		/** @var filter_terms - get terms based on vehicle listing categories */
		$filter_numeric      = array_map(
			function ( $item_filter ) {
				return $item_filter['numeric'];
			},
			$this->filters
		);
		$filter_numeric_keys = apply_filters( 'stm_filters_data_with_numeric', array_keys( $filter_numeric ), $this );
		$filter_keys         = array_keys( $this->filters );
		$filter_terms_keys   = array_diff( $filter_keys, $filter_numeric_keys );

		$this->filter_terms = TermsModel::get_stm_terms( $filter_terms_keys, $hide_empty_terms );

		if ( ! empty( $filter_numeric_keys ) ) {
			$this->filter_terms = array_merge(
				$this->filter_terms,
				TermsModel::get_stm_terms( $filter_numeric_keys, false )
			);
		}

		/** @var  $result - create empty just with keys for sorting */
		$result = array_fill_keys( $filter_keys, array() );

		/**
		 * parse options for each category
		 * @var  $filter_key - is taxonomy name
		 * @var  $filter - filter settings from listing categories
		 */
		foreach ( $this->filters as $filter_key => $filter ) {
			$options = array();
			$filter  = wp_parse_args(
				$filter,
				array(
					'slug'        => $filter_key,
					'single_name' => '',
					'numeric'     => false,
					'slider'      => false,
				)
			);

			/** @var  $parent get listing_taxonomy_parent slug to check on front for select dd */
			$parent = false;
			if ( array_key_exists( 'listing_taxonomy_parent', $filter ) ) {
				$parent = $filter['listing_taxonomy_parent'];
			}

			if ( $filter['numeric'] && ! empty( $filter['slider'] ) ) {

				$options = $this->generate_num_slider_filter_option( $filter_key, $parent );

			} elseif ( $filter['numeric'] && empty( $filter['slider'] ) ) {
				$options['']    = array(
					'label'    => sprintf(
						/* translators: %s max value */
						__( 'Max %s', 'stm_vehicles_listing' ),
						$filter['single_name']
					),
					'selected' => false,
					'disabled' => false,
					'default'  => true,
					'parent'   => $parent,
					'option'   => '',
				);
				$filter_options = $this->generate_number_filter_option( $filter_key, $parent, $filter );
				$options        = array_merge( $options, $filter_options );
			} else {
				$options['']    = array(
					'label'    => apply_filters( 'stm_listings_default_tax_name', $filter['single_name'] ),
					'selected' => false,
					'disabled' => false,
					'parent'   => $parent,
					'default'  => true,
					'option'   => '',
				);
				$filter_options = $this->generate_default_filter_option( $filter_key, $parent );
				$options        = array_merge( $options, $filter_options );
			}

			if ( count( $options ) > 0 ) {
				$result[ $filter_key ] = $options;
			} else {
				unset( $result[ $filter_key ] );
			}
		}

		$result = apply_filters( 'stm_listings_filter_options', $result );// update filter
		return $result;
	}

	/**
	 * @param false $hide_empty_terms
	 * @param false $sort_by_count_asc - Sort options data by post count
	 *
	 * @return mixed|void
	 */
	public function get_filter_data_with_options_by_taxonomy( $hide_empty_terms = false, $sort_by_count_asc = false ) {

		$this->request_options = OptionsHelper::get_listing_options_from_request();

		/** @var  filter_terms - get terms based on vehicle listing categories */
		$this->filter_terms = TermsModel::get_stm_terms( array_keys( $this->filters ), $hide_empty_terms );

		/** @var  $result - create empty just with keys for sorting */
		$result = array_fill_keys( array_keys( $this->filters ), array() );

		/**
		 * parse options for each category
		 * @var  $filter_key - is taxonomy name
		 * @var  $filter - filter settings from listing categories
		 */
		foreach ( $this->filters as $filter_key => $filter ) {
			$options = array();
			$filter  = wp_parse_args(
				$filter,
				array(
					'slug'        => $filter_key,
					'single_name' => '',
					'numeric'     => false,
					'slider'      => false,
				)
			);

			/** @var  $parent get listing_taxonomy_parent slug to check on front for select dd */
			$parent = false;
			if ( array_key_exists( 'listing_taxonomy_parent', $filter ) ) {
				$parent = $filter['listing_taxonomy_parent'];
			}

			if ( $filter['numeric'] && ! empty( $filter['slider'] ) ) {

				$options = $this->generate_num_slider_filter_option( $filter_key, $parent );

			} elseif ( $filter['numeric'] && empty( $filter['slider'] ) ) {
				$options['']    = array(
					'label'    => sprintf(
						/* translators: %s max value */
						__( 'Max %s', 'stm_vehicles_listing' ),
						$filter['single_name']
					),
					'selected' => false,
					'disabled' => false,
					'default'  => true,
					'parent'   => $parent,
					'option'   => '',
				);
				$filter_options = $this->generate_number_filter_option( $filter_key, $parent, $filter );
				$options        = array_merge( $options, $filter_options );
			} else {
				$options['']    = array(
					'label'    => apply_filters( 'stm_listings_default_tax_name', $filter['single_name'] ),
					'selected' => false,
					'disabled' => false,
					'parent'   => $parent,
					'default'  => true,
					'option'   => '',
				);
				$filter_options = $this->generate_default_filter_option( $filter_key, $parent, $filter['use_on_car_filter_links'] );
				$options        = array_merge( $options, $filter_options );
			}

			if ( count( $options ) > 0 ) {
				$result[ $filter_key ] = $options;
			} else {
				unset( $result[ $filter_key ] );
			}
		}

		$result = apply_filters( 'stm_listings_filter_options', $result );// update filter
		return $result;
	}

	public static function get_use_on_car_filters() {
		return \STM_Listing\Modules\Helper\OptionsHelper::get_all_listing_categories_by_option(
			array( 'use_on_car_filter' => true ),
			true
		);
	}

	public function get_use_on_car_numeric_filters() {
		return \STM_Listing\Modules\Helper\OptionsHelper::get_all_listing_categories_by_option(
			array(
				'use_on_car_filter' => true,
				'numeric'           => true,
			),
			true
		);
	}

	public function stm_listings_v8_filter( $source = null, $hide_empty = false ) {
		$result['filters'] = \STM_Listing\Modules\Helper\OptionsHelper::get_all_listing_categories_by_option( array( 'use_on_car_filter' => true ), true );
		$result['options'] = $this->get_all_filter_data_with_options( $hide_empty );

		return $result;
	}

}
