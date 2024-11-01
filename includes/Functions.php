<?php

use WebinaneCommerce\Classes\Session;
use WebinaneCommerce\Classes\Metaboxes;
use WebinaneCommerce\Classes\TaxonomyMetaboxes;

if ( ! function_exists('wpcm_countries') ) {

	/**
	 * Retrieve an instance of Arrayy\Arrayy for the countries data.
	 * @todo  Need to use external api like form-api.com
	 * @return Arrayy\Arrayy Returns an instance of Arrayy\Arrayy
	 */
	function wpcm_countries() {
		$countries = include WNCM_PATH . 'assets/data/_countries.php'; 
		$countries = webinane_array($countries);
		
		return $countries;
	}
}

if ( ! function_exists('wpcm_states') ) {

	/**
	 * Retrieve an instance of Arrayy\Arrayy for the countries data.
	 * @todo  Need to use external api like form-api.com
	 * @return Arrayy\Arrayy Returns an instance of Arrayy\Arrayy
	 */
	function wpcm_states($country) {
		if ( ! $country ) {
			return [];
		}

		$all_states = include WNCM_PATH . 'assets/data/states.php'; 
		$states = webinane_array(array_get( $all_states, $country ) );
		
		return $states;
	}
}

if( ! function_exists('wpcm_validator') ) {

	/**
	 * The function is used to validate the form data.
	 *
	 * @return Object Returns an instance of Rakit\Validation\Validator;
	 */
	function wpcm_validator() {
		return new Rakit\Validation\Validator;
	}
}

if ( ! function_exists('webinane_currencies') ) {

	/**
	 * Returns an array of currencies data.
	 *
	 * @return [type] [description]
	 */
	function webinane_currencies() {
		$currencies = get_transient( 'webinane_commerce_currencies' );

		if( ! $currencies ) {

			$data = webinane_get_json('assets/data/_currencies.json');
			$currencies = array();

			foreach($data as $dat) {
				$file = WNCM_PATH.'assets/data/currencies/default/'.$dat.'.json';
				if( file_exists($file)) {
					$content = webinane_get_json('assets/data/currencies/default/'.$dat.'.json');
					if( $content ) {
						$toArray = $content->toArray();
						$key = array_get($toArray, 'iso.code');
						$currencies[$key] = $toArray;
					}

				}
			}
			set_transient( 'webinane_commerce_currencies', $currencies, 3600 );
		}
		return $currencies;
	}
}

if ( ! function_exists('webinane_array') ) {

	/**
	 * Create an instance to Voku Arrayy\Arrayy
	 *
	 * @param  array  $data Data should be array or object
	 * @return Arrayy\Arrayy       Instance of Arrayy\Arrayy
	 */
	function webinane_array($data = array()) {
		return collect($data);
	}
}

if( ! function_exists('webinane_get_json') ) {

	/**
	 * Retrieves json content from file then decode the json.
	 *
	 * @param  string $file Must be inside plugin not the complete path.
	 * @return Arrayy\Arrayy       Returns instance of Arrayy\Arrayy.
	 */
	function webinane_get_json($file) {
		if(webinane_filesystem()) {
			$content = webinane_filesystem()->get_contents(WNCM_PATH . $file);
		} else {
			$content = wp_remote_get( WNCM_URL . $file );
			$content = wp_remote_retrieve_body( $content );
		}
		$content = json_decode($content, true);
		$data = webinane_array($content);

		return $data;
	}
}

if ( ! function_exists('webinane_set' ) ) {
	/**
	 * Check array or objects for specific key so they don't display warning.
	 *
	 * @param  array|object $var [description]
	 * @param  string       $key [description]
	 * @param  string       $def [description]
	 * @return string            [description]
	 */
	function webinane_set($var, $key, $def = null ) {

		if ( is_object($var ) ) {
			return isset($var->{$key}) ? $var->{$key} : $def;
		} else if( is_array($var) ) {
			return isset( $var[$key] ) ? $var[ $key ] : $def;
		}

		return $def;
	}
}

/**
 * Wp Commerce currency array.
 *
 * @param  [type] $field [description]
 * @return [type]        [description]
 */
function wpcm_currency_assos_data($field = array()) {
	$currencies = webinane_currencies();

	$return = get_transient( 'webinane_currencies_associative_array' );

	if( ! $return ) {

		$return = array();

		foreach($currencies as $k => $currency) {
			$arr = webinane_array($currency);
			$return[$k] = $arr->get('name') .' '. array_get($currency, 'units.major.symbol');
		}
		set_transient( 'webinane_currencies_associative_array', $return, 3600 );
	}

	return $return;
}

