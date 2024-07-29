<?php
namespace STM_Listing\Modules\Terms;

use STM_Listing\Modules\Core\CoreController;
use STM_Listing\Modules\Helper\OptionsHelper;
use STM_Listing\Modules\Term\TermsModel;

/**
 * TaxonomyController Class
 */

class TermsController extends CoreController {

	const FEATURES_META_KEY = array(
		'arg_name' => 'stm_features', // name of send param from request
		'key'      => 'stm_additional_features',
	);

	/** get term params from request */
	public static function get_term_params( $data ) {
		if ( ! is_array( $data ) ) {
			return false;
		}
		$categories_options_slug   = OptionsHelper::get_category_option_slugs();
		$categories_options_slug[] = self::FEATURES_META_KEY['arg_name'];

		$result = array_filter(
			$data,
			function( $filter_value, $filter_name ) use ( $categories_options_slug ) {
				return ( in_array( $filter_name, $categories_options_slug, true ) && 'price' !== $filter_name );
			},
			ARRAY_FILTER_USE_BOTH
		);
		return $result;
	}

	/** get term params from request */
	public static function get_filtered_vehicle_ids( $filter_data ) {
		$filter_data = self::get_term_params( $filter_data );

		if ( empty( $filter_data ) ) {
			return array();
		}

		$term_model = new TermsModel();
		$result     = $term_model->get_object_ids_by_taxonomy_and_slug( $filter_data );

		return $result;
	}

	public function init() {}

}
