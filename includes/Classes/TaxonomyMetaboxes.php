<?php

namespace WebinaneCommerce\Classes;

use WebinaneCommerce\Helpers\MapOldFields;

class TaxonomyMetaboxes {

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
		// add_action( 'add_meta_boxes', array(__CLASS__, 'metaboxes' ) );
		add_action('init', [__CLASS__, 'hookUp']);
		add_action('admin_enqueue_scripts', array(__CLASS__, 'enqueue' ), 1000, 2 );
		add_action('wp_ajax__wpcommerce_admin_taxonomy_metabox_data', [__CLASS__, 'metabox_data']);

	}

	static function is_edit_screen($taxonomies, $screen = null) {
		$screen = get_current_screen();

		return 
		 ( 'edit-tags' === $screen->base || 'term' === $screen->base )
		 	&& ( in_array($screen->taxonomy, $taxonomies, true) );
	}

	public static function hookUp() {
		// printr(self::$config);
		if( self::$config && is_array(self::$config)) {
			foreach( self::$config as $taxonomy => $config ) {
				// printr($config);
				
				add_action( "edited_{$taxonomy}", array(__CLASS__, 'save'), 50 );
				add_action( "created_{$taxonomy}", array(__CLASS__, 'save'), 50 );
				// add_action( 'wp_ajax__wpcommerce_admin_metabox_data', array(__CLASS__, 'metabox_data'), 50, 2 );
				add_action("{$taxonomy}_edit_form", [__CLASS__, 'editForm'], 10, 2);
				add_action("{$taxonomy}_add_form_fields", [__CLASS__, 'addForm'], 10, 2);
				// foreach($config as $id => $args) {
				// }
			}
		}
	}

	/**
	 * [metaboxes description]
	 * @return [type] [description]
	 */
	/*static function metaboxes() {
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
	}*/

	public static function addForm($taxonomy) {
		if(self::is_edit_screen([$taxonomy])) {
			self::enqueue();
		}
		self::output($taxonomy, new \stdClass);
	}
	public static function editForm($term, $taxonomy) {

		if(self::is_edit_screen([$taxonomy])) {
			self::enqueue();
		}
		self::output($taxonomy, $term);
	}
	
	public static function output($taxonomy = '', $term) {

		$config = array_get(self::$config, $taxonomy);

		foreach($config as $id => $args) {
			//$mb = collect($metabox);
			// $mb = webinane_array($metabox);
			// $config = array_get($metabox, 'args.config');
			$id = array_get($args, 'id');
			$args = apply_filters( "webinane_commerce_register_taxonomy_metabox_{$id}", $args );
			include WNCM_PATH . 'templates/admin/taxonomy-metabox.php';
		}
	}

	static function enqueue() {
		global $post_type;
		$post_types = apply_filters( 'wpcommerce_metabox_fields_supported_post_types', array('cause', 'project') );

		wp_enqueue_media();
		wp_enqueue_script(array('wp-blocks', 'wp-i18n', 'wp-element'));
		wp_enqueue_style( array('element-ui', 'wpcm-bootstrap', 'fontawesome', 'wpcommerce_main', 'wpcommerce_style', 'wpcommerce_responsive') );
		wp_enqueue_script( array('wpcm-admin-metaboxes') );
	
	
	}

	static function save($term_id) {

		if( ! current_user_can( 'manage_options' )) {
			// wp_send_json_error( array('message' => esc_html__('You are not authorized', 'webinane-commerce')) );
			return;
		}
		$_post = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
		// printr($_post);
		$term = get_term($term_id);
		$meta_key = esc_attr( webinane_set( $_POST, 'webinane_commer_meta_key' ) );
		// echo $meta_key;exit;
		// $post_id = absint( webinane_set( $_POST, 'post_id' ) );
		// $post_type = get_post_type( $post_id );
		
		if( ! $meta_key || ! $term_id ) {
			return;
			// wp_send_json_error( array('message' => esc_html__('There is something went wrong', 'webinane-commerce')) );
		}

		$metabox_args = array_get(self::$config, $term->taxonomy.'.'.$meta_key);
		$options = [];

		foreach($metabox_args['fields'] as $field) {
			if($value = array_get($_post, $field['id'])) {
				$options[ $field['id'] ] = $value;
			}
		}

		update_term_meta( $term_id, $meta_key, $options );

		do_action('wpcommerce_metabox_after_save', $term_id, $meta_key, $options);

		/*if( $post_type ) {
			do_action('wpcommerce_metabox_after_save_'.$post_type, $post_id, $meta_key, $meta_data);
		}*/

		// wp_send_json_success( array( 'message' => esc_html__('Updated Susscessfully', 'webinane-commerce'), 'post_id' => $post_id, 'post' => $_post) );
	}

	static function metabox_data() {
		$_post = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
		$metabox_key = esc_attr( webinane_set( $_post, 'metabox_id' ) );
		$taxonomy = esc_attr( webinane_set( $_post, 'type' ) );
		$post_id = esc_attr( webinane_set( $_post, 'post_id' ) );
		$options = get_term_meta($post_id, $metabox_key, true);
		if( ! $options ) {
			$options = array('key' => '');
		}
		$metabox = array_get( self::$config, $taxonomy . '.' .$metabox_key );
		$metabox = apply_filters( "webinane_commerce_register_taxonomy_metabox_{$metabox_key}", $metabox );
		wp_send_json( array('fields' => $metabox , 'options' => $options ) );
	}
}
