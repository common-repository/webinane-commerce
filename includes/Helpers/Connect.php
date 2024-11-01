<?php

namespace WebinaneCommerce\Helpers;

use Plugin_Installer_Skin;
use Plugin_Upgrader;
use WP_Error;

class Connect
{

	const WEBINANE_ITEMS = 'webinane_extensions';
	const USER_ITEMS = 'webinane_user_items';

	static function init() {
		$prefix = 'wp_ajax_webinane_commerce_user_';
		add_action($prefix . 'connect', [__CLASS__, 'connect_user']);
		add_action($prefix . 'disconnect', [__CLASS__, 'disconnect_user']);
		add_action($prefix . 'connect_get_items', [__CLASS__, 'get_items']);
		add_action($prefix . 'connect_install_plugin', [__CLASS__, 'install_plugin']);
		add_action($prefix . 'connect_activate_plugin', [__CLASS__, 'activate_plugin']);
		API::init();

	}

	/**
	 * Validate the post request in wordpress.
	 * @return [type] [description]
	 */
	static function validate_request() {
		$nonce = sanitize_text_field( array_get($_POST, 'nonce') );

		if(! wp_verify_nonce( $nonce, WPCM_AJAX_ACTION )) {
			wp_send_json_error( ['message' => esc_html__('Request is not verified', 'webinane-commerce')], 403 );
		}
		if(! current_user_can( 'manage_options' )) {
			wp_send_json_error( ['message' => esc_html__('You are not authorized to that', 'webinane-commerce')], 403 );
		}
	}

	/**
	 * Connect user to webinane.
	 * @return [type] [description]
	 */
	static function connect_user() {

		self::validate_request();

		$form = array_get($_POST, 'form');

		$res = wp_remote_post( API::get_auth_url(), [
			'body'	=> API::request_body($form)
		] );

		$res = API::parse_response($res);

		if(is_wp_error( $res )) {
			wp_send_json_error( ['message' => $res->get_error_message()], 403 );
		}

		wp_send_json_success( $res );

	}

	/**
	 * Disconnect user to webinane.
	 * @return [type] [description]
	 */
	static function disconnect_user() {

		self::validate_request();

		/**
		 * @todo need to cache the data instead of sending request each time.
		 */

		$config = [
			'path' => '/auth/logout',
			'type' => 'post'
		];

		$res = Api::request($config);

		$status = wp_remote_retrieve_response_code( $res );

		if($status == 200 || $status == 401) {
			$body = json_decode(wp_remote_retrieve_body( $res ));
			//printr($body);
			$is_disconnected = Api::is_disconnected();

			if(!$is_disconnected){
				wp_send_json_error( ['message' => esc_html__('trash to disconnect is get wrong.', 'webinane-commerce')], 403 );
			}

			wp_send_json_success( ['message' => $body->message], 200 );

		} else {

			$body = wp_remote_retrieve_body( $res );
			if(json_decode($body)) {
				$body = json_decode($body);

				wp_send_json_error( ['message' => $body->message], 403 );
			} else {
				wp_send_json_error( ['message' => $body], 403 );
			}

		}

		wp_send_json_error( ['message' => esc_html__('Unable to disconnected from the server, try again later', 'webinane-commerce')], 403 );

	}

	/**
	 * Get list of purchased items.
	 *
	 * @return [type] [description]
	 */
	static function get_items() {

		self::validate_request();

		if ( ! Api::is_connected() ) {
			wp_send_json_error( ['message' => esc_html__( 'You are not connected', 'webinane-commerce' )] );
		}

		$items = get_transient( self::WEBINANE_ITEMS );
		$user_items = get_transient( self::USER_ITEMS );

		if ( ! $items ) {

			$form = array_get($_POST, 'form');

			/**
			 * @todo need to cache the data instead of sending request each time.
			 */

			$config = [
				'path' => '/connect/plugins',
				'type' => 'get'
			];

			$res = Api::request($config);

			$status = wp_remote_retrieve_response_code( $res );

			if ( $status == 200 ) {
				$body = json_decode(wp_remote_retrieve_body( $res ), true);

				$user_items = self::get_user_items();

				if ( is_wp_error( $user_items ) ) {
					wp_send_json_error( ['message' => $user_items->get_error_message()], 403 );
				}
				set_transient( self::WEBINANE_ITEMS, $body, 24 * HOUR_IN_SECONDS );
				set_transient( self::USER_ITEMS, $user_items, 24 * HOUR_IN_SECONDS );

				wp_send_json_success( ['items' => $body['data'], 'user_items' => $user_items] );

			} else {
				$body = wp_remote_retrieve_body( $res );
				if(json_decode($body)) {
					$body = json_decode($body);

					wp_send_json_error( ['message' => $body->message], 403 );
				} else {
					wp_send_json_error( ['message' => $body], 403 );
				}

			}

			wp_send_json_error( ['message' => esc_html__('Unable to connect the API, try again later', 'webinane-commerce')], 403 );
		} else {
			wp_send_json_success( ['items' => $items['data'], 'user_items' => $user_items] );
		}
	}