/**
 * Returns the assosiative array for fields.
 *
 * @param  [type] $field_args [description]
 * @return [type]             [description]
 */
function wpmc_posts_array($field_args) {
	_deprecated_function( __FUNCTION__, '0.1.0', 'wpcm_posts_data()' );
	$options = array('' => esc_html__( '--Select--', 'webinane-commerce' ));
	$args = $field_args->args('query_args');
	if ( $args ) {

		$posts = new WP_Query($args);

		if ( $posts->have_posts() ) {
			while($posts->have_posts()) {
				$posts->the_post();

				$options[get_the_id()] = get_the_title();
			}
		}
		wp_reset_postdata();
	}

	return $options;
}

/**
 * Returns assosiative array with post ID and title.
 *
 * @param  [type] $args [description]
 * @return [type]       [description]
 */
function wpcm_posts_data($args) {

	$options = array();

	if ( $args ) {

		$query = new WP_Query($args);

		if ( $query->have_posts() ) {
			while($query->have_posts()) {
				$query->the_post();

				$options[get_the_id()] = get_the_title();
			}
		}
		wp_reset_postdata();
	}

	return $options;
}

/**
 * Get a list of registered post sidebars.
 *
 * @param  [type] $args [description]
 * @return [type]       [description]
 */
function wpcm_sidebar_data($args) {
	global $wp_registered_sidebars;
	$sidebars = $wp_registered_sidebars;
	$data = array('' => esc_html__('No Sidebar', 'webinane-commerce'));
	foreach ((array) $sidebars as $sidebar) {
	    $data[webinane_set($sidebar, 'id')] = webinane_set($sidebar, 'name');
	}

	return $data;
}
/**
 * Add to cart.
 *
 * @param  array  $args [description]
 * @return [type]       [description]
 */
function wpcm_add_to_cart($args = array() ) {

	return \WebinaneCommerce\Classes\Checkout::add_to_cart($args);
}

/**
 * Remove from cart
 *
 * @param  array  $args [description]
 * @return [type]       [description]
 */
function wpcm_remove_from_cart($args = array() ) {

	return \WebinaneCommerce\Classes\Checkout::remove_from_cart($args);
}

/**
 * [wpcm_get_cart_content description]
 *
 * @return [type] [description]
 */
function wpcm_get_cart_content() {
	return Session::get_session_data_by_key('cart');
}

/**
 * Get the list of gateways.
 *
 * @return [type] [description]
 */
function wpcm_get_active_gateways($filtered = false) {

	$gateways = apply_filters( 'wpcommerce_payment_gateways', array() );
	if( $filtered ) {
		$new = array();
		foreach($gateways as $id => $gateway) {
			if($gateway->is_active()) {
				$new[$id] = $gateway;
			}
		}
		return $new;
	}

	return $gateways;
}

/**
 * Get the main plugin settings.
 *
 * @param  boolean $collect [description]
 * @param  boolean $set_def [description]
 * @return [type]           [description]
 */
function wpcm_get_settings($collect = true, $set_def = false) {

	$options = (array) get_option(WPCM_SETTINGS_KEY);

	if( $set_def ) {
		$options = \WebinaneCommerce\Admin\Settings::set_default_setting($options);
	}

	if( $collect ) {
		return collect($options);
	}

	return $options;
}

/**
 * Check whether the current page is checkout page.
 *
 * @return [type] [description]
 */
function wpcm_is_checkout_page() {
	global $wp_query;

	if ( ! is_admin() && is_page() ) {

		$checkout_page_id = wpcm_get_settings()->get( 'checkout_page', false );

		if( $checkout_page_id ) {
			if( is_page( $checkout_page_id ) ) {
				return true;
			}
		}
	}

	return false;
}

/**
 * Check whether the current page is checkout page.
 *
 * @return [type] [description]
 */
