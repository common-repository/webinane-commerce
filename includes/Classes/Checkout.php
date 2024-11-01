<?php
namespace WebinaneCommerce\Classes;

use WebinaneCommerce\Classes\Customers;
use WebinaneCommerce\Classes\Session;

class Checkout {

	static function init() {

		add_action('wp_ajax__wpcommerce_checkout_data', array(__CLASS__, 'checkout_data'));
		add_action('wp_ajax_nopriv__wpcommerce_checkout_data', array(__CLASS__, 'checkout_data'));

		add_action('wpcm_process_checkout', array(__CLASS__, 'process_checkout'));

		add_action('wp_enqueue_scripts', array(__CLASS__, 'enqueue' ) );

		add_filter('body_class', array(__CLASS__, 'body_class'));
	}

	static function enqueue() {
		$key = 'nonce_wpcm_fieldsphpwpcommerce_frontend_checkout_form_customer_info';
		$_post = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

		if( isset( $_POST[$key]) && wpcm_is_checkout_page() ) {
			$nonce = esc_attr( webinane_set($_POST, $key ) );
			if( ! wp_verify_nonce( $nonce, $key ) ) {
				wpcm_redirect_back_checkout();wp_die();
			}
			do_action('wpcm_process_checkout', $_post);
		}

		if( wpcm_is_checkout_page() ) {
			wp_enqueue_style('wpcm-bootstrap', WNCM_URL . 'assets/css/bootstrap.min.css');
			wp_enqueue_style('bootstrap-datetimepicker', WNCM_URL . 'assets/css/bootstrap-datetimepicker.min.css');
			wp_enqueue_style('fontawesome', WNCM_URL . 'assets/css/fontawesome.min.css');
			wp_enqueue_style('bootstrap-touchspin', WNCM_URL . 'assets/css/jquery.bootstrap-touchspin.min.css');

			wp_enqueue_style('wpcm_responsive', WNCM_URL . 'assets/css/responsive.css');
			wp_enqueue_style('wpcm_style', WNCM_URL . 'assets/css/style.css');
		}
	}
	/**
	 * Finally add to cart.
	 *
	 * @param array $args [description]
	 */
	static function add_to_cart($args = array() ) {

		$id = webinane_set( $args, 'item_id' );
		$data = Session::get_session_data_by_key('cart');

		if ( isset( $data[ $id ] ) ) {

			$data[$id]['quantity'] += (float) webinane_set( $args, 'quantity', 1 );
		} else {
			$data[$id] = array(
				'item_id'		=> $id,
				'quantity'		=> (int) webinane_set( $args, 'quantity', 1 ),
				'price'			=> (float) webinane_set( $args, 'price', 1 )
			);
		}

		Session::set_session_data('cart', $data);

		return esc_html__( 'Added to cart', 'webinane-commerce' );
	}

	/**
	 * Finally add to cart.
	 *
	 * @param array $args [description]
	 */
	static function remove_from_cart($args = array() ) {

		$id = webinane_set( $args, 'item_id' );
		$data = Session::get_session_data_by_key('cart');

		if ( isset( $data[ $id ] ) ) {
			unset($data[ $id ]);
		}

		Session::set_session_data('cart', $data);

		return esc_html__( 'Removed from cart', 'webinane-commerce' );
	}

	/**
	 * [checkout_page_orders description]
	 *
	 * @return [type] [description]
	 */
	static function checkout_page_orders() {

		webinane_template( 'checkout/orders.php' );
	}

	/**
	 * [checkout_page_gateways description]
	 *
	 * @return [type] [description]
	 */
	static function checkout_page_gateways() {
		$gateways = wpcm_get_active_gateways()->get_options();

		$names = apply_filters( 'wpcm_gateways_names', array('offline_payment', 'paypal') );

		webinane_template('checkout/gateways.php', compact('gateways', 'names'));

	}

	/**
	 * [checkout_form_button description]
	 * @return [type] [description]
	 */
	static function checkout_form_button() {

		webinane_template('checkout/button.php');
	}

