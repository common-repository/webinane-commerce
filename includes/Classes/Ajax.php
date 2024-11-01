<?php
namespace WebinaneCommerce\Classes;

use WebinaneCommerce\Admin\Api;

class Ajax 
{


	static function init() {
		$_post = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
		$subction = esc_attr( webinane_set( $_post, 'subaction' ) );

		$subction = apply_filters( 'webinane_commerce_ajax_subaction', $subction );

		if ( $subction ) {
			if( method_exists(__CLASS__, $subction) ) {
				call_user_func(array(__CLASS__, $subction));
			}
			exit;
		}

		$callback = webinane_set( $_post, 'callback' );
		$callback = apply_filters( 'webinane_commerce_ajax_callback', $callback );

		if ( $callback ) {
			if( is_array($callback) && !is_callable($callback)) {
				$callback[0] = stripslashes($callback[0]);
			}
		
			if( is_callable($callback) ) {
				call_user_func($callback);
			}
			exit;
		}

		exit;
	}

	/**
	 * Ajax add to cart process.
	 *
	 * @return [type] [description]
	 */
	static function ajax_add_to_cart() {
		$_post = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
		$nonce = esc_attr( webinane_set($_post, 'nonce') );

		if ( ! wp_verify_nonce( $nonce, WPCM_GLOBAL_KEY )) {
			wp_send_json( array('success' => false, 'message' => esc_html__( 'Security verification failed', 'webinane-commerce' ) ), 403 );
		}

		$id = esc_attr( webinane_set( $_post, 'item_id' ) );
		$qty = esc_attr( webinane_set( $_post, 'quantity', 1 ) );
		$price = esc_attr( webinane_set( $_post, 'price', 0 ) );

		if ( ! $id ) {
			wp_send_json( array('success' => false, 'message' => esc_html__( 'Item id is not provided', 'webinane-commerce' ) ), 403 );
		}

		$res = wpcm_add_to_cart(array(
			'item_id'		=> $id,
			'quantity'		=> $qty,
			'price'			=> $price,
		));

		if ( ! $res ) {
			wp_send_json( array('success' => false, 'message' => $res ), 403 );
		}

		if( wpcm_get_settings()->get('redirect_to_checkout') ) {
			$url = get_permalink(wpcm_get_settings()->get('checkout_page'));
			if( $url ) {
				wp_send_json( array('success' => true, 'message' => esc_html__( 'Item added to cart', 'webinane-commerce' ), 'redirect' => esc_url($url) ), 200 );
			}
		}
		wp_send_json( array('success' => true, 'message' => esc_html__( 'Item added to cart', 'webinane-commerce' ) ), 200 );
		
	}

	/**
	 * [checkout description]
	 * @return [type] [description]
	 */
	static function checkout() {

		$nonce = esc_attr( webinane_set($_POST, 'nonce' ) );
		if( ! wp_verify_nonce( $nonce, WPCM_GLOBAL_KEY ) ) {
			wp_send_json( array('success' => false, 'message' => esc_html__( 'Security check failed, try again after refresh the page', 403, 'webinane-commerce' )) );
		}

		do_action('wpcm_process_checkout');
	}

	/**
	 * [update_cart_items description]
	 *
	 * @return [type] [description]
	 */
	static function update_cart_items() {
		$nonce = esc_attr( webinane_set($_POST, 'nonce' ) );
		if( ! wp_verify_nonce( $nonce, WPCM_GLOBAL_KEY ) ) {
			wp_send_json( array('success' => false, 'message' => esc_html__( 'Security check failed, try again after refresh the page', 403, 'webinane-commerce' )) );
		}
		$_post = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
		$cart = wpcm_get_cart_content();

		$items = webinane_set( $_post, 'items');
		foreach( $cart as $index => $c ) {
			$item = webinane_set( $items, $c['item_id']);
			if( $item ) {
				$cart[$index]['quantity'] = $item['qty'];
			}
		}

		Session::set_session_data('cart', $cart);

		wp_send_json_success( array('items' => $items) );
	}

	/**
	 * [get_active_plugins description]
	 *
	 * @return [type] [description]
	 */
	static function get_active_plugins(){
		$plug_array = get_option('active_plugins');
		$_plug_array = [];
		foreach($plug_array as $plug){
			$_plug_arra[]=dirname($plug);
		}
		wp_send_json_success($_plug_arra);
	}

	/**
	 * [webinanecom_athorized description]
	 *
	 * @return [type] [description]
	 */
	static function webinanecom_athorized(){

		$token = Api::get_token();

		if($token){
			wp_send_json_success(['token'=> $token]);
		}

		wp_send_json_error(['auth_url'=> Api::get_auth_url()]);
		
	}
}