function wpcm_get_success_page_url($args) {
	$settings = wpcm_get_settings();
	$page_id = $settings->get('success_page');

	if( ! $page_id ) {
		if( defined('DOING_AJAX' ) ) {
			wp_send_json_error( array('message' => esc_html__( 'Unable to find success page, Plese contact administrator', 'webinane-commerce' )) );
		} else {
			throw new Exception(esc_html__( 'Unable to find success page, Plese contact administrator', 'webinane-commerce' ), 1);

		}
	}

	$url = add_query_arg($args, get_permalink( $page_id ));

	if( ! esc_url( $url ) ) {
		if( defined('DOING_AJAX' ) ) {
			wp_send_json_error( array('message' => esc_html__( 'Invalided success page URL, Plese contact administrator', 'webinane-commerce' )) );
		} else {
			throw new Exception(esc_html__( 'Invalid success page URL, Plese contact administrator', 'webinane-commerce' ), 1);
		}
	}

	return $url;
}

/**
 * Retrieve the subtotal for the items in cart.
 *
 * @return [type] [description]
 */
function wpcm_get_cart_subtotal() {
	$total = 0;

	if( $cart = wpcm_get_cart_content() ) {
		foreach( $cart as $crt ) {
			$total += ((float) webinane_set( $crt, 'price' ))*(webinane_set( $crt, 'quantity' ));
		}
	}

	return $total;
}

/**
 * Retrieve the total inc. Shipping Charges for the items in cart.
 *
 * @return [type] [description]
 */
function wpcm_get_cart_total() {
	$total = 0;

	if( $cart = wpcm_get_cart_content() ) {
		foreach( $cart as $crt ) {
			$total += ((float) webinane_set( $crt, 'price' ))*(webinane_set( $crt, 'quantity' ));
		}
	}

	return $total;
}

/**
 * Sends all the payment data to the specified gateway
 *
 * @since 1.0
 * @param string $gateway Name of the gateway
 * @param array $payment_data All the payment data to be sent to the gateway
 * @return void
*/
function wpcm_send_to_gateway( $gateway, $payment_data ) {

	$payment_data['gateway_nonce'] = wp_create_nonce( 'wpcm-gateway' );
	// $gateway must match the ID used when registering the gateway
	do_action( 'wpcm_send_to_gateway_' . esc_attr($gateway), $payment_data );
}

/**
 * Redirect to checkout page.
 *
 * @return [type] [description]
 */
function wpcm_redirect_back_checkout() {
	$settings = wpcm_options('wp_commerce_settings_display_options')->get_options();

	$checkout_page_id = webinane_set( $settings, 'checkout_page' );

	if( $checkout_page_id ) {
		$checkout_page = get_page($checkout_page_id);
		if( $checkout_page ) {
			wp_redirect( get_permalink($checkout_page->ID) );
			wp_die();
		}
	}

	wp_redirect( esc_url(home_url('/')), 302 );
	wp_die();
}

/**
 * Remove all the items in the cart and emtpy.
 *
 * @return void Returns nothing.
 */
function wpcm_empty_cart() {

	Session::set_session_data('cart', array());
}

/**
 * Add the metabox to the gobal configuration so render it accordingly.
 *
 * @param  [type] $args [description]
 * @return [type]       [description]
 */
function webinane_commerce_set_metabox($args) {
	$id = webinane_set( $args, 'id' );
	if( $id ) {
		/*$map = Metaboxes::instance()->make([$args]);
		$args = $map[0];*/

		Metaboxes::$config[$id] = $args;
	}

	return Metaboxes::$config;
}

/**
 * Add the metabox to the gobal configuration so render it accordingly.
 *
 * @param  [type] $args [description]
 * @return [type]       [description]
 */
function webinane_commerce_set_taxonomy_metabox($args) {
	$id = webinane_set( $args, 'id' );

	$taxonomies = array_get($args, 'taxonomies');

	if( $id ) {
		/*$map = Metaboxes::instance()->make([$args]);
		$args = $map[0];*/
		foreach($taxonomies as $taxonomy) {
			TaxonomyMetaboxes::$config[$taxonomy][$id] = $args;
		}
	}

	return TaxonomyMetaboxes::$config;
}

/**
 * [webinane_cm_price_with_symbol description]
 *
 * @param  [type] $price [description]
 * @return [type]        [description]
 */
function webinane_cm_price_with_symbol($price) {
	$settings = wpcm_get_settings();

	$symbol = webinane_currency_symbol();
	$position = $settings->get('currency_position', 'left');
	$sep = $settings->get('thousand_saparator', ''); // Thousand Separator
	$d_sep = $settings->get('decimal_separator', '.'); // Decimal separator
	$d_point = $settings->get('number_decimals', 0); // Decimal numbers

	$price = number_format((float)$price, (int)$d_point, $d_sep, $sep); // Aplly formation on number.

	if( $position == 'right' ) {
		return wp_kses_post($price . ' <i>' . $symbol . '</i>');
	} else {
		return wp_kses_post('<i>' . $symbol . '</i> ' . $price);
	}
}

