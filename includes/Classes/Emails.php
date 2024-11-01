<?php
namespace WebinaneCommerce\Classes;

use Konekt\PdfInvoice\InvoicePrinter;
use WebinaneCommerce\Classes\Customers;
use WebinaneCommerce\Helpers\Email;
use WebinaneCommerce\Models\Customer;

class Emails extends Email
{
	
	static $_instance;

	static function init() {

		//add_action('wpcm_order_successful', array(__CLASS__, 'order_emails'), 10, 2);
		add_action('wpcommerce_order_action_notification', array(__CLASS__, 'order_emails'), 10, 2);
		add_action('wpcommerce_order_action_email_invoice', array(__CLASS__, 'email_invoice'), 10, 2);

		// add_action('wpcm_owner_new_order_email', array(__CLASS__, 'owner_email_template'), 10, 2);
	}

	/**
	 * Main intance to handle dynamic functions
	 * 
	 * @return [type] [description]
	 */
	static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	
	/**
	 * Order email.
	 * 
	 * @param  [type] $order       [description]
	 * @param  [type] $customer_id [description]
	 * @return [type]              [description]
	 */
	static function order_emails($order, $customer_id) {

		self::send_customer_notif($order, $customer_id);
		self::send_owner_notif($order, $customer_id);
	}

	static function get_headers() {
		$name = get_bloginfo('name');
		$email = get_option('admin_email');
		return array(
			'From: '.$name.' <'.$email.'>',
			'Content-Type: text/html; charset=UTF-8',
		);
	}
	/**
	 * Send Notification to customer.
	 * 
	 * @param  [type] $payment_data [description]
	 * @param  [type] $order_id     [description]
	 * @return [type]               [description]
	 */
	static function send_customer_notif($order, $customer_id) {
		global $wpdb;

		ob_start();
		$file = 'emails/customer-new-order.php';
		$theme_template = get_theme_file_path( 'webinane-commerce/' . $file );

		if(file_exists($theme_template)) {
			include $theme_template;
		} else {
			include WNCM_PATH . 'templates/'.$file;
		}

		$customer = Customer::find($customer_id);
		$template = ob_get_clean();
		$template = apply_filters( 'wpcm_new_order_customer_email_template', $template, $order, $customer_id );

		$template = self::prepare_email_template($template, $order, $customer);

		if( $customer ) {

			$email = $customer->email;
			$settings = wpcm_get_settings();

			if( is_email( $email ) ) {
				$subject = sprintf( esc_html__( 'You order has been received - %s', 'webinane-commerce' ), get_bloginfo( 'name' ) );
				$subject = $settings->get('customer_email_subject') ? $settings->get('customer_email_subject') : $subject;
				$placeholders = self::placeholders($order, $customer);
				$subject = str_replace(array_keys($placeholders), array_values($placeholders), $subject);
				$subject = apply_filters( 'wpcm_new_order_customer_email_subject', $subject );

				$headers = self::get_headers();
				wp_mail( $email, $subject, $template, $headers );
			}
		}
	}

	/**
	 * Send notification to store owner.
	 *
	 * @param  [type] $payment_data [description]
	 * @param  [type] $order_id     [description]
	 * @return [type]               [description]
	 */
	static function send_owner_notif($order, $customer_id) {

		ob_start();

		$file = 'emails/owner-new-order.php';
		$theme_template = get_theme_file_path( 'webinane-commerce/' . $file );

		if(file_exists($theme_template)) {
			include $theme_template;
		} else {
			include WNCM_PATH . 'templates/'.$file;
		}

		$template = ob_get_clean();
		$customer = Customer::find($customer_id);

		$template = apply_filters( 'wpcm_new_order_owner_email_template', $template, $order, $customer_id );
		$template = self::prepare_email_template($template, $order, $customer);

		$email = get_bloginfo( 'admin_email' );

		if( is_email( $email ) ) {
			$settings = wpcm_get_settings();
			$subject = sprintf( esc_html__( 'New order is received - %s', 'webinane-commerce' ), get_bloginfo( 'name' ) );
			$subject = $settings->get('admin_email_subject') ? $settings->get('admin_email_subject') : $subject;
			$placeholders = self::placeholders($order, $customer);
			$subject = str_replace(array_keys($placeholders), array_values($placeholders), $subject);
			$subject = apply_filters( 'wpcm_new_order_owner_email_subject', $subject );

			$headers = self::get_headers();
			wp_mail( $email, $subject, $template, $headers );
		}
	}

