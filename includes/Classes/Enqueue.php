<?php
namespace WebinaneCommerce\Classes;

class Enqueue {

	static function init() {

		self::scripts();
		self::styles();
	}

	static function styles() {
		wp_enqueue_style( array('element-ui', 'wpcm-bootstrap', 'wpcommerce_main', 'wpcommerce_style', 'wpcommerce_responsive'));
	}

	static function scripts() {
		$trans = include WNCM_PATH . 'config/i18n.php';
		$version = (defined('WP_DEBUG') && WP_DEBUG ) ? time() : WPCM_VERSION;

		self::common();

		wp_register_script('bootstrap', WNCM_URL . 'assets/js/common/bootstrap.min.js', array('jquery'), $version, true);
		wp_register_script( 'wpcm-add-to-cart', WNCM_URL . 'assets/js/frontend/add-to-cart.js', array('jquery'), $version, true );

		wp_register_script('bootstrap-touchspin', WNCM_URL . 'assets/js/jquery.bootstrap-touchspin.min.js', 'jquery', $version, true);
		wp_register_script('wpcm-main-script', WNCM_URL . 'assets/js/common/script.js', 'jquery', $version, true);
		wp_register_script('wpcm-frontend-checkout', WNCM_URL . 'assets/js/frontend/checkout.js', array('jquery', 'wp-i18n'), $version, true);
		wp_register_script('wpcm-frontend-myaccount', WNCM_URL . 'assets/js/frontend/my-account.js', array('jquery', 'wpcm-components'), $version, true);



		wp_register_script( 'wpcm-admin-metaboxes', WNCM_URL . 'assets/js/admin/metaboxes.js', array('jquery', 'element-ui', 'wpcm-components'), $version, true );

		//wp_enqueue_media();
	}

	/**
	 * [common description]
	 *
	 * @return [type] [description]
	 */
	static function common() {
		$version = (defined('WP_DEBUG') && WP_DEBUG ) ? time() : WPCM_VERSION;
		$min = (defined('WP_DEBUG') && WP_DEBUG) ? '' : '.min';

		wp_register_script( 'popper', WNCM_URL . 'assets/js/common/popper.min.js', array(), $version, true );
		wp_register_script( 'bootstrap', WNCM_URL . 'assets/js/common/bootstrap.min.js', array('jquery', 'popper'), $version, true );
		wp_register_script( 'wpcommerce_export_order', WNCM_URL . 'assets/js/admin/export-order.js', array('jquery'), $version, true );
		wp_register_script( 'vuejs', WNCM_URL . 'assets/js/common/vue'.$min.'.js', array('jquery', 'underscore'), $version, false );
		wp_register_script('element-ui', WNCM_URL . 'assets/js/common/element-ui.min.js', array('jquery', 'vuejs'), $version, true);
		wp_register_script('element-ui-en', WNCM_URL.'assets/js/common/element-ui-en.js', array('jquery', 'vuejs', 'element-ui'), $version, true);

		wp_register_script( 'wpcm-components', WNCM_URL . 'assets/js/components.js', array('jquery', 'vuejs', 'element-ui-en'), $version, true );
		wp_enqueue_script( 'moment', WNCM_URL . 'assets/js/common/moment.min.js', array('jquery'), $version, true );

		wp_register_style( 'wpcm-bootstrap', WNCM_URL . 'assets/css/bootstrap.min.css', '', $version);


		/*
		 * Deprecated bootstrap-datepicker
		 * */

		// wp_register_style( 'bootstrap-datepicker', WNCM_URL . 'assets/css/bootstrap-datetimepicker.min.css', array('wpcm-bootstrap'), $version, true );
		wp_register_style( 'fontawesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.11.2/css/all.min.css', array(), $version );
		wp_register_style( 'element-ui', WNCM_URL.'assets/css/element-ui.css', array(), $version );

		wp_register_style( 'wpcommerce_main', WNCM_URL . 'assets/css/main.css', array(), $version );
		wp_register_style( 'wpcommerce_export_order', WNCM_URL . 'assets/css/export-order.css', array(), $version );
		wp_register_style( 'wpcommerce_style', WNCM_URL . 'assets/css/style.css', array(), $version );
		wp_register_style( 'wpcommerce_responsive', WNCM_URL . 'assets/css/responsive.css', array(), $version );

		$data = array(
			'ajaxurl'		=> admin_url( 'admin-ajax.php' ),
			'nonce'			=> wp_create_nonce( WPCM_GLOBAL_KEY ),
			'ajax_action'	=> WPCM_AJAX_ACTION,
		);
		wp_localize_script( 'jquery', 'wpcm_data', $data );
	}

	static function admin_enqueue() {
		$settings = wpcm_get_settings();

		$symbol = webinane_currency_symbol();
		$position = $settings->get('currency_position', 'left');
		$sep = $settings->get('thousand_saparator', ''); // Thousand Separator
		$d_sep = $settings->get('decimal_separator', '.'); // Decimal separator
		$d_point = $settings->get('number_decimals', 0); // Decimal numbers

		self::common();
		self::styles();
		$version = (defined('WP_DEBUG') && WP_DEBUG ) ? time() : WPCM_VERSION;

		$vars = compact('symbol', 'position', 'sep', 'd_sep', 'd_point');
		wp_register_script( 'wpcm-admin-metaboxes', WNCM_URL . 'assets/js/admin/metaboxes.js', array('jquery', 'element-ui', 'wpcm-components'), $version, true );
		wp_localize_script( 'wpcm-admin-metaboxes', 'wpcm_data', $vars );
	}

}
