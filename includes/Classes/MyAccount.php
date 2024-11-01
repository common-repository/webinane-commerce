<?php
namespace WebinaneCommerce\Classes;

use WebinaneCommerce\Models\Customer;
use WP_Error;

class MyAccount {

	static function init() {
	}

	/**
	 * [process_login_form description]
	 * @return [type] [description]
	 */
	static function process_login_form() {

	}

	static function config() {
		$config = include WNCM_PATH . 'config/my-account.php';
		return $config;
	}

	static function account_config() {
		$config = self::config();
		wp_send_json( array('config' => $config ) );
	}

	/**
	 * [user_data description]
	 * @return [type] [description]
	 */
	static function user_data() {

		$config = self::config();

		$user = wp_get_current_user();
		$customer_data = [];
		if( ! isset($user->ID) ) {
			$user = array();
		} else {
			$user->meta = self::user_meta($user);
			$customer = new Customers($user->data->user_email);

			$customer_data = $customer->full_customer_info();
		}

		$user = json_decode(json_encode($user), true);
		$customer = json_decode(json_encode($customer_data), true);

		$data = array('customer' => $customer, 'user' => $user);

		//dd(array_get($data, 'customer.email'));
		$options = array();

		foreach( $config[0]['fields'] as $field ) {
			if( isset( $field['id'] ) ) {
				$thisdata = webinane_array($data);
				$options[ $field['id'] ] = array_get($data, webinane_set( $field, 'value_key', 'hello'));
			}
		}
		wp_send_json( compact('options', 'customer', 'user') );
	}

	/**
	 * @param  [type]
	 * @return [type]
	 */
	static function user_meta($user) {
		$meta = get_user_meta( $user->ID );
		$new_meta = array();
		foreach( $meta as $key => $me ) {
			$new_meta[$key] = maybe_unserialize( $me[0] );
		}

		return $new_meta;
	}

	/**
	 * This method is hooked up with ajax handler for frontend updating current user profile
	 * the base profile, billing information and social network information.
	 *
	 * @return [type] [description]
	 */
	static function save_profile() {
		global $wpdb;

		if( ! is_user_logged_in() ) {
			wp_send_json_error( array('message' => esc_html__( 'You are not authorized', 'webinane-commerce' )) );
		}
		$_post = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
		$_post = webinane_array($_post);
		$options = $_post->get('options', array());
		$message = '';

		$config = self::config();

		$user = wp_get_current_user();
		$customer_data = [];
		if( ! isset($user->ID) ) {
			$user = array();
		} else {
			$user->meta = self::user_meta($user);
			$customer = new Customers($user->data->user_email);

			$customer_data = $customer->full_customer_info();
		}

		$user = json_decode(json_encode($user), true);
		$customer = json_decode(json_encode($customer_data), true);

		$data = array('customer' => $customer, 'user' => $user);

		foreach( $config[0]['fields'] as $field ) {
			if( isset( $field['id'] ) ) {
				$val_key = webinane_set($field, 'value_key', 'hello');
				$id = $field['id'];
				if( $val_key ) {
					data_set($data, $val_key, webinane_array($options)->get($id));
				}
			}
		}

		/**
		 * Here we do all the option over password and confirm password.
		 * If all the validation are successful then we append the password into
		 * the array otherwise we remove the user_pass key from the array. So it
		 * should not updated at all.
		 * @var array $options
		 */
		$opt = webinane_array($options);
		$customer = array_get($data, 'customer');

		if( $password = $opt->get('password') ) {
			if(  $confirm_pass = $opt->get('password_confirm') ) {
				if( $password === $confirm_pass ) {
					if( strlen($password) > 5 ) {
						data_set($data, 'user.data.user_pass', $password);
					} else {
						$message = esc_html__('Password length must be at least 10 character', 'webinane-commerce' ) . "\n";
					}
				} else {
					$message = esc_html__('Password and confirm password does not match', 'webinane-commerce' ) . "\n";
				}
			} else {

				array_forget($data, 'user.data.user_pass');
			}
		} else {
			array_forget($data, 'user.data.user_pass');
		}
		if( $message ) {
			wp_send_json_error( array('message' => $message) );
		}


		/**
		 * Here we are updating the user object. But we need to unset password field.
		 *
		 * @todo  We need to unset password key from data.
		 */

		wp_update_user( array_get($data, 'user.data') );

		$our_meta = apply_filters( 'wpmc_user_account_custom_meta_keys', array('avatar', 'facebook', 'twitter', 'linkedin', 'pinterest', 'first_name', 'last_name', 'description', 'show_donation_publicaly') );

		foreach( array_get($data, 'user.data.meta') as $meta_key => $val ) {
			if( in_array($meta_key, $our_meta)) {
				update_user_meta(get_current_user_id(), $meta_key, $val);
			}
		}

		/**
		 * Here we are saving the customer data. Will need to convert it into a method.
		 * Also we'll have to check whether customer basic data is successfully saved.
		 *
		 * @todo  We need to verify that $data array has meta key.
		 *
		 * @var array
		 */
		if($customer) {
			$meta = array_get($customer, 'meta');
			unset($customer['meta']);

			$res = Customer::find($customer['id']);

			if($res) {
				$metas = $res->metas;
				foreach( $meta as $meta_key => $val ) {
					$found = $metas->where('meta_key', $meta_key)->first();
					if( $found ) {
						$found->meta_value = $val;
						$found->save();
					} else {
						$res->metas()->create([
							'meta_key'	=> $meta_key,
							'meta_value' => $val
						]);
					}
				}
			}
		}

		wp_send_json_success( array('message' => $message. esc_html__( 'Profile Updated', 'webinane-commerce' ), 'data' => $data) );
	}