	/**
	 * Email invoice
	 * @param  [type] $order       [description]
	 * @param  [type] $customer_id [description]
	 * @return [type]              [description]
	 */
	static function email_invoice($order, $customer_id) {

		$headers = self::get_headers();

		$customer = new Customers($customer_id);
		$customer_data = $customer->full_customer_info();

		ob_start();

		$file = 'emails/customer-order-invoice.php';
		$theme_template = get_theme_file_path( 'webinane-commerce/' . $file );

		if(file_exists($theme_template)) {
			include $theme_template;
		} else {
			include WNCM_PATH . 'templates/'.$file;
		}

		$template = ob_get_clean();

		if( is_email( $customer_data->email ) ) {

			$attachment = self::generate_invoice($order, $customer_data);

			$subject = apply_filters( 'webinane_commerce_customer_invoice_email_subject', sprintf( esc_html__( 'Invoice for order # %s - %s', 'webinane-commerce' ), $order['ID'], get_bloginfo('name') ), $order );

			wp_mail( $customer_data->email, $subject, $template, $headers, [$attachment] );
			webinane_filesystem()->delete($attachment);
		}
	}

	/**
	 * Generate PDF invoice
	 * @param  [type] $odr           [description]
	 * @param  [type] $customer_data [description]
	 * @return [type]                [description]
	 */
	static function generate_invoice($odr, $customer_data) {
		$order = get_post($odr['ID']);
		$settings = wpcm_get_settings();
		$custom_id = get_post_meta( $order->ID, '_wpcm_order_customer_id', true);


		$invoice = new InvoicePrinter('A4', webinane_currency_symbol());
  		
  		$custom_logo_id = get_theme_mod( 'custom_logo' );
  		if( $custom_logo_id ) {
  			$logo = wp_get_attachment_url( $custom_logo_id );
  		} else {
  			$logo = WNCM_URL . "assets/images/logo-placehold.png";
  		}

  		$logo = apply_filters( 'webinane_commerce_store_logo',  $logo);

		/* Header settings */
		$invoice->setLogo($logo);   //logo image path
		$invoice->setColor("#007fff");      // pdf color scheme
		$invoice->setType("Donation Invoice");    // Invoice Type
		$invoice->setReference("INV-55033".$order->ID);   // Reference
		$invoice->setDate(date('M dS ,Y',strtotime($order->post_date)));   //Billing Date
		$invoice->setTime(date('h:i:s A',time()));   //Billing Time
		$invoice->setDue(date('M dS ,Y',strtotime($order->post_date)));    // Due Date
		$invoice->setFrom(array(get_bloginfo('name'),get_bloginfo('description'), $settings->get('address_line_1'),$settings->get('city') . ', ' . $settings->get('zip')));
		$invoice->setTo(array($order->post_title, $customer_data->meta['billing_company'], $customer_data->meta['billing_address_line_1'], $customer_data->meta['billing_city'].','.$customer_data->meta['billing_zip']));
		$invoice->setNumberFormat($settings->get('thousand_separator', ','), $settings->get('decimal_separator', '.'));

		$order_data = WebinaneCommerce\Orders::order_data($order);

		$total = 0;
		foreach( $order_data->order_items as $item ) {
			$amount = (int)$item->qty*(int)$item->price;
			$total += $amount;

			$invoice->addItem($item->order_item_name, "", $item->qty,0,$item->price,0, $amount);
		}

		$invoice->addTotal(esc_html__("Total", 'webinane-commerce' ),$total);
		$invoice->addTotal(esc_html__("VAT", 'webinane-commerce' ), 0);
		$invoice->addTotal(esc_html__("Total due", 'webinane-commerce' ),$total,true);

		$statuses = array(
	    	'pending_payment'			=> esc_html__( 'Pending Payment', 'webinane-commerce' ),
	    	'processing'				=> esc_html__( 'Processing', 'webinane-commerce' ),
	    	'hold'						=> esc_html__( 'On Hold', 'webinane-commerce' ),
	    	'completed'					=> esc_html__( 'Completed', 'webinane-commerce' ),
	    	'cancelled'					=> esc_html__( 'Cancelled', 'webinane-commerce' ),
	    	'refunded'					=> esc_html__( 'Refunded', 'webinane-commerce' ),
	    	'failed'					=> esc_html__( 'Failed', 'webinane-commerce' ),
	    );
		$invoice->addBadge(webinane_set($statuses, $order->post_status));

		$invoice->addTitle(esc_html__("Important Notice", 'webinane-commerce' ));

		$invoice->addParagraph("No item will be replaced or refunded if you don't have the invoice with you.");

		$invoice->setFooternote(get_bloginfo('name'));

		$uploads = wp_get_upload_dir();
		$basedir = $uploads['basedir'] . '/wp-commerce';

		webinane_filesystem()->mkdir($basedir);
		$file = $basedir . '/' . md5(time()) .'.pdf';
		$invoice->render($file,'F'); 
		/* I => Display on browser, D => Force Download, F => local path save, S => return document as string */

		return $file;
	}

