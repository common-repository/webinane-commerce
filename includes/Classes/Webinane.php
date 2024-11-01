<?php

namespace WebinaneCommerce\Classes;

use WebinaneCommerce\Admin\Install;
use WebinaneCommerce\Admin\Settings;
use WebinaneCommerce\Classes\Checkout;
use WebinaneCommerce\Classes\Customers;
use WebinaneCommerce\Classes\Enqueue;
use WebinaneCommerce\Classes\Extensions;
use WebinaneCommerce\Classes\Session;
use WebinaneCommerce\Classes\Shortcodes;
use WebinaneCommerce\Gateways\GatewayOffline;
use WebinaneCommerce\Gateways\GatewayPaypal;

final class Webinane
{
	static $_instance;
	static $session;

	/**
	 * Hook to load with plugins_loaded.
	 *
	 * @return [type] [description]
	 */
	static function plugins_loaded() {
		do_action('webinane_commerce/before_loaded');
	}
	/**
	 * Init.
	 * @return [type] [description]
	 */
	static function init() {
		if( ! self::version_check() ) {
			return;
		}

		self::loaded();

		if( get_transient( '_webinane_commerce_create_default_pages' ) ) {
			if( is_admin() ) {
				add_action('init', array(Install::class, 'create_pages'));
			}
		}

		add_action('wp_enqueue_scripts', [Enqueue::class, 'init'], 10);
		add_action('admin_enqueue_scripts', [Enqueue::class, 'admin_enqueue']);

		add_action('wp_ajax__wpcommerce_admin_add_new_customer', [Customers::class, 'add_new_customer']);
		add_action('wp_ajax__wpcommerce_admin_get_users', [Customers::class, 'get_wp_users']);
		add_action('wp_ajax__wpcommerce_admin_customer_remove', [Customers::class, 'remove_single_customer']);
		add_action('wp_ajax__wpcommerce_admin_customer_update_data', [Customers::class, 'ajax_full_customer_info']);

		add_action('wp', [Shortcodes::class, 'webhooks_response'], 20 );

		add_filter( 'webinane_commerce/order_total', [__CLASS__, 'convert_currency'], 100, 2 );

		add_action( 'upgrader_process_complete', [__CLASS__, 'plugin_upgrade'],10, 2);

		// Creating table whenever a new blog is created
		add_action( 'wpmu_new_blog', [__CLASS__, 'network_create'], 10, 6);

		// Deleting the table whenever a blog is deleted
		add_filter( 'wpmu_drop_tables', [__CLASS__, 'delete_network_tables']);

	}

	/**
	 * Main intance to handle dynamic functions
	 *
	 * @return [type] [description]
	 */
	static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Hookedup with WordPress init hook.
	 * 
	 * @return [type] [description]
	 */
	static function wpInit() {
		GatewayOffline::init();
		GatewayPaypal::init();
		Checkout::init();
		Settings::init();
		Shortcodes::init();

		if(!is_admin()) {
			Session::run();
		}
	}

	/**
	 * WP Commerce requires at least PHP 7.0 to work.
	 *
	 * @return boolean Returns true if required version is active.
	 */
	static function version_check() {
		if( version_compare(PHP_VERSION, '7.0.0') >= 0 ) {
			return true;
		}

		add_action( 'admin_notices', array(__CLASS__, 'version_error_notice') );

		return false;
	}

	static function loaded() {
		do_action('webinane_commerce_loaded');

		Extensions::run();
		add_action('wp_ajax__wpcommerce_get_dropdown_items', array(	__CLASS__, 'ajax_dropdown'));
	}

	/**
	 * Magic function to handle static or dynamic methods.
	 *
	 * @param  string $method     The method
	 * @param  array $parameters  Array of parameters.
	 * @return callable           Returns nothing.
	 */
	public function __call($method, $parameters = null) {
	    if($method == 'exists') {
	        return call_user_func_array(array($this, 'exists'), array(array($this->file)));
	    }
	}

	public static function __callStatic($method, $parameters) {
	    if($method == 'exists') {
	        return call_user_func(__CLASS__.'::exists', $parameters);
	    }
	}

	/**
	 * [ajax_dropdown description]
	 * @return [type] [description]
	 */
	public static function ajax_dropdown() {

		$_post = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
		$cb = esc_attr( webinane_set( $_post, 'option_cb' ) );

		if( $cb && function_exists($cb) ) {
			$params = webinane_set($_post, 'args');
			//$value = call_user_func($cb);
			$value = call_user_func_array($cb, array('args'=>$params));
			wp_send_json(array('data' => $value ) );
		}

		wp_send_json( array('data' => array() ) );
	}

	static function version_error_notice() {
		$class = 'notice notice-error';
		$message = esc_html__( 'Minimum PHP version 7.1 is required for Webinane Commerce to work', 'webinane-commerce' );

		printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
	}

