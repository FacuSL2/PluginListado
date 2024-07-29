<?php

namespace STM_Listing\Settings\Loader;

use STM_Listing\Modules\Api\ApiPosts;
use STM_Listing\Modules\Helper\FilterHelper;


class Loader {

	public function init() {
		/** register classes autoload */
		spl_autoload_register(
			array( $this, 'autoload' ),
			true
		);

		/** register api */
		$this->register_api();

		$this->add_filters();
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_data' ), 150 );
	}
	protected function add_filters() {
		add_filter( 'stm_listings_v8_filter', array( new FilterHelper(), 'stm_listings_v8_filter' ), 10, 2 );
	}

	public function enqueue_data() {
		wp_register_style( 'v8-stm-vehicle-style', STM_LISTINGS_URL . '/v8/assets/css/style.css', array(), STM_VEHICLE_NEW_VERSION, false, 'all' );
		if ( is_page( array( 'inventory' ) ) || is_page( array( 'cars' ) ) || is_page( array( 'motorcycles' ) ) ) {
			wp_enqueue_style( 'v8-stm-vehicle-style' );
		}
	}

	private function register_api() {
		$post = new ApiPosts();
		$post->init();
	}

	/**
	 * Classname rules - camel case, first part of class name must be
	 * folder name ( module - as User/Post etc)
	 * second part logic type of file - controller/model
	 * classname and filename must be equal
	 *
	 * @param $className
	 */
	private function autoload( $className ) {
		if ( ! defined( 'STM_VEHICLE_PATH' ) ) {
			return;
		}

		$className = str_replace( '\\', DIRECTORY_SEPARATOR, $className );
		$className = basename( $className );

		$parts = preg_split( '/(?=[A-Z])/', $className );
		if ( count( $parts ) < 3 ) {
			return;
		}

		$classPath = STM_VEHICLE_PATH . '/modules/' . $parts[1] . '/' . $className . '.php';
		if ( isset( $parts[1] ) && 'Core' !== $parts[1] && 'Model' === substr( $className, - strlen( 'Model' ) ) ) {
			$classPath = STM_VEHICLE_PATH . '/modules/' . $parts[1] . '/model/' . $className . '.php';
		}
		if ( isset( $parts[2] ) && 'Helper' === $parts[2] ) {
			$classPath = STM_VEHICLE_PATH . '/modules/' . $parts[2] . '/' . $className . '.php';
		}

		if ( file_exists( $classPath ) ) {
			require_once $classPath;
		}
	}
}
