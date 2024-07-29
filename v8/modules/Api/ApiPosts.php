<?php

namespace STM_Listing\Modules\Api;

use STM_Listing\Modules\Core\CoreApi;
use STM_Listing\Modules\Helper\CleanHelper;
use STM_Listing\Modules\Post\PostModel;
use STM_Listing\Modules\Terms\TermsController;

/**
 * PostController Class
 */
class ApiPosts extends CoreApi {

	public function __construct() {
		parent::__construct();
		$this->route = 'posts';
	}

	public function init() {
		$this->register_stm_routes();
	}

	public function get_rest_data() {
		return array(
			'path'  => $this->route_namespace,
			'route' => $this->route,
			'key'   => $this->get_auth_key(),
		);
	}

	public function register_stm_routes() {
		add_action(
			'rest_api_init',
			function () {
				register_rest_route(
					$this->route_namespace,
					$this->route,
					array(
						'methods'             => \WP_REST_Server::READABLE,
						'callback'            => array( $this, 'get_listing' ),
						'permission_callback' => array( $this, 'check_permission' ),
						'args'                => $this->get_endpoint_args_for_item_schema( \WP_REST_Server::CREATABLE ),
					)
				);
			}
		);
	}

	/**
	 * @param \WP_REST_Request $request
	 * $request is same as $source var in older version, was used in 'stm_listings_query' function, file includes/query.php
	 * $request contain
	 *  - dynamic options data from option
	 *      - 'stm_vehicle_listing_options' ( get_option( 'stm_vehicle_listing_options' ) )
	 *  - as_html ( default false )
	 *  - paged
	 * - status ( default = publish )
	 * - posts_per_page - ( default = 9 )
	 * - sort_order - ( default = date_high| possible values - PostModel ORDER_SORT_DATA)
	 * - popular - used for sorting ( default = false )
	 *
	 * @return \WP_REST_Response
	 */
	public function get_listing( \WP_REST_Request $request ) {
		$filter_data  = CleanHelper::clean_and_parse_request_data( $request->get_params() );
		$vehicles_ids = TermsController::get_filtered_vehicle_ids( $filter_data );

		$post_model = new PostModel();
		$results    = $post_model->get_listing_by_wp_query( $filter_data, $vehicles_ids );

		return new \WP_REST_Response( $results, 200 );
	}

	public function validate_args() {
		return true;
	}

	public function get_item_schema() {
		return array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'type'       => 'object',
			'properties' => array(
				'status'  => array(
					'type'              => 'string',
					'description'       => esc_html__( 'Listing items status', 'stm_vehicles_listing' ),
					'default'           => 'publish',
					'sanitize_callback' => 'sanitize_text_field',
				),
				'as_html' => array(
					'type'              => 'bool',
					'description'       => esc_html__( 'Generate html listing blocks', 'stm_vehicles_listing' ),
					'default'           => false,
					'sanitize_callback' => 'rest_sanitize_boolean',
				),
				'popular' => array(
					'type'              => 'bool',
					'description'       => esc_html__( 'Sort by popular', 'stm_vehicles_listing' ),
					'default'           => false,
					'sanitize_callback' => 'rest_sanitize_boolean',
				),
				'paged'   => array(
					'description'       => esc_html__( 'Current page of the listing.', 'stm_vehicles_listing' ),
					'type'              => 'integer',
					'default'           => 1,
					'sanitize_callback' => 'absint',
				),
			),
		);
	}
}
