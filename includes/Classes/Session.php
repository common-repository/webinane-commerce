<?php
namespace WebinaneCommerce\Classes;

use WebinaneCommerce\Helpers\Cookie;

class Session {
	static $session_key = 'wpmc_global_session';
	static $db_table = 'wpcommerce_sessions';

	/**
	 * Run the session.
	 * @return [type] [description]
	 */
	static function run() {
		global $pagenow;

		self::get_session_key();
	}

	/**
	 * Get the session key.
	 *
	 * @return [type] [description]
	 */
	static function get_session_key() {

		$cookie = webinane_set($_COOKIE, 'jst_'.self::$session_key);
		if ( is_array($cookie) || !$cookie ) {

			$key = md5( uniqid() );
			
			if( ! headers_sent() ) {
				Cookie::set(self::$session_key, $key);
			}
		} else {
			$key = webinane_set($_COOKIE, 'jst_'.self::$session_key);
		}

		return $key;
	}

	/**
	 * get current url.
	 * 
	 * @return [type] [description]
	 */
	static function get_current_url() {
		$host = $_SERVER['HTTP_HOST'];
		$request = $_SERVER['REQUEST_URI'];
		$url = esc_url($host.$request);

		return ($url) ? $url : esc_url(home_url('/'));
	}

	/**
	 * Get the funnel data from session.
	 *
	 * @return [type] [description]
	 */
	static function get_session_db_data() {

		if ( $key = self::get_session_key() ) {
			if($res = self::query_db() ) {
				return $res;
			} else {
				self::create_new_empty_db_row();
				return self::query_db();
			}
		}

		return false;
	}

	/**
	 * Get session data from globals.
	 *
	 * @return [type] [description]
	 */
	static function get_session_data() {
		$data = self::get_session_db_data();
		if( $data ) {
			return json_decode( $data->session_value, true );
		}
		return [];
	}

	/**
	 * Get the session data of specific key.
	 *
	 * @param  [type] $key [description]
	 * @return [type]      [description]
	 */
	static function get_session_data_by_key($key) {

		$data = self::get_session_data();

		return webinane_set( $data, $key );
	}

	/**
	 * Set the provided session data.
	 * 
	 * @param array $data [description]
	 */
	static function set_session_data( $key, $data = array() ) {
		
		$session = self::get_session_data();

		$session[ $key ] = $data;
		$db_data = self::get_session_db_data();
		$db_data->session_value = wp_json_encode( $session );

		self::update_session_db($db_data);
		return true;

	}

	/**
	 * [update_session_db description]
	 * @return [type] [description]
	 */
	static function update_session_db($data = array() ) {
		global $wpdb;
		
		if ( false ) {
			$data->session_expiry = date("Y-m-d H:i:s", strtotime('+48 hours'));
			unset($data->session_id);
			$new_id = $wpdb->insert(
				$wpdb->prefix.self::$db_table,
				$data
			);

		} else {
			$wpdb->update(
				$wpdb->prefix.self::$db_table,
				(array) $data,
				array('session_id' => $data->session_id )
			);
		}
	}

	/**
	 * [query_db description]
	 * @return [type] [description]
	 */
	static protected function query_db() {

		global $wpdb;

		$session_key = self::get_session_key();
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.self::$db_table." WHERE session_key = %s", $session_key ) );
	}

	/**
	 * [create_new_empty_db_row description]
	 * @return [type] [description]
	 */
	static protected function create_new_empty_db_row() {
		global $wpdb;

		$session_key = self::get_session_key();

		$wpdb->insert(
			$wpdb->prefix . self::$db_table,
			array('session_key' => $session_key, 'session_value' => json_encode( array() ), 'session_expiry' => date("Y-m-d H:i:s", strtotime('+48 hours')) )
		);

		return true;
	}
}