	/**
	 * Get list of purchased items.
	 *
	 * @return [type] [description]
	 */
	static function get_user_items() {

		$config = [
			'path' => '/connect/plugins/user-plugins',
			'type' => 'get'
		];

		$res = Api::request($config);

		$status = wp_remote_retrieve_response_code( $res );

		if($status == 200) {
			$body = json_decode(wp_remote_retrieve_body( $res ), true);

			return $body;

		} else {
			$body = wp_remote_retrieve_body( $res );
			if(json_decode($body)) {
				$body = json_decode($body);
				return new WP_Error('message', $body->message);
			} else {
				return new WP_Error('message', $body);
			}

		}

		return new WP_Error( 'message', esc_html__('Unable to connect the API, try again later', 'webinane-commerce') );
	}


	/**
	 * Install the purchased plugin.
	 *
	 * @return void
	 */
	public static function install_plugin() {

		self::validate_request();

		$slug = sanitize_text_field( array_get($_POST, 'item.wp_slug') );

		$config = [
			'path' => "/connect/plugins/{$slug}/get-download-url",
			'type' => 'get'
		];

		$res = Api::request($config);
		// print_r($res);exit;
		$status = wp_remote_retrieve_response_code( $res );

		if($status == 200) {
			$body = json_decode(wp_remote_retrieve_body( $res ), true);
			self::process_install($slug, $body['download_url']);
			wp_send_json_success( ['message' => esc_html__('Plugin is installed and activated successfully', 'webinane-commerce')] );

		} else {
			$body = wp_remote_retrieve_body( $res );
			if(json_decode($body)) {
				$body = json_decode($body);
				wp_send_json_error( ['message' => $body->message] );
			} else {
				wp_send_json_error( ['message' => $body] );
			}

		}


		/**
		 * @todo Install the plugin.
		 */
		wp_send_json_success( ['message' => esc_html__('Plugin is installed and activated successfully', 'webinane-commerce')] );
	}

	/**
	 * Install the plugin using WordPress core technique.
	 * This is better using WordPress core functions and classes.
	 *
	 * @return string Return the results of the plugin installation.
	 */
	private static function process_install($plugin, $download_url) {
		ob_start();
		include_once( ABSPATH . 'wp-admin/includes/plugin-install.php' ); //for plugins_api..

		$api = $json = [
            "version" => '1.2',
            "name" => get_bloginfo('name'),
            "download_url" => $download_url,
            "requires" => '5.0',
            "tested" => '5.2',
            "requires_php" => '7.1',
            "last_updated" => date('Y-m-d'),
            "contirbutors" => [],
            'banners'    => [],
            'screenshots'    => [],
            'versions'	=> []

        ];
        $api = (object) $api;

		//includes necessary for Plugin_Upgrader and Plugin_Installer_Skin
		include_once( ABSPATH . 'wp-admin/includes/file.php' );
		include_once( ABSPATH . 'wp-admin/includes/misc.php' );
		include_once( ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' );

		$upgrader = new Plugin_Upgrader( new Plugin_Installer_Skin( compact('title', 'url', 'nonce', 'plugin', 'api') ) );
		$upgrader->install($download_url);

		return ob_get_clean();
	}

	public static function activate_plugin() {
		self::validate_request();

		$slug = sanitize_text_field( array_get($_POST, 'item.wp_slug') );

		activate_plugin( $slug.'/'.$slug.'.php' );

		wp_send_json_success( ['message' => esc_html__( 'Plugin is activated successfully', 'webinane-commerce' )] );
	}
}
