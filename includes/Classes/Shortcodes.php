<?php
namespace WebinaneCommerce\Classes;

use WebinaneCommerce\Models\Order;

class Shortcodes {

	static function init() {

		add_shortcode('wpcm_checkout', array(__CLASS__, 'checkout') );
		add_shortcode('wpcm_my_account', array(__CLASS__, 'my_account') );
		add_shortcode('wpcm_order_success', array(__CLASS__, 'order_success') );
		add_shortcode('wpcm_add_to_cart_button', array(__CLASS__, 'add_to_cart_button') );

		add_action('wpcm_init', array( __CLASS__, 'register_fields' ) );
	}

	/**
	 * Checkout page shortcode.
	 *
	 * @param  array  $atts    [description]
	 * @param  string $content [description]
	 * @param  string $tag     [description]
	 * @return [type]          [description]
	 */
	static function checkout($atts, $content = null, $tag) {

		wp_enqueue_script( array( 'jquery', 'element-ui-en', 'underscore', 'bootstrap', 'bootstrap-touchspin', 'wpcm-main-script', 'vuejs', 'wpcm-frontend-checkout') );
		wp_enqueue_style(array('fontawesome'));
		ob_start();

		webinane_template('checkout/checkout.php', compact('atts', 'content'));

		return ob_get_clean();
	}

	/**
	 * My Account shortcode.
	 *
	 * @param  array  $atts    [description]
	 * @param  string $content [description]
	 * @param  string $tag     [description]
	 * @return [type]          [description]
	 */
	static function my_account($atts, $content = null, $tag) {

		wp_enqueue_script( array( 'wp-i18n', 'jquery', 'underscore', 'bootstrap', 'bootstrap-touchspin', 'wpcm-main-script', 'vuejs', 'element-ui-en', 'wpcm-frontend-myaccount') );
		wp_enqueue_style(array('fontawesome'));
		ob_start();

		webinane_template('my-account/my-account.php', compact('atts', 'content'));

		return ob_get_clean();
	}

	/**
	 * Add to cart button shortcode.
	 *
	 * @param [type] $atts    [description]
	 * @param [type] $content [description]
	 * @param [type] $tag     [description]
	 */
	static function add_to_cart_button($atts, $content = null, $tag) {
		extract(shortcode_atts( array(
			'item_id'		=> 0,
			'quantity'		=> 1,
			'price'			=> 0,
			'class'			=> 'btn btn-default',
			'enable_ajax'	=> true,
		), $atts, $tag ));

		wp_enqueue_script( array('wpcm-add-to-cart') );

		ob_start();

		webinane_template('global/add-to-cart-button.php', compact('item_id', 'quantity', 'price', 'class', 'enable_ajax', 'content'));

		return ob_get_clean();
	}

	static function order_success($atts, $content = null, $tag ) {

		wp_enqueue_style(array('fontawesome'));
		if( is_admin() ) {
			return;
		}
		
		$gateway = webinane_set($_REQUEST, 'gateway');

		if( $gateway ) {
			do_action('webinane_commerce/order_success/before/'.$gateway);
		}

		$key = esc_attr( webinane_set( $_GET, 'key' ) );
		$type = esc_attr( webinane_set( $_GET, 'type' ) );

		if( $type === 'success' || $type === 'failed' ) {
			ob_start();

			$order = Order::whereHas('meta', function($query) use ($key) {
				return $query->where(['meta_key' => '_wpcm_order_purchase_key', 'meta_value' => $key]);
			})->first();
			// $order = get_posts(array('posts_per_page' => 1, 'meta_key' => '_wpcm_order_purchase_key', 'meta_value' => $key, 'post_type' => 'orders', 'post_status' => 'any' ) );

			/*if( $order ) {
				$order = $order[0];
			}*/

			if( ! $order ) {
				return '';
			}
			$gateway = get_post_meta($order->ID, '_wpcm_order_gateway', true);

			$customer_id = get_post_meta($order->ID, '_wpcm_order_customer_id', true);
			
			if( $gateway ) {

				if($type === 'success') {
					webinane_template('orders/success-page.php', compact('key', 'order', 'atts', 'content'));
				}
				do_action("wpcm_order_successful_{$gateway}", $order, $key);
				$sent = get_post_meta( $order->ID, '_wpcm_notification_sent', true);
				if( ! $sent ) {
					do_action('wpcommerce_order_action_notification', $order, $customer_id);
					update_post_meta($order->ID, '_wpcm_notification_sent', true);
				}
			}
			
			return ob_get_clean();
		} else if( $type == 'notify') {
			$gateway = esc_attr( $_GET, 'gateway' );
			do_action("wpcm_order_webhooks_notification_{$gateway}");
		}

	}
	/**
	 * [register_fields description]
	 * @return [type] [description]
	 */
	static function register_fields() {

		$data = include WNCM_PATH . 'config/checkout_form.php';

		foreach( $data as $box ) {
			$cmbox = new_wpcm_box( $box );
		}
	}

	static function webhooks_response() {
		if( function_exists('wpcm_get_settings') ) {
			$success_page = wpcm_get_settings()->get( 'success_page' );

			if( is_page($success_page) && webinane_set($_GET, 'type') == 'notify' ) {
				$gateway = esc_attr( webinane_array($_GET)->get('gateway' ) );
				do_action("wpcm_order_webhooks_notification_{$gateway}");
				exit;
			}
		}
	}
}