	/**
	 * [login description]
	 * @return [type] [description]
	 */
	static function login() {
		$nonce = sanitize_text_field( webinane_set( $_POST, 'nonce' ) );

		if( ! wp_verify_nonce( $nonce, WPCM_GLOBAL_KEY ) ) {
			wp_send_json_error( ['message' => esc_html__('Request is not validated.', 'webinane-commerce')] );
		}
		$form = array_get($_POST, 'form');

		$userdata = array(
		    'user_login'    	=>  sanitize_text_field( webinane_set( $form, 'username' ) ),
		    'user_password'   	=>  sanitize_text_field( webinane_set( $form, 'password' ) ),
		    'rememberme'		=>	sanitize_text_field( webinane_set( $form, 'rememberme' ) )
		);

		$user = wp_signon( $userdata, false );

		if ( is_wp_error($user) ) {
			wp_send_json_error( ['message' => $user->get_error_message()] );
		}

		wp_send_json_success( ['message' => esc_html__('Login successful.', 'webinane-commerce')] );
	}

	/**
	 * Register new user.
	 *
	 * @return [type] [description]
	 */
	static function register() {
		$nonce = sanitize_text_field( webinane_set( $_POST, 'nonce' ) );

		if( ! wp_verify_nonce( $nonce, WPCM_GLOBAL_KEY ) ) {
			wp_send_json_error( ['message' => esc_html__('Request is not validated.', 'webinane-commerce')] );
		}
		$form = array_get($_POST, 'form');

		if (! get_option( 'users_can_register' ) ) {
			wp_send_json_error( ['message' => esc_html__('Registration is not allowed at the moment', 'webinane-commerce')] );

		}

		$email = sanitize_email( webinane_set($form, 'email') );
		$username = sanitize_text_field( webinane_set($form, 'username') );
		$password = sanitize_text_field( webinane_set($form, 'password') );
		$c_password = sanitize_text_field( webinane_set($form, 'confirm_password') );

		if(strlen($password) < 10) {
			wp_send_json_error( ['message' => esc_html__('Minimum password length must be 10.', 'webinane-commerce')] );
		}
		if($password !== $c_password) {
			wp_send_json_error( ['message' => esc_html__('Password and confirm password don\'t match', 'webinane-commerce')] );
		}

		$user = self::register_new_user($username, $email, $password);

		if ( is_wp_error($user) ) {
			wp_send_json_error( ['message' => $user->get_error_message()] );
		}

		wp_send_json_success( ['message' => esc_html__('Registration successful, please login.', 'webinane-commerce')] );
	}