	/**
	 * [process_checkout description]
	 * @return [type] [description]
	 */
	static function process_checkout() {
		do_action( 'wpcm_pre_process_purchase' );

		$_post = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

		$is_ajax = isset( $_post['is_ajax'] );

		$nonce = esc_attr( webinane_set($_post, 'nonce') );

		if ( ! wp_verify_nonce( $nonce, WPCM_GLOBAL_KEY )) {
			wp_send_json_error( array( 'message' => esc_html__( 'Security verification failed', 'webinane-commerce' ) ) );
		}

		// Make sure the cart isn't empty
		if ( ! wpcm_get_cart_content() ) {
			$valid_data = false;
			wp_send_json_error( array( 'empty_cart' => esc_html__( 'Your cart is empty', 'webinane-commerce' ) ) );
		} else {
			// Validate the form $_POST data
			$valid_data = self::validate_fields($_post, $is_ajax);

			// Allow themes and plugins to hook to errors
			do_action( 'wpcm_checkout_error_checks', $valid_data, $_post );
		}

		if( ! is_user_logged_in() ) {
			$email = sanitize_email( webinane_set( webinane_set( $_post, 'billing' ), 'email' ) );

			if( ! $email ) {
				wp_send_json_error( esc_html__( 'Please provide a valid email', 'webinane-commerce' ) );
			}

			$user_id = username_exists( $email );

			if ( ! $user_id && false == email_exists( $email ) ) {
			    $random_password = wp_generate_password();
			    $user_id = wp_create_user( $email, $random_password, $email );
			    if( is_wp_error( $user_id ) ) {
			    	wp_send_json_error( $user_id->getMessage() );
			    }
			    wp_send_new_user_notifications( $user_id, 'both' );
			    wp_set_auth_cookie( $user_id, true );
			} else {
				$user_id = ( $user_id ) ? $user_id : email_exists( $email );

				if( $user_id ) {
			    	wp_set_auth_cookie( $user_id, true );
				} else {
					wp_send_json_error( esc_html__('Something went wrong', 'webinane-commerce' ) );
				}
			}
		}

		// Validate the user
		$user = wp_get_current_user();

		if( ! isset( $user->ID ) ) {
			wp_send_json_error( esc_html__( 'Please refresh the page and try again', 'webinane-commerce' ) );
		}

		// Let extensions validate fields after user is logged in if user has used login/registration form

		if ( false === $valid_data || ! $user ) {
			if ( $is_ajax ) {
				do_action( 'wpcm_ajax_checkout_errors' );
				wp_die();
			} else {
				return false;
			}
		}

		$billing = webinane_set( $_post, 'billing' );
		$shipping = webinane_set( $_post, 'shipping' );

		// Setup user information
		$user_info = array(
			'id'         => ($user->ID) ? $user->ID : 0,
			'email'      => ($user->user_email) ? $user->user_email : sanitize_email( webinane_set( $billing, 'email') ),
			'first_name' => ($user->user_first) ? $user->user_first : esc_attr( webinane_set( $billing, 'first_name' ) ),
			'last_name' => ($user->user_last) ? $user->user_last : esc_attr( webinane_set( $billing, 'last_name' ) ),
			'address'    => ! empty( $user->address ) ? $user->address : esc_attr(webinane_set( $billing, 'address_line_1')),
		);

		// Update a customer record if they have added/updated information
		$customer = new Customers( $user_info['email'], $_post );
		$customer->insert_meta($customer->customer, $_post);
		if ( $is_ajax ) {
		}

		$auth_key = defined( 'AUTH_KEY' ) ? AUTH_KEY : '';
		// Set up the unique purchase key. If we are resuming a payment, we'll overwrite this with the existing key.
		$purchase_key     = strtolower( md5( $user_info['email'] . date( 'Y-m-d H:i:s' ) . $auth_key . uniqid( 'wpcm', true ) ) );

		// Setup purchase information
		$purchase_data = array(
			'items'	       => wpcm_get_cart_content(),

			'subtotal'     => wpcm_get_cart_subtotal(),    // Amount before taxes and discounts

			'price'        => wpcm_get_cart_total(),    // Amount after taxes
			'purchase_key' => $purchase_key,
			'user_email'   => $user_info['email'],
			'date'         => date( 'Y-m-d H:i:s', current_time( 'timestamp' ) ),
			'user_info'    => stripslashes_deep( $user_info ),
			'post_data'    => $_post,
			'gateway'      => $valid_data['gateway'],
		);

		// Add the user data for hooks
		$valid_data['user'] = $user;

		// Allow themes and plugins to hook before the gateway
		do_action( 'wpcm_checkout_before_gateway', $_post, $user_info, $valid_data );

		// If the total amount in the cart is 0, send to the manual gateway. This emulates a free download purchase
		if ( !$purchase_data['price'] ) {
			// Revert to manual
			$purchase_data['gateway'] = 'offline';
			$_POST['payment-gateway'] = 'offline';
		}

		// Setup the data we're storing in the purchase session

		// Make sure credit card numbers are never stored in sessions

		// Used for showing download links to non logged-in users after purchase, and for other plugins needing purchase data.
		// Send info to the gateway for payment processing
		wpcm_send_to_gateway( $purchase_data['gateway'], $purchase_data );
		wp_die();
	}