	/**
	 * Network create
	 * 
	 * @param  [type] $blog_id [description]
	 * @param  [type] $user_id [description]
	 * @param  [type] $domain  [description]
	 * @param  [type] $path    [description]
	 * @param  [type] $site_id [description]
	 * @param  [type] $meta    [description]
	 * @return [type]          [description]
	 */
	static function network_create( $blog_id, $user_id, $domain, $path, $site_id, $meta ) {
		$current_plugin_path_name = plugin_basename( WNCM_FILE );
	    if ( is_plugin_active_for_network( $current_plugin_path_name ) ) {
	        switch_to_blog( $blog_id );
	        \WebinaneCommerce\Admin\Install::init(true);
	        restore_current_blog();
	    }
	}

	/**
	 * Delete network tables.
	 *
	 * @param  [type] $tables [description]
	 * @return [type]         [description]
	 */
	static function delete_network_tables( $tables ) {
	    global $wpdb;
	    $tables[] = $wpdb->prefix . 'wpcommerce_sessions';
	    $tables[] = $wpdb->prefix . 'wpcommerce_order_items';
	    $tables[] = $wpdb->prefix . 'wpcommerce_payment_tokens';
	    $tables[] = $wpdb->prefix . 'wpcommerce_payment_tokenmeta';
	    $tables[] = $wpdb->prefix . 'wpcommerce_log';
	    $tables[] = $wpdb->prefix . 'wpcommerce_customers';
	    $tables[] = $wpdb->prefix . 'wpcommerce_customer_meta';
	    return $tables;
	}

	/**
	 * Filter to convert currency.
	 *
	 * @param  [type] $total [description]
	 * @param  [type] $data  [description]
	 * @return [type]        [description]
	 */
	static function convert_currency($total, $data) {
		$currency = wpcm_get_settings()->get('base_currency', 'USD');
		if($new = array_get($data, 'currency')) {
			if($new !== $currency) {
				$convert = webinane_currency_convert($total, $currency, $new);
				return $convert;
			}
		}

		return $total;
	}


	/**
	 * [plugin_upgrade description]
	 * @param  [type] $upgrader_object [description]
	 * @param  [type] $options         [description]
	 * @return [type]                  [description]
	 */
	static function plugin_upgrade( $upgrader_object, $options ) {
	    $current_plugin_path_name = plugin_basename( WNCM_FILE );

	    if ($options['action'] == 'update' && $options['type'] == 'plugin' ){
	       if( isset($options['plugins'])) {
		       foreach($options['plugins'] as $each_plugin){
		          if ( $each_plugin == $current_plugin_path_name){
		          	self::update_database();

		          }
		       }
	       } elseif( isset($options['plugin']) ) {
	       		if ( $options['plugin'] == $current_plugin_path_name ) {
	       			self::update_database();
	       		}
	       }
	    }
	}

	/**
	 * Update database.
	 * @return [type] [description]
	 */
	static function update_database() {
		global $wpdb;

		$result = $wpdb->get_results("SHOW COLUMNS FROM `{$wpdb->prefix}wpcommerce_order_items` LIKE 'base_price'");

		if( ! $result ) {
			$wpdb->query("ALTER TABLE {$wpdb->prefix}wpcommerce_order_items ADD base_price float DEFAULT '0'");
			$wpdb->query("ALTER TABLE {$wpdb->prefix}wpcommerce_order_items ADD currency varchar(3) DEFAULT 'USD'");
		}

		$settings = get_option('_wpcommerce_settings');
	    if(! isset($settings['gateways'])) {
			$settings['base_country'] = ['country' => $settings['base_country'], 'state' => array_get($settings, 'base_state')];
			$settings['paypal_express_gateway'] = array_get($settings, 'paypal_express_gateway_status');
			$settings['offline_gateway'] = array_get($settings, 'offline_gateway_status');
			$settings['stripe_gateway'] = array_get($settings, 'stripe_gateway_status');

			$settings['gateways'] = array();
			$settings['gateways']['paypal_express_gateway'] = array(
				'paypal_title'	=> array_get($settings, 'paypal_title'),
				'paypal_description'	=> array_get( $settings, 'paypal_description'),
				'paypal_email'	=> array_get( $settings, 'paypal_email'),
				'paypal_api_username'	=> array_get( $settings, 'paypal_api_username'),
				'paypal_api_signature'	=> array_get( $settings, 'paypal_api_signature'),
			);
			$settings['gateways']['offline_gateway'] = array(
				'offline_payment_description'	=> array_get( $settings, 'offline_payment_description'),
				'offline_payment_instruction'	=> array_get( $settings, 'offline_payment_instruction'),
				'offline_payment_title'	=> array_get( $settings, 'offline_payment_title'),
			);
			$settings['gateways']['stripe_gateway'] = array(
				'stripe_description'	=> array_get($settings, 'stripe_description'),
				'stripe_private_key'	=> array_get($settings, 'stripe_private_key'),
				'stripe_publishable_key'	=> array_get($settings, 'stripe_publishable_key'),
				'stripe_title'	=> array_get( $settings, 'stripe_title'),
				'stripe_webhook_signing_secret'	=> array_get($settings, 'stripe_webhook_signing_secret'),
			);
			
			update_option('_wpcommerce_settings', $settings);
		}
	}


}