	/**
	 * Handles registering a new user.
	 *
	 * @since 2.5.0
	 *
	 * @param string $user_login User's username for logging in
	 * @param string $user_email User's email address to send password and add
	 * @return int|WP_Error Either user's ID or error on failure.
	 */
	private static function register_new_user( $user_login, $user_email, $password ) {
		$errors = new WP_Error();

		$sanitized_user_login = sanitize_user( $user_login );
		/**
		 * Filters the email address of a user being registered.
		 *
		 * @since 2.1.0
		 *
		 * @param string $user_email The email address of the new user.
		 */
		$user_email = apply_filters( 'user_registration_email', $user_email );

		// Check the username.
		if ( '' == $sanitized_user_login ) {
			$errors->add( 'empty_username', __( '<strong>Error</strong>: Please enter a username.', 'webinane-commerce' ) );
		} elseif ( ! validate_username( $user_login ) ) {
			$errors->add( 'invalid_username', __( '<strong>Error</strong>: This username is invalid because it uses illegal characters. Please enter a valid username.', 'webinane-commerce' ) );
			$sanitized_user_login = '';
		} elseif ( username_exists( $sanitized_user_login ) ) {
			$errors->add( 'username_exists', __( '<strong>Error</strong>: This username is already registered. Please choose another one.', 'webinane-commerce' ) );

		} else {
			/** This filter is documented in wp-includes/user.php */
			$illegal_user_logins = (array) apply_filters( 'illegal_user_logins', array() );
			if ( in_array( strtolower( $sanitized_user_login ), array_map( 'strtolower', $illegal_user_logins ), true ) ) {
				$errors->add( 'invalid_username', __( '<strong>Error</strong>: Sorry, that username is not allowed.', 'webinane-commerce' ) );
			}
		}

		// Check the email address.
		if ( '' == $user_email ) {
			$errors->add( 'empty_email', __( '<strong>Error</strong>: Please type your email address.', 'webinane-commerce' ) );
		} elseif ( ! is_email( $user_email ) ) {
			$errors->add( 'invalid_email', __( '<strong>Error</strong>: The email address isn&#8217;t correct.', 'webinane-commerce' ) );
			$user_email = '';
		} elseif ( email_exists( $user_email ) ) {
			$errors->add( 'email_exists', __( '<strong>Error</strong>: This email is already registered, please choose another one.', 'webinane-commerce' ) );
		}

		/**
		 * Fires when submitting registration form data, before the user is created.
		 *
		 * @since 2.1.0
		 *
		 * @param string   $sanitized_user_login The submitted username after being sanitized.
		 * @param string   $user_email           The submitted email.
		 * @param WP_Error $errors               Contains any errors with submitted username and email,
		 *                                       e.g., an empty field, an invalid username or email,
		 *                                       or an existing username or email.
		 */
		do_action( 'register_post', $sanitized_user_login, $user_email, $errors );

		/**
		 * Filters the errors encountered when a new user is being registered.
		 *
		 * The filtered WP_Error object may, for example, contain errors for an invalid
		 * or existing username or email address. A WP_Error object should always returned,
		 * but may or may not contain errors.
		 *
		 * If any errors are present in $errors, this will abort the user's registration.
		 *
		 * @since 2.1.0
		 *
		 * @param WP_Error $errors               A WP_Error object containing any errors encountered
		 *                                       during registration.
		 * @param string   $sanitized_user_login User's username after it has been sanitized.
		 * @param string   $user_email           User's email.
		 */
		$errors = apply_filters( 'registration_errors', $errors, $sanitized_user_login, $user_email );

		if ( $errors->has_errors() ) {
			return $errors;
		}

		$user_pass = ($password) ? $password : wp_generate_password( 12, false );
		$user_id   = wp_create_user( $sanitized_user_login, $user_pass, $user_email );
		if ( ! $user_id || is_wp_error( $user_id ) ) {
			$errors->add(
				'registerfail',
				sprintf(
					/* translators: %s: Admin email address. */
					__( '<strong>Error</strong>: Couldn&#8217;t register you&hellip; please contact the <a href="mailto:%s">webmaster</a> !', 'webinane-commerce' ),
					get_option( 'admin_email' )
				)
			);
			return $errors;
		}

		update_user_option( $user_id, 'default_password_nag', true, true ); // Set up the password change nag.

		/**
		 * Fires after a new user registration has been recorded.
		 *
		 * @since 4.4.0
		 *
		 * @param int $user_id ID of the newly registered user.
		 */
		do_action( 'register_new_user', $user_id );

		return $user_id;
	}


	/**
	 * Get user orders.
	 *
	 * @return [type] [description]
	 */
	static function get_user_orders() {
		global $wpdb;
		$user = wp_get_current_user();
		if( ! is_user_logged_in() ) {
			wp_send_json_error( array('message' => esc_html__( 'You are not authorized to access it', 'webinane-commerce' )) );
		}
		$settings = wpcm_get_settings();
		$query = new \WP_Query(array('post_type' => 'orders', 'meta_key' => '_wpcm_order_customer_id', 'meta_value' => $user->ID, 'posts_per_page'));
		$symbol = webinane_currency_symbol();
		$position = $settings->get('currency_position', 'left');
		$sep = $settings->get('thousand_separator', ','); // Thousand Separator
		$d_sep = $settings->get('decimal_separator', '.'); // Decimal separator
		$d_point = $settings->get('number_decimals', 2); // Decimal numbers
		$strings = [
			'orders' => apply_filters( 'wpcm_orders_admin_menu_label', esc_html__('Orders', 'webinane-commerce') ),
			'order' => apply_filters( 'wpcm_order_admin_menu_label', esc_html__('Order', 'webinane-commerce') ),
		];

		$customer = new Customers( $user->data->user_email );
		$customer_data = $customer->full_customer_info();
		if( $query->have_posts() ) {
			$found_posts = $query->posts;
			wp_reset_postdata();
			wp_send_json(array('orders' => wp_list_pluck( $found_posts, 'ID' ), 'settings' => compact('symbol', 'position', 'strings', 'sep', 'd_sep', 'd_point'), 'customer' => $customer_data ));
		} else {
			$order_label = apply_filters('wpcm_order_admin_menu_label', esc_html__('Order', 'webinane-commerce'));
			wp_send_json_error( array('message' => sprintf(esc_html__('You don\'t have any %s', 'webinane-commerce' ), $order_label)) );
		}
	}

