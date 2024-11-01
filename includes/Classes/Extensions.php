<?php

namespace WebinaneCommerce\Classes;

use WebinaneCommerce\Helpers\Api;
use WebinaneCommerce\Helpers\Connect;

class Extensions
{
	private static $extensions = [];
	private static $user_items = [];

	/**
	 * Register the pro extension.
	 * 
	 * @param  [type] $file [description]
	 * @return [type]       [description]
	 */
	public static function register($file) {
		$plugin = plugin_basename( $file );

		self::$extensions[$file] = $plugin;
	}

	/**
	 * Run loading the extesions after validation.
	 *
	 * @return [type] [description]
	 */
	public static function run() {
		if( ! Api::is_connected() ) {
			return;
		}
		self::$user_items = (array) self::get_user_items();
		self::load_extensions();
	}
	/**
	 * Finally load the extensions.
	 *
	 * @return [type] [description]
	 */
	private static function load_extensions() {

		$config = self::ext_config();

		if ( ! function_exists('is_plugin_active')) {
			require_once ABSPATH . '/wp-admin/includes/plugin.php';
		}

		foreach (self::$extensions as $file => $ext) {
			if($found = array_get($config, $ext)) {
				if(is_plugin_active( $ext )) {
					$ext_file = plugin_dir_path($file) . $found['load'];

					if ( 
						file_exists( $ext_file ) && 
						in_array( dirname( $ext ), self::$user_items) 
					) {
						require_once $ext_file;
						call_user_func_array($found['callback'], []);
					}
				}
			}
		}
	}

	/**
	 * Get Useritems. 
	 *
	 * @return [type] [description]
	 */
	private static function get_user_items() {
		$items = get_transient( Connect::USER_ITEMS );

		if ( ! $items ) {
			$api_items = Connect::get_user_items();
			if( ! is_wp_error( $api_items )) {
				$items = $api_items;
			}
		}

		return $items;
	}

	/**
	 * Get the extension configuration file.
	 * 
	 * @return [type] [description]
	 */
	private static function ext_config() {
		return include WNCM_PATH . 'config/extensions.php';
	}
}