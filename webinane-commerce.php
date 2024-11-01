<?php
/**
 * Plugin Name:     Webinane Commerce
 * Plugin URI:      http://webinane.com
 * Description:     eCommerce WordPress plugin
 * Author:          Webinane
 * Author URI:      https://webinane.com
 * Text Domain:     webinane-commerce
 * Domain Path:     /languages
 * Version:         2.2.3
 *
 * @package         Webinane_Commerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}



defined( 'WNCM_FILE' ) || define( 'WNCM_FILE', __FILE__ );
defined( 'WNCM_PATH' ) || define( 'WNCM_PATH', plugin_dir_path( WNCM_FILE ) );
defined( 'WNCM_URL' ) || define( 'WNCM_URL', plugin_dir_url( WNCM_FILE ) );

/**
 * Load plugin textdomain.
 *
 * @since 1.0.0
 */
function webincom_load_textdomain() {
	load_plugin_textdomain( 'webinane-commerce', false, basename( dirname( __FILE__ ) ) . '/languages' );
}

add_action( 'plugins_loaded', 'webincom_load_textdomain' );


// Include the main WPCommerce class.
if ( ! class_exists( 'WebinaneCommerce\Loader' ) ) {
	require_once WNCM_PATH . 'vendor/autoload.php';
	include_once WNCM_PATH . 'includes/Loader.php';
}