	/**
	 * Get user used payment methods.
	 *
	 * @return [type] [description]
	 */
	static function get_user_payment_methods() {
		global $wpdb;
		$user = wp_get_current_user();
		if( ! is_user_logged_in() ) {
			wp_send_json_error( array('message' => esc_html__( 'You are not authorized to access it.', 'webinane-commerce' )) );
		}
		$query = new \WP_Query(array('post_type' => 'orders', 'meta_key' => '_wpcm_order_customer_id', 'meta_value' => $user->ID));

		if( $query->have_posts() ) {
			$found_posts = $query->posts;
			$gatways = array();
			foreach($found_posts as $post){
				$active_gatwaus = (object) wpcm_get_active_gateways(true);
				$gate_name = get_post_meta($post->ID, '_wpcm_order_gateway', true);
				$gatways[$gate_name] = $active_gatwaus->$gate_name;
			}
			wp_reset_postdata();
			$gatways = array_chunk($gatways, 4);
			wp_send_json(array('gateways' => $gatways ));
		} else {
			wp_send_json_error( array('message' => esc_html__('You don\'t have any gatways', 'webinane-commerce' )) );
		}
	}


	/**
	 * Get single order data.
	 *
	 * @return [type] [description]
	 */
	static function get_single_order() {

		global $wpdb;
		if( ! is_user_logged_in() ) {
			wp_send_json_error( array('message' => esc_html__( 'You are not authorized to access it', 'webinane-commerce' )) );
		}
		$user = wp_get_current_user();

		$order_id = esc_attr( webinane_set( $_POST, 'order_id') );
		$order = get_post( $order_id );

		$order_data = Orders::order_payment_info($order);

		wp_send_json(compact('order_data'));
	}

	static function get_order_notes() {
		global $wpdb;
		$user = wp_get_current_user();
		if( ! is_user_logged_in() ) {
			wp_send_json_error( array('message' => esc_html__( 'You are not authorized to access it', 'webinane-commerce' )) );
		}

		$order_id = esc_attr( webinane_set( $_POST, 'order_id') );

		$args = array(
		    'status'  => 'approve',
		    'number'  => '5',
		    'post_id' => $order_id, // use post_id, not post_ID
		);
		$comments = get_comments( $args );

		wp_send_json( array( 'notes' => $comments ) );
	}

	static function add_order_notes() {
		global $wpdb;
		$user = wp_get_current_user();
		if( ! is_user_logged_in() ) {
			wp_send_json_error( array('message' => esc_html__( 'You are not authorized to access it', 'webinane-commerce' )) );
		}

		$order_id = esc_attr( webinane_set( $_POST, 'order_id') );
		$note = sanitize_textarea_field( webinane_set( $_POST, 'note') );

		if( ! $note ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Note should not be empty', 'webinane-commerce' ) ) );
		}

		if( strlen($note) < 10 ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Please write a brief note', 'webinane-commerce' ) ) );
		}

		$time = current_time('mysql');

		$data = array(
		    'comment_post_ID' => $order_id,
		    'comment_author' => $user->display_name,
		    'comment_author_email' => $user->user_email,
		    'comment_author_url' => '',
		    'comment_content' => wp_kses_post($note),
		    'comment_type' => '',
		    'comment_parent' => 0,
		    'user_id' => $user->ID,
		    'comment_author_IP' => $_SERVER['REMOTE_ADDR'],
		    'comment_agent' => $_SERVER['HTTP_USER_AGENT'],
		    'comment_date' => $time,
		    'comment_approved' => 1,
		);

		$comment_id = wp_new_comment($data);
		if( $comment_id ) {
			//update_comment_meta( $comment_id, '_comment_status', '$status' );
		}

		$args = array(
		    'status'  => 'approve',
		    'number'  => '5',
		    'post_id' => $order_id, // use post_id, not post_ID
		);
		$comments = get_comments( $args );

		wp_send_json_success( array( 'notes' => $comments, 'message' => esc_html__('Note is added successfully', 'webinane-commerce' ) ) );
	}
}


add_action('wp', [MyAccount::class, 'process_login_form']);
