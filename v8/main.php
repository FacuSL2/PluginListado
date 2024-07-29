<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Here define a constants*/
define( 'STM_VEHICLE_PATH', __DIR__ );
define( 'STM_VEHICLE_NEW_VERSION', 'v8' );

/** self module loader */
require_once __DIR__ . '/settings/Loader.php';

/** @var  $loader
 * Load classes by  Load spl_autoload_register
 */
$loader = new \STM_Listing\Settings\Loader\Loader();
$loader->init();

/** composer autoload */
require_once __DIR__ . '/vendor/autoload.php';
