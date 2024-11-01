<?php

namespace WebinaneCommerce\Classes;

use WebinaneCommerce\Helpers\MapOldFields;

class Metaboxes {

	use MapOldFields;

	public static $config;
	public static $instance;

	static function register() {
		
	}

	/**
	 * Instance.
	 */
	public static function instance() {

		if(is_null(self::$instance)) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	static function init() {

		add_action( 'add_meta_boxes', array(__CLASS__, 'metaboxes' ) );
		add_action('admin_enqueue_scripts', array(__CLASS__, 'enqueue' ), 1000, 2 );

		add_action( 'wp_ajax__wpcommerce_admin_save_metabox', array(__CLASS__, 'save'), 50, 2 );
		add_action( 'wp_ajax__wpcommerce_admin_metabox_data', array(__CLASS__, 'metabox_data'), 50, 2 );
	}

	/**
	 * [metaboxes description]
	 * @return [type] [description]
	 */
	static function metaboxes() {
		if( self::$config && is_array(self::$config)) {
			
			foreach( self::$config as $config ) {
				$collect = collect($config);

				if( $collect->get('id') ) {

					add_meta_box( 
						$collect->get('id'), 
						$collect->get('heading'), 
						array(__CLASS__, 'output'), 
						$collect->get('post_types', 'post'),
						$collect->get('context', 'normal'),
						$collect->get('priority', 'low'),
						array('config' => $config)
					);
				}
			}
		}
	}

	public static function output($post, $metabox) {

		//$mb = collect($metabox);
		$mb = webinane_array($metabox);
		$config = array_get($metabox, 'args.config');
		$id = array_get($metabox, 'args.config.id');
		$config = apply_filters( "webinane_commerce_register_metabox_{$id}", $config );
		include WNCM_PATH . 'templates/admin/metabox.php';
	}

	static function enqueue($hook) {
		global $post_type;
		$post_types = apply_filters( 'wpcommerce_metabox_fields_supported_post_types', array('cause', 'project') );

		if( $hook == 'post.php' || $hook == 'post-new.php' ) {
			if( in_array($post_type, $post_types) ) {
				wp_enqueue_script(array('wp-blocks', 'wp-i18n', 'wp-element'));
				wp_enqueue_style( array('element-ui', 'wpcm-bootstrap', 'fontawesome', 'wpcommerce_main', 'wpcommerce_style', 'wpcommerce_responsive') );
				wp_enqueue_script( array('wpcm-admin-metaboxes') );
			}
		}
	}

	static function save() {

		if( ! current_user_can( 'manage_options' )) {
			wp_send_json_error( array('message' => esc_html__('You are not authorized', 'webinane-commerce')) );
		}
		$_post = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

		$meta_key = esc_attr( webinane_set( $_POST, 'meta_id' ) );
		$post_id = absint( webinane_set( $_POST, 'post_id' ) );
		$meta_data = webinane_set( $_post, 'options' );
		$post_type = get_post_type( $post_id );
		
		if( ! $meta_key || ! $post_id ) {
			wp_send_json_error( array('message' => esc_html__('There is something went wrong', 'webinane-commerce')) );
		}

		update_post_meta( $post_id, $meta_key, $meta_data );

		do_action('wpcommerce_metabox_after_save', $post_id, $meta_key, $meta_data);

		if( $post_type ) {
			do_action('wpcommerce_metabox_after_save_'.$post_type, $post_id, $meta_key, $meta_data);
		}

		wp_send_json_success( array( 'message' => esc_html__('Updated Susscessfully', 'webinane-commerce'), 'post_id' => $post_id, 'post' => $_post) );
	}

	static function metabox_data() {
		$_post = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
		$metabox_key = esc_attr( webinane_set( $_post, 'metabox_id' ) );
		$post_id = esc_attr( webinane_set( $_post, 'post_id' ) );
		$options = get_post_meta($post_id, $metabox_key, true);
		if( ! $options ) {
			$options = array('key' => '');
		}
		$metabox = webinane_set( self::$config, $metabox_key );
		$metabox = apply_filters( "webinane_commerce_register_metabox_{$metabox_key}", $metabox );
		wp_send_json( array('fields' => $metabox , 'options' => $options ) );
	}
}
