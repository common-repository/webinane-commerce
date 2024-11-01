<?php
namespace WebinaneCommerce\Admin;

class Install
{

	static function init($network_wide = false) {
		global $wpdb;
		if ( is_multisite() && $network_wide ) {
	        // Get all blogs in the network and activate plugin on each one
	        $blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
	        foreach ( $blog_ids as $blog_id ) {
	            switch_to_blog( $blog_id );
				self::install();
	            restore_current_blog();
	        }
	    } else {
			self::install();
	    }
	}

	static function install() {

		if ( ! is_blog_installed() ) {
			return;
		}

		// If we made it till here nothing is running yet, lets set the transient now.
		defined( 'WPCM_INSTALLING' ) || define( 'WPCM_INSTALLING', true );

		self::create_tables();
    	// self::create_pages();

    	// add_action('wp', array(__CLASS__, 'create_pages'));
    	self::update_default_settings();
	}

	/**
	 * Create default pages.
	 *
	 * @return [type] [description]
	 */
	static function create_pages() {

		$shortcodes = array('[wpcm_checkout]', '[wpcm_my_account]', '[wpcm_order_success]');

		foreach( $shortcodes as $shortcode) {
			$query = new \WP_Query(array('post_type' => 'page', 's' => $shortcode, 'post_status' => 'any'));
			if( ! $query->have_posts() ) {
				$title = ucwords(str_replace(array('wpcm_', '_', '[', ']'), array('', ' ', '', ''), $shortcode));
				$post_data = array(
					'post_title'	=> $title,
					'post_content'	=> $shortcode,
					'post_status'	=> 'publish',
					'post_type'		=> 'page'
				);
				$post_id = wp_insert_post( $post_data, true );

				if( ! is_wp_error( $post_id ) ) {
					$settings = (array)get_option(WPCM_SETTINGS_KEY);
					$key = str_replace(array('[', ']', 'wpcm_', 'order_'), '', $shortcode) . '_page';
					$settings[$key] = $post_id;
					update_option( WPCM_SETTINGS_KEY, $settings );
				}
			}
			wp_reset_postdata();
		}

		delete_transient( '_webinane_commerce_create_default_pages' );
	}
	/**
	 * Update default settings.
	 *
	 * @return [type] [description]
	 */
	static function update_default_settings() {

		delete_transient( '_webinane_commerce_create_default_pages' );
		set_transient( '_webinane_commerce_create_default_pages', 1, 60*60*24 );
		add_action('init', function(){
			$opts = wpcm_get_settings(false, true);
			if( $opts ) {
				update_option(WPCM_SETTINGS_KEY, $opts);
			}
		});
		$wpcm_user_data = get_option('wpcm_user_data');

		$default = [
			'update' => [
				'updated' => false,
				'object'  => '',
				'cron'=> [
					'jobs'=> [
						'callback'=>[],
						'status'
					],
					'status'=> ''
				]
			],
			'athorization'=>[
				'token'=>'',
				'expiration'=>'',
				'refresh'=>'',
			]
		];

		update_option('wpcm_user_data', array_merge($default, (array)$wpcm_user_data));

	}

	/**
	 * Create the required tables.
	 *
	 * @return void This method returns nothing.
	 */
	private static function create_tables() {
		global $wpdb;

		$wpdb->hide_errors();

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		/**
		 * Before installing tables make sure they are not already exists.
		 */
		if ( $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->prefix}wpcommerce_sessions';" ) ) {
			return;
		}

		dbDelta( self::get_schema() );
	}

	/**
	 * Get Table schema.
	 *
	 * check whether tables already exists.
	 *
	 * @return string
	 */
	private static function get_schema() {
		global $wpdb;

		$collate = '';

		if ( $wpdb->has_cap( 'collation' ) ) {
			$collate = $wpdb->get_charset_collate();
		}

		$tables = "
			CREATE TABLE IF NOT EXISTS {$wpdb->prefix}wpcommerce_sessions (
			session_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			session_key char(32) NOT NULL,
			session_value longtext NOT NULL,
			session_expiry datetime DEFAULT NULL,
			PRIMARY KEY  (session_id),
			UNIQUE KEY session_key (session_key)
			) $collate;

			CREATE TABLE {$wpdb->prefix}wpcommerce_order_items (
			order_item_id BIGINT UNSIGNED NOT NULL auto_increment,
			order_item_name TEXT NOT NULL,
			`qty` int(11) DEFAULT '1',
			`price` float DEFAULT '0',
			`base_price` float DEFAULT '0',
			`currency` varchar(3) DEFAULT 'USD',
			order_item_type varchar(200) NOT NULL DEFAULT '',
			order_id BIGINT UNSIGNED NOT NULL,
			post_id BIGINT UNSIGNED NOT NULL,
			PRIMARY KEY  (order_item_id),
			KEY order_id (order_id)
			) $collate;

			CREATE TABLE {$wpdb->prefix}wpcommerce_payment_tokens (
			token_id BIGINT UNSIGNED NOT NULL auto_increment,
			gateway_id varchar(200) NOT NULL,
			token text NOT NULL,
			user_id BIGINT UNSIGNED NOT NULL DEFAULT '0',
			type varchar(200) NOT NULL,
			is_default tinyint(1) NOT NULL DEFAULT '0',
			PRIMARY KEY  (token_id),
			KEY user_id (user_id)
			) $collate;
			CREATE TABLE {$wpdb->prefix}wpcommerce_payment_tokenmeta (
			meta_id BIGINT UNSIGNED NOT NULL auto_increment,
			payment_token_id BIGINT UNSIGNED NOT NULL,
			meta_key varchar(255) NULL,
			meta_value longtext NULL,
			PRIMARY KEY  (meta_id),
			KEY payment_token_id (payment_token_id),
			KEY meta_key (meta_key(32))
			) $collate;
			CREATE TABLE {$wpdb->prefix}wpcommerce_log (
			log_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			timestamp datetime NOT NULL,
			level smallint(4) NOT NULL,
			source varchar(200) NOT NULL,
			message longtext NOT NULL,
			context longtext NULL,
			PRIMARY KEY (log_id),
			KEY level (level)
			) $collate;
			CREATE TABLE {$wpdb->prefix}wpcommerce_customers (
			`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			`user_id` bigint(20) NOT NULL,
			`email` varchar(255) NOT NULL,
			`name` mediumtext NOT NULL,
			`created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			UNIQUE KEY id (id)
			) $collate;
			CREATE TABLE {$wpdb->prefix}wpcommerce_customer_meta (
			`meta_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			`customer_id` bigint(20) NOT NULL,
			`meta_key` varchar(255) NOT NULL,
			`meta_value` longtext DEFAULT NULL,
			PRIMARY KEY  (meta_id),
			UNIQUE KEY meta_id (meta_id)
			) $collate;
		";

		return $tables;
	}
}
