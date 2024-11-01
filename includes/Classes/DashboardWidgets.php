<?php
namespace WebinaneCommerce\Classes;

use WebinaneCommerce\Classes\Orders;

class DashboardWidgets
{

	static function init() {
		// Register the new dashboard widget with the 'wp_dashboard_setup' action
		add_action('wp_dashboard_setup', array(__CLASS__, 'add') );
		add_action( 'admin_enqueue_scripts', array(__CLASS__, 'admin_enqueue_scripts') );
	}

	// Function that outputs the contents of the dashboard widget
	static function widget( $post, $callback_args ) {
		$stats = Orders::orders_status_this_month();
		webinane_template('admin/stats-dashboard-widget.php', compact('stats'));
	}

	// Function used in the action hook
	static function add() {
		$title = apply_filters( 'wpcommerce_dashboard_widget_stats_title', esc_html__('WP Commerce Status', 'webinane-commerce' ) );
		wp_add_dashboard_widget('dashboard_widget', $title, array(__CLASS__, 'widget'));
	}

	// Function used in the action hook and enqueue style
	static function admin_enqueue_scripts() {
		wp_enqueue_style('wpcommerce_dashboard_css', WNCM_URL . 'assets/css/dashboard-widget.css');
	}
}
