<?php
namespace WebinaneCommerce\Classes;

use WebinaneCommerce\Models\Customer;
use WebinaneCommerce\Models\CustomerMeta;

class Customers
{

	protected $db_table = 'wpcommerce_customers';
	protected static $mdb_table = 'wpcommerce_customers';
	protected $meta_table = 'wpcommerce_customer_meta';
	protected $id_or_email = '';
	var $userdata = array();
	var $wp_user = array();
	protected $fields = array( 'user_id', 'email', 'name' );

	public $meta = array( 'first_name', 'last_name', 'address_line_1', 'address_line_2', 'city', 'base_country', 'state', 'zip', 'phone', 'company');
	public $customer;
	static $instance;

	function __construct( $email, $data = array() ) {
		$this->id_or_email = $email;
		$this->find($email, $data);
	}

	public static function instance() {
		if( self::$instance == null ) {
			self::$instance = new self('');
		}

		return self::$instance;
	}

	/**
	 * [find description]
	 *
	 * @param  [type] $email [description]
	 * @param  [type] $data  [description]
	 * @return [type]        [description]
	 */
	function find($email, $data) {

		global $wpdb;

		$customer = null;

		if( is_email( $email )) {
			$customer = Customer::with('metas')->where('email', $email)->first();
		} else if(absint( $email )) {
			$customer = Customer::with('metas')->find($email);
		}

		/*if(! $customer) {
			return false;
		}*/

		if( $customer ) {
			
			$this->customer = $customer;
			$this->userdata = $customer;
			$this->wp_user = new \WP_User($email);
		} else {
			if( $email ) {
				$customer = $this->create_new($email, $data);
				$this->customer = $customer;
				$this->userdata = $customer;
				$this->wp_user = new \WP_User($email);
			} else {
				$customer = new Customer;
				$this->customer = $customer;
			}
		}

		return $customer;
	}

	/**
	 * [query description]
	 *
	 * @return [type] [description]
	 */
	function query() {
		global $wpdb;

		$email = $this->id_or_email;

		if( is_email($email) ) {
			$res = Customer::where('email', $email)->first(); 
		} else {
			$res = Customer::find($email);
		}

		return $res;
	}

	/**
	 * [query_meta description]
	 *
	 * @param  [type] $customer [description]
	 * @return [type]           [description]
	 */
	function query_meta($customer) {
		global $wpdb;
		if( ! $customer ) {
			return array();
		}
		$res = $wpdb->get_results( $wpdb->prepare("SELECT * FROM ".$wpdb->prefix.$this->meta_table." WHERE customer_id = %d", $customer->id) );

		return $res;
	}
	/**
	 * [create_new description]
	 * @param  [type] $email [description]
	 * @param  [type] $data  [description]
	 * @return [type]        [description]
	 */
	function create_new($email, $data) {
		global $wpdb;
		
		$user_id = get_current_user_id();
		if( isset($data['user_info']['id']) ) {
			$user_id = $data['user_info']['id'];
		}

		
		if( is_email( $email ) ) {

			$customer = new Customer;
			$customer->email = $email;
			$customer->name = webinane_set( $data, 'first_name') . ' ' . webinane_set( $data, 'last_name' );
			$customer->user_id = ($user_id) ? $user_id : 0;
			$customer_id = $customer->save();

			if ( $customer_id ) {
				$this->insert_meta($customer, $data);
				return $customer;
			} else {
				return new \WP_Error( esc_html__( 'There is something wrong with adding customer', 'webinane-commerce' ) );
			}
		} 

	}

	/**
	 * [insert_meta description]
	 *
	 * @param  [type] $id [description]
	 * @return [type]     [description]
	 */
	function insert_meta(Customer $customer, $data = array()) {
		global $wpdb;

		if ( ! $data ) {
			$data = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
		}

		if( $customer ) {
			foreach( array('billing', 'shipping') as $address_keys) {

				$new_data = webinane_set($data, $address_keys);

				foreach ($this->meta as $value) {
					
					if( $d_value = webinane_set( $new_data, $value ) ) {
						$this->update_meta( $customer, $address_keys.'_'.$value, $d_value);
					}
				}
			}
			if( $ship_diff = esc_attr( webinane_set( $data, 'ship_diff') ) ) {
				$this->update_meta($customer, 'ship_diff', $ship_diff);
			}
		}
	}

	/**
	 * This method is used to retrieve all the customer data including WP_User data.
	 * 
	 * @return array Returns the complete user info.
	 */
	function full_customer_info($customer = null) {

		if( ! $customer ) {
			$customer = $this->customer;
		}
		$return = null;
		if( $customer ) {
			$return = $customer->getAttributes();
			$return = (object)$return;
			if( $customer->metas ) {
				foreach( $customer->metas as $met ) {
					$return->meta[$met->meta_key] = $met->meta_value;
				}
			}

		}
		return $return;
	}

	public static function all() {
		global $wpdb;

		$res = Customer::all()->toArray();

		return $res;
	}

