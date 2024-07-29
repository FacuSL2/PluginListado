<?php

namespace STM_Listing\Modules\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WP_User;

/**
 * Class Controlller
 *
 * @package STM_Listing\Core\Controlller
 */
abstract class CoreController {

	/**
	 * @var $user instanceof WP_User
	 */
	protected $user;

	/**
	 * Controlller constructor.
	 * add user info
	 * no args for now
	 */
	public function __construct() {
		$this->user = $this->get_current_user();
	}

	abstract public function init();

	/**
	 * @return false|instanceof|WP_User
	 */
	protected function get_current_user() {
		if ( isset( $this->user ) && $this->user instanceof WP_User ) {
			return $this->user;
		}

		if ( ! function_exists( 'wp_get_current_user' ) ) {
			include ABSPATH . 'wp-includes/pluggable.php';
		}

		$user = wp_get_current_user();
		if ( ! $user instanceof WP_User || ! is_object( $user ) ) {
			return false;
		}

		return $user;
	}

	/**
	 * @return bool
	 */
	protected function canUserManageOptions() {
		return current_user_can( 'manage_options' );
	}

	/**
	 * @return bool
	 */
	protected function doAutosave() {
		return defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE;
	}
}