	/**
	 * This method is used to validation the checkout form submitted data.
	 *
	 * @param  array   $data     An array of user submitted data.
	 * @param  boolean $is_ajax  Whether the request is an ajax or simple.
	 * @return mix               If ajax then sends the json data, throw exception or array if successful.
	 */
	static function validate_fields($data, $is_ajax) {
		$rules = apply_filters( 'webinane_commerce_checkout_form_validation_rules', [
			'billing.email'				=> 'required|email',
			'billing.first_name'		=> 'required|min:4',
			// 'billing.last_name'			=> 'required|min:4',
			'billing.address_line_1'	=> 'required|min:10',
			'billing.city'				=> 'required',
			'billing.base_country'		=> 'required',
			'nonce'						=> 'required',
			'gateway'					=> 'required'
		] );

		$validation = wpcm_validator()->make($data, $rules);

		// or this way:
		$validation->setAliases([
			'billing.email' => esc_html__('Billing Email', 'webinane-commerce'),
			'billing.first_name' => esc_html__('Billing First Name', 'webinane-commerce'),
			'billing.address_line_1' => esc_html__('Billing Address', 'webinane-commerce'),
			'billing.city' => esc_html__('Billing City', 'webinane-commerce'),
			'billing.base_country' => esc_html__('Billing Country', 'webinane-commerce'),
			'nonce' => esc_html__('Security check', 'webinane-commerce'),
			'gateway' => esc_html__('Payment Gateway', 'webinane-commerce'),
		]);

		// then validate
		$validation->validate();

		if ($validation->fails()) {
			// handling errors
			$errors = $validation->errors();
			if( $is_ajax ) {
				wp_send_json_error( array('message' => implode("\n", $errors->all("<p>:message</p>")) ) );
			}
			throw new \Exception($errors, 1);
		}

		return $data;
	}

	static function body_class($classes) {

		if( is_page() ) {
			if( wpcm_is_checkout_page() ) {
				$classes[] = 'wpcm-checkout-page';
			}
		}
		return $classes;
	}

	static function checkout_data() {

		$user = wp_get_current_user();
		$settings = get_option('_wpcommerce_settings');

		$countries = wpcm_countries()->toArray();

		$symbol = webinane_currency_symbol();

		$cart = (array) wpcm_get_cart_content();

		$items = array();

		if( $cart ) {
			foreach( $cart as $c ) {
				$collection = collect($c);
				$items[$collection->get('item_id')] = array(
					'thumb'			=> wp_get_attachment_image_src( get_post_thumbnail_id( $collection->get('item_id') ), 'thumbnail' ),
					'link'			=> get_permalink($collection->get('item_id')),
					'title'			=> get_the_title( $collection->get('item_id')),
					'qty'			=> webinane_set( $c, 'quantity'),
					'price'			=> webinane_set( $c, 'price'),
				);
			}
		}

		$cart['symbol'] = $symbol;

		$customer = ($user->ID) ? new Customers( $user->user_email ) : array();
		if( $customer ) {
			$customer = $customer->full_customer_info();
		}
		wp_send_json(compact('countries', 'cart', 'customer', 'items'));
	}
}