	/**
	 * Copy the billing data to shipping. The param should be the complete cusotmer data.
	 * 
	 * @param  array $customer  The customer data should complete customer billing and shipping info
	 * @return void             This function doesn't return any value.
	 */
	public function copyBilling(Customer $customer) {
		
		if( ! $customer ) {
			wp_send_json_error(array('message' => esc_html__('Customer not found', 'webinane-commerce')));
		}
		
		$exp = explode(' ', $customer->name);
		$this->update_meta($customer, 'shipping_first_name', $exp[0]);

		if( isset( $exp[1] ) ) {
			$this->update_meta($customer, 'shipping_last_name', $exp[1]);
		}

		foreach( $this->meta as $m ) {
			$found = $this->get_meta( $customer, 'billing_'.$m );

			if( $found ) {
				$this->update_meta($customer, 'shipping_'.$m, $found->meta_value);
			}
		}
	}
	/**
	 * Update or insert customer meta data.
	 *
	 * @param  integer $id         The Customer id from wpcommerce_customers db table.
	 * @param  string $meta_key    The meta key
	 * @param  string $meta_value  The meta value.
	 * @return void                This method doesn't return any value.
	 */
	public function update_meta(Customer $customer, $meta_key, $meta_value) {

		$res = $customer->metas->where('meta_key', $meta_key);

		if( $res->count() ) {
			$res = $res->first();
			$res->meta_value = $meta_value;
			if($res instanceof CustomerMeta) {
				$res->save();
			}

		} else {
			$customer->metas()->save(new CustomerMeta([
				'customer_id' => $customer->id,
				'meta_key'	=> $meta_key,
				'meta_value' => $meta_value,
			]));
		}
	}

	/**
	 * Get customer meta data.
	 *
	 * @param  integer  $id       The Customer id from wpcommerce_customers db table.
	 * @param  string  $meta_key  The meta key.
	 * @param  boolean $uniq      Whether to retrieve a single row or multiple rows.
	 * @return object             Returns the stdobject of db data.
	 */
	public function get_meta(Customer $customer, $meta_key, $uniq = true) {
		global $wpdb;

		if( $uniq ) {
			return $customer->metas->where('meta_key', $meta_key)->first();
		} else {
			return $customer->metas->where('meta_key', $meta_key);
		}
	}

	/**
	 * Add new customer from Add/Edit order from admin.
	 */
	static function add_new_customer() {
		if( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error(array('message' => esc_html__('You are not authorized', 'webinane-commerce')));
		}

		$data = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

		$email = array_get($data, 'form.email');
		$name = array_get($data, 'form.name');
		$user_id = absint(array_get($data, 'form.user_id'));
		
		if( ! is_email($email) ) {
			wp_send_json_error(array('message' => esc_html__('Invalid email address provided', 'webinane-commerce')));
		}
		if( ! $name || strlen($name) < 4 ) {
			wp_send_json_error(array('message' => esc_html__('Name is either empty or size is less than 4', 'webinane-commerce')));
		}
		$customer = new Customer;

		$customer->name = $name;
		$customer->email = $email;
		$customer->user_id = ($user_id) ? $user_id : get_current_user_id();
		$customer->created_at = date('Y-m-d h:i:s');

		$customer->save();

		wp_send_json_success( ['message' => esc_html__('Customer is created successfully', 'webinane-commerce'), 'customers' => Customer::all()->toArray()] );
	}

	/**
	 * Get wp users.
	 * @return [type] [description]
	 */
	public static function get_wp_users() {
		if( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error(array('message' => esc_html__('You are not authorized', 'webinane-commerce')));
		}
		$query = esc_attr(webinane_set($_POST, 'query'));

		$users = get_users(['search' => $query.'*']);
		wp_send_json_success( ['users' => $users] );
	}

	/**
	 * Remvoe customer.
	 * @return [type] [description]
	 */
	public static function remove_single_customer() {
		if( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error(array('message' => esc_html__('You are not authorized', 'webinane-commerce')));
		}
		$customer_id = absint(webinane_set($_POST, 'customer'));

		if( ! $customer_id ) {
			wp_send_json_error(array('message' => esc_html__('Invalid Customer ID', 'webinane-commerce')));
		}

		$customer = Customer::find($customer_id);

		if( ! $customer ) {
			wp_send_json_error(array('message' => esc_html__('Given customer not found', 'webinane-commerce')));
		}

		$customer->metas()->delete();
		$customer->delete();

		wp_send_json_success( ['message' => esc_html__('Customer is removed', 'webinane-commerce'), 'customers' => Customer::all()] );
	}

	/**
	 * Get full customer info for ajax call.
	 * @return [type] [description]
	 */
	public static function ajax_full_customer_info() {
		if( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error(array('message' => esc_html__('You are not authorized', 'webinane-commerce')));
		}
		$customer_id = absint(webinane_set($_POST, 'customer'));

		if( ! $customer_id ) {
			wp_send_json_error(array('message' => esc_html__('Invalid Customer ID', 'webinane-commerce')));
		}

		$customer = Customer::find($customer_id);

		$info = self::instance()->full_customer_info($customer);

		wp_send_json_success(['customer' => $info]);
	}
}
