<?php

namespace WebinaneCommerce\Admin;

use WebinaneCommerce\Classes\Enqueue;
use WebinaneCommerce\Models\Customer;

class Dashboard {

	static $instance;
	static $dashboard;

	public function init() {
		add_action( 'wp_ajax_webinane_commerce_dashboard_chart', array( $this, 'charts' ) );
		add_action( 'wp_ajax_webinane_commerce_customers_data', array( $this, 'customers' ) );
		add_action( 'wp_ajax_webinane_commerce_update_customer', array( $this, 'update_customer' ) );
		add_action( 'wp_ajax_webinane_commerce_customers_remove', array( $this, 'remove_customer' ) );
		add_action( 'wp_ajax_webinane_commerc_dashboard_menus', array( $this, 'menus' ) );
		add_action( 'wp_ajax_webinane_commerce_dashboard_tables', array( $this, 'tables' ) );
		// Enqueue::init();
	}

	private function is_authorized() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'You are not authorized to do this action', 'webinane-commerce' ) ) );
		}
	}

	/**
	 * Ajax
	 *
	 * @return [type] [description]
	 */
	public function charts() {

		wpcm_validate_nonce();
		$this->is_authorized();

		$type = sanitize_text_field( array_get( $_GET, 'type' ) );

		$data = array();

		$start_date = sanitize_text_field( array_get($_GET, 'start_date') );
		$end_date = sanitize_text_field( array_get($_GET, 'end_date') );


		switch ( $type ) {
			case 'chart':
				// $data = json_decode( file_get_contents( WNCM_PATH . 'assets/data/charts-data.json' ), true );
				// $groupby = sanitize_text_field( array_get() )
				$data = apply_filters( 'webinane_commerce/dashboard/charts', array(), $start_date, $end_date );
				break;
			case 'switch-data':
				$data = json_decode( file_get_contents( WNCM_PATH . 'assets/data/switch-data.json' ), true );
				break;
			case 'notification':
				$data = json_decode( file_get_contents( WNCM_PATH . 'assets/data/notification.json' ), true );
				break;
			case 'stats':
				$data = apply_filters( 'webinane_commerce/dashboard/stats', array(), $start_date, $end_date );
				break;
			case 'table':
				$query = sanitize_text_field( array_get($_GET, 'query') );
				$id = sanitize_text_field( array_get($_GET, 'id') );
				$data = apply_filters( 'webinane_commerce/dashboard/tables', array(), $query, $id );
				break;


			default:
				// code...
				break;
		}

		wp_send_json_success( $data );
	}

	public function output() {

		echo '<div id="wpcm-admin-dashboard"><App base_url="' . WNCM_URL . '" /></div>';
	}

	public static function instance() {
		if ( self::$instance == null ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function enqueue() {
		$version = ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ? time() : WPCM_VERSION;

		wp_enqueue_style( array( 'fontawesome' ) );
		wp_enqueue_style( 'wp-commerce-dashboard', WNCM_URL . 'assets/css/dashboard.css' );
		wp_enqueue_style( 'google-fonts-barlow', 'https://fonts.googleapis.com/css?family=Barlow:400,500,600,700,800&display=swap' );
		wp_enqueue_style( 'perfectscrollbar', WNCM_URL . 'assets/libs/perfectscrollbar/perfect-scrollbar.css', '1.3.0' );

		wp_enqueue_script( array( 'wp-blocks', 'wp-i18n', 'wp-element', 'underscore', 'vuejs', 'element-ui', 'boostratp', 'wpcm-components' ) );

		wp_enqueue_script( 'moment', WNCM_URL . 'assets/js/common/moment.min.js', array( 'jquery' ), $version, true );
		wp_enqueue_script( 'highcharts', WNCM_URL . 'assets/libs/highcharts/highcharts.js', array( 'jquery' ), '7.1.3', true );
		wp_enqueue_script( 'highcharts-exporting', WNCM_URL . 'assets/libs/highcharts/exporting.js', array( 'jquery' ), '7.1.3', true );
		wp_enqueue_script( 'highcharts-export-data', WNCM_URL . 'assets/libs/highcharts/export-data.js', array( 'jquery' ), '7.1.3', true );
		wp_enqueue_script( 'perfectscrollbar', WNCM_URL . 'assets/libs/perfectscrollbar/perfectscrollbar.min.js', array( 'jquery' ), '1.3.0', true );
		wp_enqueue_script( 'wp-commerce-dashboard', WNCM_URL . 'assets/js/admin/dashboard.js', array( 'jquery', 'wpcm-components' ), $version, true );

		wp_set_script_translations( 'wp-commerce-dashboard', 'webinane-commerce', WNCM_PATH . 'languages' );
	}

	public function customers() {

		wpcm_validate_nonce();
		$this->is_authorized();

		$query = sanitize_text_field( $_POST['query'] );
		if ( $query ) {
			$customers = Customer::with( array( 'user' ) )->where( 'name', 'like', '%' . $query . '%' )->orWhere( 'email', 'like', '%' . $query . '%' )->paginate( 20 );
		} else {
			$customers = Customer::with( array( 'user' ) )->paginate( 20 );
		}

		wp_send_json_success( $customers->toArray() );
	}

	/**
	 * Update customer
	 *
	 * @return [type] [description]
	 */
	public function update_customer() {

		wpcm_validate_nonce();
		$this->is_authorized();

		$form = array_get( $_POST, 'form' );

		if ( ! array_get( $form, 'id' ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Invalid Customer ID provided', 'webinane-commerce' ) ) );
		}

		$customer = Customer::find( array_get( $form, 'id' ) );
		if ( ! $customer ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Specified customer is not found', 'webinane-commerce' ) ) );
		}
		$email = sanitize_email( array_get( $form, 'email' ) );
		if ( ! $email ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Invalid customer email provided', 'webinane-commerce' ) ) );
		}

		$customer->email = $email;
		$customer->name = sanitize_text_field( array_get( $form, 'name' ) );

		$customer->save();

		$this->update_customer_meta( $customer, $form );

		wp_send_json_success( array( 'message' => esc_html__( 'Customer is updated Successfully', 'webinane-commerce' ) ) );

	}

	/**
	 * Update customer meta.
	 *
	 * @param  Customer $customer [description]
	 * @return [type]             [description]
	 */
	private function update_customer_meta( Customer $customer, $data ) {
		$fields = array( 'company', 'address_line_1', 'address_line_2', 'phone', 'base_country', 'state', 'city', 'zip' );
		$meta_data = array_get( $data, 'meta_data' );

		foreach ( $fields as $field ) {
			foreach ( array( 'billing_', 'shipping_' ) as $type ) {

				if ( $found = array_get( $meta_data, $type . $field ) ) {

					if ( $res = $customer->metas()->where( 'meta_key', $type . $field )->first() ) {
						$res->meta_value = $found;
						$res->save();
					} else {
						$res = $customer->metas()->create(
							array(
								'meta_key'      => $type . $field,
								'meta_value'    => $found,
							)
						);
					}
				}
			}
		}
	}

	/**
	 * Remove customer
	 *
	 * @return void
	 */
	public function remove_customer() {

		wpcm_validate_nonce();
		$this->is_authorized();

		$id = sanitize_text_field( array_get( $_POST, 'id' ) );

		$customer = Customer::find( $id );

		if ( ! $customer ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Customer is not found', 'webinane-commerce' ) ) );
		}

		$customer->delete();
		wp_send_json_success( array( 'message' => esc_html__( 'Customer is deleted Successfully', 'webinane-commerce' ) ) );
	}

	/**
	 * Dashboard Menus.
	 *
	 * @return void
	 */
	public function menus() {
		wpcm_validate_nonce();
		$this->is_authorized();
		/**
		 * Example.
		 * [
		 *  [
		 *      'label' => esc_html__('Inbox'),
		 *      'link'  => 'https://example.com',
		 *      'icon'  => '<img />'
		 *  ]
		 * ]
		 */
		$menus = apply_filters( 'webinane_commerce/dashboard/menus', array() );

		wp_send_json_success( $menus );
	}

	/**
	 * Tables data.
	 *
	 * @return void
	 */
	/*public function tables() {

		$data = apply_filters( 'webinane_commerce/dashboard/tables', [] );

		wp_send_json_success( $data );
	}*/
}
