<?php
namespace WebinaneCommerce\Admin;

use WebinaneCommerce\Admin\Api;
use WebinaneCommerce\Helpers\Connect;
use WebinaneCommerce\Helpers\MapOldFields;
use WebinaneCommerce\Helpers\SettingsResource;

/**
 * Settings .
 *
 * @todo Need to reduce code by spliting it into many files
 * @todo Need to add proper form submission validation. May be add validation in settings array.
 */
class Settings {

	use MapOldFields;

	static $menu;
	static $dashboard;
	static $connect_menu;
	public static $instance;

	/**
	 * Instance.
	 */
	public static function instance() {

		if(is_null(self::$instance)) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Init.
	 */
	static function init() {

		add_action( 'wpcommerce_admin_page', array(__CLASS__, 'register') );

		add_action( 'admin_menu', array(__CLASS__, 'menu_pages_init') );

		// add_action( 'admin_print_styles-toplevel_page_wp-commerce-settings', array(__CLASS__, 'admin_custom_css') );

		add_action('wp_ajax__wpcommerce_admin_settings', array(__CLASS__, 'ajax'));

		add_filter( 'webinane_commerce_settings_before_save', array(__CLASS__, 'filter_settings_save_data') );

		add_filter('display_post_states', function( $states, $post ) {
			$settings = wpcm_get_settings();
		    if ( 'page' == get_post_type( $post->ID ) ) {
		    	if($post->ID == $settings->get('checkout_page')) {
		            $states[] = __('WPCM Checkout Page', 'webinane-commerce');
		    	} else if($post->ID == $settings->get('success_page')) {
		            $states[] = __('WPCM Order Success Page', 'webinane-commerce');
		    	} else if($post->ID == $settings->get('my_account_page')) {
		            $states[] = __('WPCM My Account Page', 'webinane-commerce');
		    	}
		    }
		    return $states;
		}, 10, 2);

		add_action('wp_ajax_wpcm_field_upload_avatar', [__CLASS__, 'upload_image']);

		Connect::init();
		Dashboard::instance()->init();

		/*$settings = require_once WNCM_PATH . 'config/settings.php';
		$settings = apply_filters( 'wpcommerce_settings', $settings );
		$myins = (new SettingsResource($settings, wpcm_get_settings()));
		$resolve = $myins->resolve();

		printr($myins->values);*/
	}

	/**
	 * Create admin menu pages for wpcm.
	 *
	 * @return [type] [description]
	 */
	static function menu_pages_init(){
		do_action('wpcommerce_admin_page');
	}

	/**
	 * enqueue scripts and styles on a specific settings page.
	 *
	 * @return [type] [description]
	 */
	static function admin_custom_css() {
		$version = (defined('WP_DEBUG') && WP_DEBUG ) ? time() : WPCM_VERSION;
		wp_enqueue_editor();
		wp_enqueue_media();

		wp_enqueue_script(array('wp-blocks', 'wp-i18n', 'wp-element', 'underscore', 'vuejs', 'element-ui', 'boostratp', 'wpcm-components'));

		wp_enqueue_script( 'moment', WNCM_URL . 'assets/js/common/moment.min.js', array('jquery'), $version, true );

		wp_enqueue_script( 'admin_settings', WNCM_URL . 'assets/js/admin/settings.js', ['element-ui'], $version, true );
		wp_enqueue_style( array('element-ui', 'wpcm-bootstrap', 'bootstrap-datepicker', 'fontawesome', 'wpcommerce_main', 'wpcommerce_style', 'wpcommerce_responsive') );
	}

	/**
	 * Filter the data before saving.
	 *
	 * @param  [type] $data [description]
	 * @return [type]       [description]
	 */
	static function filter_settings_save_data($data) {
		if( is_array($data) ) {
			foreach( $data as $key => $dat) {
				if( $dat == 'false' ) {
					$data[$key] = false;
				}
				if( $dat == 'true' ) {
					$data[$key] = true;
				}

			}
		}

		return $data;
	}

	/**
	 * Handle ajax request specific for WP Commerce settings.
	 *
	 * @return [type] [description]
	 */
	static function ajax() {
		$_post = $_POST;//filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

		$subaction = esc_attr( webinane_set( $_post, 'subaction') );
		$nonce = esc_attr( webinane_set( $_post, 'nonce') );

		if(! wp_verify_nonce( $nonce, 'wp_rest' )) {
			wp_send_json_error( ['message' => esc_html__('Sucurity verification failed', 'webinane-commerce')], 403 );
		}

		if( ! current_user_can( 'manage_options' ) ) {
			wp_send_json( array('message' => esc_html__('You are not authorized', 'webinane-commerce' )), 403 );
		}

		if( $subaction == 'fields_data') {
			$settings = require_once WNCM_PATH . 'config/settings.php';
			$settings = apply_filters( 'wpcommerce_settings', $settings );
			
			$settings = (new SettingsResource($settings, wpcm_get_settings()))->resolve();
			// $settings = self::instance()->make($settings);

			$options = wpcm_get_settings(false, true);
			if( ! $options ) {
				$options = new \stdClass;
			}
			wp_send_json( array('data' => $settings, 'options' => $options ) );

		} else if ( $subaction == 'save_data') {
			/**
			 * Save webinane commerce settings.
			 * @var [type]
			 */
			$data = webinane_set( $_post, 'data' );
			$data = json_decode(stripslashes($data), true);
			//$data['offline_payment_instruction'] = wp_kses(array_get($_POST, 'data.offline_payment_instruction'), wp_kses_allowed_html( 'post' ));

			$data = apply_filters( 'webinane_commerce_settings_before_save', $data );

			if ( $data ) {
				update_option('_wpcommerce_settings', $data);

				$options = get_option('_wpcommerce_settings');
				wp_send_json( array('options' => $options, 'message' => esc_html__('Settings are updated successfully', 'webinane-commerce' ) ) );
			} else {
				wp_send_json( array('message' => esc_html__('Something went wrong', 'webinane-commerce' )), 403 );
			}
		} else if( $subaction == 'export_settings' ) {
			/**
			 * Export all the settings into a json file in json format.
			 */
			self::export_settings();
			exit;
		}
		else if( $subaction == 'import_settings' ) {
			$_post = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
			/**
			 * Export all the settings into a json file in json format.
			 */
			$import_input = webinane_set( $_post, 'file');


			switch (array_get($_post, 'input_type')) {
				case 3:
					$file = array_get($_FILES, 'file');
					if(! array_get($file, 'tmp_name') || array_get($file, 'type') !== 'application/json') {
						wp_send_json_error( esc_html__('Invalid File provided', 'webinane-commerce' ) );
					}
					$contents= file_get_contents(array_get($file, 'tmp_name'));

					$text = json_decode( $contents, true );
					break;
				case 6:
					if(! esc_url($import_input)) {
						wp_send_json_error( esc_html__('Invalid URL Provided', 'webinane-commerce' ) );
					}
					
					$contents= file_get_contents(esc_url($import_input));
					$text = json_decode( $contents, true );
					break;
				case 9:
					$text = $import_input;
					# code...
					break;
			}


			if( $text && ! is_array($text) ) {
				$text = stripslashes_deep($text);
				$text = json_decode( $text, true );
			}

			if( ! $text ) {
				wp_send_json_error( esc_html__('Please provide a proper json data', 'webinane-commerce' ) );
			}
			if( is_array($text) ) {
				update_option(WPCM_SETTINGS_KEY, $text);
				wp_send_json_success( esc_html__('Settings are imported successfully', 'webinane-commerce' ) );
			}
			exit;
		}
		exit;
	}

	/**
	 * Load the default values for the options which has no db value.
	 *
	 * @param [type] $options [description]
	 */
	static function set_default_setting($options) {

		$settings = include WNCM_PATH . 'config/settings.php';

		$settings = apply_filters( 'wpcommerce_settings', $settings );
		/*$list = wp_list_pluck( $settings, 'fields' );
		foreach( $list as $li ) {
			foreach( $li as $field ) {
				if( isset( $field['id']) && ! isset( $options[ $field['id'] ] ) ) {
					$options[ $field['id'] ] = isset( $field['default'] ) ? $field['default'] : '';
				}
			}
		}
		if(isset($options[0])) {
			unset($options[0]);
		}*/

		return $options;
	}
	/**
	 * Export settings.
	 *
	 * @return [type] [description]
	 */
	static function export_settings() {
		$strFile = wpcm_get_settings()->toJson();
		header("HTTP/1.1 200 OK");
		header("Pragma: public");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");

		// The optional second 'replace' parameter indicates whether the header
		// should replace a previous similar header, or add a second header of
		// the same type. By default it will replace, but if you pass in FALSE
		// as the second argument you can force multiple headers of the same type.
		header("Cache-Control: private", false);

		header("Content-type: " . 'application/text');

		// $strFileName is, of course, the filename of the file being downloaded.
		// This won't have to be the same name as the actual file.
		header("Content-Disposition: attachment; filename=\"webinane-commerce-settings.json\"");

		header("Content-Transfer-Encoding: binary");
		header("Content-Length: " . mb_strlen($strFile));

		// $strFile is a binary representation of the file that is being downloaded.
		echo $strFile;
	}

	/**
	 * Register admin menu for settings.
	 *
	 * @return [type] [description]
	 */
	static function register() {
		self::$menu = add_menu_page(
	        apply_filters( 'webinane_commerce/settings/page_heading', esc_html__( 'WP Commerce Settings', 'webinane-commerce' ) ),
	        apply_filters( 'webinane_commerce/settings/menu_label', esc_html__( 'WP Commerce', 'webinane-commerce' ) ),
	        'manage_options',
	        'wp-commerce-settings',
	        array(__CLASS__, 'output'),
	        apply_filters( 'webinane_commerce/settings/menu_icon', WNCM_URL . 'assets/svg/cart.svg' ),
	        40
		);
		Dashboard::$dashboard = add_submenu_page(
	        'wp-commerce-settings',
	        esc_html__( 'Dashboard', 'webinane-commerce' ),
	        esc_html__('Dashboard', 'webinane-commerce' ),
	        'manage_options',
	        'wp-commerce-dashboard',
	        array(Dashboard::instance(), 'output'),
	        40
		);
		self::$connect_menu = add_submenu_page(
			'wp-commerce-settings',
	        esc_html__( 'Webinane Extensions', 'webinane-commerce' ),
	        esc_html__('Extensions', 'webinane-commerce' ),
	        'manage_options',
	        'wp-commerce-extensions',
	        array(__CLASS__, 'connect_menu'),
	        400
		);

		add_action( 'admin_print_styles-'.self::$menu, array(__CLASS__, 'admin_custom_css') );
		add_action( 'admin_print_styles-'.self::$connect_menu, array(__CLASS__, 'admin_custom_css') );
		add_action( 'admin_print_styles-'.Dashboard::$dashboard, array(Dashboard::instance(), 'enqueue') );

		$orders_label = apply_filters('wpcm_orders_admin_menu_label', esc_html__('Orders', 'webinane-commerce'));
		$link_our_new_CPT = 'edit.php?post_type=orders';
		add_submenu_page('wp-commerce-settings', $orders_label, $orders_label, 'manage_options', $link_our_new_CPT);

	}

	/**
	 * Render the settings.
	 *
	 * @return [type] [description]
	 */
	static function output() {
		$settings = require_once WNCM_PATH . 'config/settings.php';

		self::store_api_info();

		include WNCM_PATH . 'templates/admin/settings.php';
	}

	/**
	 * Connect menu.
	 * @return [type] [description]
	 */
	static function connect_menu() {
		include WNCM_PATH . 'templates/admin/connect.php';
	}

	/**
	 * Render the settings.
	 *
	 * @return [type] [description]
	 */
	static function store_api_info() {
		/*$api = new Api;
		$info = sanitize_text_field(array_get($_GET, 'access_token'));

		if($info){
			$api->set_token($info);
		}

		$info = sanitize_text_field(array_get($_GET, 'expires_in'));

		if($info){
			$api->set_token_exp($info);
		}

		$info = sanitize_text_field(array_get($_GET, 'refresh_token'));

		if($info){
			$api->set_refresh_token($info);
		}

		$api->update();*/
	}

	/**
	 * A WPCM options-page display callback override which adds tab navigation among
	 * WPCM options pages which share this same display callback.
	 *
	 * @param WPCM_Options_Hookup $wpcmm_options The WPCM_Options_Hookup object.
	 */
	static function display($wpcmm_options) {
		_deprecated_function( __FUNCTION__, '2.0', "No replacement available" );
		$tabs = self::options_page_tabs( $wpcmm_options );

		include WNCM_PATH . 'templates/admin/settings.php';
	}

	/**
	 * Gets navigation tabs array for WPCM options pages which share the given
	 * display_cb param.
	 *
	 * @param WPCM_Options_Hookup $wpcmm_options The WPCM_Options_Hookup object.
	 *
	 * @return array Array of tab information.
	 */
	static function options_page_tabs( $wpcmm_options ) {
		_deprecated_function( __FUNCTION__, '0.1.0', "No replacement available" );
		$tab_group = $wpcmm_options->wpcmm->prop( 'tab_group' );
		$tabs      = array();
		foreach ( WPCM_Boxes::get_all() as $wpcmm_id => $wpcmm ) {
			if ( $tab_group === $wpcmm->prop( 'tab_group' ) ) {
				$tabs[ $wpcmm->options_page_keys()[0] ] = $wpcmm->prop( 'tab_title' )
					? $wpcmm->prop( 'tab_title' )
					: $wpcmm->prop( 'title' );
			}
		}
		return $tabs;
	}

	/**
	 * Upload image through ajax call came from Image component.
	 * @return [type] [description]
	 */
	static function upload_image() {

		if( ! is_user_logged_in() ) {
			wp_send_json_error(['message' => esc_html__('You are not authorized', 'webinane-commerce')]);
		}

		if ( ! function_exists( 'wp_handle_upload' ) ) {
		    require_once( ABSPATH . 'wp-admin/includes/file.php' );
		}

		$uploadedfile = $_FILES['file'];

		$upload_overrides = array(
		    'test_form' => false
		);

		$movefile = wp_handle_upload( $uploadedfile, $upload_overrides );

		if ( $movefile && ! isset( $movefile['error'] ) ) {
		    wp_send_json_success(['message' => esc_html__('File is valid, and was successfully uploaded.', 'webinane-commerce'), 'url' => $movefile['url']]);
		} else {
		    /*
		     * Error generated by _wp_handle_upload()
		     * @see _wp_handle_upload() in wp-admin/includes/file.php
		     */
		    wp_send_json_error(['message' => $movefile['error']]);
		}
	}


}
