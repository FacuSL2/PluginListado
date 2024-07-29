<?php

namespace STM_Listing\Modules\Core;

use STM_Listing\Modules\Helper\AuthorizationHelper;
use STM_Listing\Modules\Helper\RequestHelper;

/**
 * PostController Class
 */
abstract class CoreApi extends \WP_REST_Controller {

	private $header_key_name = 'access-key';
	/**
	* @var string
	* used to check access to API
	* send in headers
	 */
	protected $route_list           = array(
		'posts',
	);
	protected $version              = 'v8';
	protected $namespace            = 'stm_vehicles_listing';
	protected $need_auth_route_list = array();
	protected $route_namespace;

	/** @var route used for child */
	protected $route;

	public function __construct() {
		$this->route_namespace = $this->namespace . '/' . $this->version;
	}

	/** check user permission */
	public function check_permission( \WP_REST_Request $request ) {
		if ( RequestHelper::get_header_by_name( $request, $this->header_key_name ) !== AuthorizationHelper::encode_key() ) {
			return false;
		}
		return true;
	}

	protected function get_auth_key() {
		return AuthorizationHelper::encode_key();
	}

	abstract public function register_stm_routes();
}