/**
 * Returns currency symbol.
 *
 * @return [type] [description]
 */
function webinane_currency_symbol() {
	$currency = wpcm_get_settings()->get('base_currency', 'USD');
	$currencies = webinane_array(webinane_currencies());
	$symbol = $currencies->filter(function($value) use ($currency){
		return $value['iso']['code'] == $currency;
	})->first();

	return array_get($symbol, 'units.major.symbol', '$');
}

/**
 * [webinane_template description]
 *
 * @param  [type] $template [description]
 * @return [type]           [description]
 */
function webinane_template($template, $args = array(), $full_path = '') {

	$file = get_theme_file_path( $template );

	extract($args);

	if( file_exists($file) ) {
		include $file;
		return;
	}

	$file = get_theme_file_path('webinane-commerce/' . $template);
	if( file_exists($file) ) {
		include $file;
		return;
	}
	if(file_exists($full_path)) {
		include $full_path;
		return;
	}

	$file = WNCM_PATH . 'templates/' . $template;
	if( file_exists($file) ) {
		include $file;
	}
}

/**
 * Currency converter.
 *
 * @param  [type] $amount [description]
 * @param  [type] $to     [description]
 * @param  string $from   [description]
 * @return [type]         [description]
 */
function webinane_currency_convert($amount, $to, $from = '' ) {
	if( ! $from ) {
		$from = wpcm_get_settings()->get('base_currency', 'USD');
	}

	$response = wp_remote_get( 'http://rate-exchange-1.appspot.com/currency?from='.$from.'&to='.$to );

	$body = wp_remote_retrieve_body( $response );

	if( $body ) {
		$json = json_decode( $body, true );
		if( $rate = webinane_set( $json, 'rate') ) {
			return (float)$amount * (float)$rate;
		}
	}

	return $amount;
}

/**
 * [filesystem description]
 * @return [type] [description]
 */
function webinane_filesystem() {
	/* you can safely run request_filesystem_credentials() without any issues and don't need to worry about passing in a URL */
	if( ! function_exists('request_filesystem_credentials')) {
		require_once ABSPATH . 'wp-admin/includes/file.php';
	}

	ob_start();
	$creds = request_filesystem_credentials(site_url() . '/', '', false, false, array());
	ob_end_clean();
	/* initialize the API */
	if ( ! WP_Filesystem($creds) ) {
		/* any problems and we exit */
		return false;
	}

	global $wp_filesystem;
	/* do our file manipulations below */
	return $wp_filesystem;
}

if( ! function_exists('wpcm_order') ) {
	/**
	 * Order
	 *
	 * @param  WP_Query $order
	 * @return \WebinaneCommerce\Models\Order object
	 */
	function wpcm_order($order) {
		$order_id = is_array($order) ? array_get($order, 'ID') : $order->ID;
		return \WebinaneCommerce\Models\Order::find($order_id);
	}
}

if( ! function_exists('wpcm_order_model') ) {
	/**
	 * Order Model.
	 * 
	 * @param  \WebinaneCommerce\Models\Order $order
	 * @return \WebinaneCommerce\Models\Order
	 */
	function wpcm_order_model($order) {
		if( $order instanceof \WebinaneCommerce\Models\Order) {
			return $order;
		}
		return \WebinaneCommerce\Models\Order::find($order->ID);
	}
}

if( ! function_exists('wpcm_validate_nonce') ) {
	function wpcm_validate_nonce($nonce = null, $ajax = true) {

		$nonce = (! $nonce) ? sanitize_text_field( array_get($_REQUEST, 'nonce') ) : $nonce;

		$valid = wp_verify_nonce( $nonce, WPCM_GLOBAL_KEY );
		if(! $valid) {
			$message = esc_html__('Security check is failed, please refresh the page and try again', 'webinane-commerce');
			if($ajax) {
				wp_send_json_error(['message' => $message]);
			} else {
				return new WP_Error('error', $message);
			}
		}

		return true;
	}
}

if( ! function_exists('printr') ) {
	/**
	 * Print
	 * 
	 * @param  mix $var
	 * @return void
	 */
	function printr($var) {
		echo '<pre>';
		print_r($var);
		echo '</pre>';
		exit;
	}
}