	/**
	 * Load email template for owner on new order placement.
	 * 
	 * @param  [type] $order [description]
	 * @return [type]        [description]
	 */
	static function owner_email_template($order, $customer_id) {
		webinane_template('emails/layouts/template.php', compact('order', 'customer_id'));
	}

	static function prepare_email_template($template, $order, $customer, $for = 'customer') {
		$settings = wpcm_get_settings();
		$placeholders = self::placeholders($order, $customer);
		$email_body = str_replace("\n", '', $settings->get($for.'_email_body'));
		$footer_text = str_replace("\n", '', $settings->get($for.'_email_footer_text'));
		$placeholders = array_merge($placeholders, [
			'{{email_body}}'	=> self::values_from_settings($placeholders, $email_body),
			'{{footer_text}}'	=> self::values_from_settings($placeholders, $footer_text),
		]);

		$template = str_replace(
			array_keys($placeholders),
			array_values($placeholders), 
			$template
		);
		return $template;
	}

	/**
	 * Email Body.
	 * @param  [type] $placeholders [description]
	 * @return [type]               [description]
	 */
	private static function values_from_settings($placeholders, $data) {

		return str_replace(
			array_keys($placeholders),
			array_values($placeholders), 
			$data
		);
	}
	/**
	 * Placeholders
	 * 
	 * @param  [type] $order    [description]
	 * @param  [type] $customer [description]
	 * @return [type]           [description]
	 */
	private static function placeholders($order, $customer) {
		$settings = wpcm_get_settings();

		$orders_label = apply_filters('wpcm_orders_admin_menu_label', esc_html__('Orders', 'webinane-commerce'));
		$order_label = apply_filters('wpcm_order_admin_menu_label', esc_html__('Order', 'webinane-commerce'));
		$price_label = apply_filters('wpcm_price_label', esc_html__('Price', 'webinane-commerce'));
		$placeholders = [
			'{{header_logo}}'	=> wp_get_attachment_url( $settings->get('customer_email_header_logo') ),
			'{{footer_logo}}'	=> wp_get_attachment_url( $settings->get('customer_email_footer_logo') ),
			'{{customer_name}}'	=> $customer->full_name,
			'{{customer_email}}'	=> $customer->email,
			'{{customer_address}}'	=> $customer->address,
			'{{customer_city}}'	=> $customer->city,
			'{{customer_state}}'	=> $customer->state,
			'{{customer_country}}'	=> $customer->country,
			'{{site_name}}'		=> esc_attr( get_bloginfo('name') ),
			'{{site_url}}'		=> esc_url( home_url( '/' ) ),
			'{{admin_email}}'	=> esc_attr( get_bloginfo('admin_email') ),
			'{{customer_account_url}}'	=> esc_url( get_permalink( $settings->get('my_account_page') ) ),
			'{{admin_order_url}}'		=> esc_url( admin_url('post.php?post='.$order->ID.'&action=edit') ),
			'{{total_amount}}'	=> $order->formatted_price,
			'{{orders_label}}'	=> $orders_label,
			'{{order_label}}'	=> $order_label,
			'{{price_label}}'	=> $price_label,
		];

		return $placeholders;
	}
}
