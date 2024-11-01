<?php
namespace WebinaneCommerce\Gateways;

use PayPal\CoreComponentTypes\BasicAmountType;
use PayPal\EBLBaseComponents\ActivationDetailsType;
use PayPal\EBLBaseComponents\AddressType;
use PayPal\EBLBaseComponents\BillOutstandingAmountRequestDetailsType;
use PayPal\EBLBaseComponents\BillingAgreementDetailsType;
use PayPal\EBLBaseComponents\BillingPeriodDetailsType;
use PayPal\EBLBaseComponents\CreateRecurringPaymentsProfileRequestDetailsType;
use PayPal\EBLBaseComponents\CreditCardDetailsType;
use PayPal\EBLBaseComponents\DoExpressCheckoutPaymentRequestDetailsType;
use PayPal\EBLBaseComponents\PaymentDetailsItemType;
use PayPal\EBLBaseComponents\PaymentDetailsType;
use PayPal\EBLBaseComponents\RecurringPaymentsProfileDetailsType;
use PayPal\EBLBaseComponents\ScheduleDetailsType;
use PayPal\EBLBaseComponents\SetExpressCheckoutRequestDetailsType;
use PayPal\IPN\PPIPNMessage;
use PayPal\PayPalAPI\BillOutstandingAmountReq;
use PayPal\PayPalAPI\BillOutstandingAmountRequestType;
use PayPal\PayPalAPI\CreateRecurringPaymentsProfileReq;
use PayPal\PayPalAPI\CreateRecurringPaymentsProfileRequestType;
use PayPal\PayPalAPI\DoExpressCheckoutPaymentReq;
use PayPal\PayPalAPI\DoExpressCheckoutPaymentRequestType;
use PayPal\PayPalAPI\GetExpressCheckoutDetailsReq;
use PayPal\PayPalAPI\GetExpressCheckoutDetailsRequestType;
use PayPal\PayPalAPI\GetRecurringPaymentsProfileDetailsReq;
use PayPal\PayPalAPI\GetRecurringPaymentsProfileDetailsRequestType;
use PayPal\PayPalAPI\GetTransactionDetailsReq;
use PayPal\PayPalAPI\GetTransactionDetailsRequestType;
use PayPal\PayPalAPI\RefundTransactionReq;
use PayPal\PayPalAPI\RefundTransactionRequestType;
use PayPal\PayPalAPI\SetExpressCheckoutReq;
use PayPal\PayPalAPI\SetExpressCheckoutRequestType;
use PayPal\Service\PayPalAPIInterfaceServiceService;
use WebinaneCommerce\Classes\Gateways;
use WebinaneCommerce\Classes\Orders;
use WebinaneCommerce\Fields\Text;


class GatewayPaypal extends Gateways
{
	static $_instance;
	public $name = '';
	public $id = 'paypal';
	const ID = 'paypal';
	public $desc = '';
	public $is_refundable = true;
	public $recurring = true;
	public $title = '';
	public $icon = '';
	static function init() {
		self::setup();
		add_filter('wpcommerce_payment_gateways', array(__CLASS__, 'gateway'));
		add_filter('wpcommerce_payment_gateways_setting_tabs', array(__CLASS__, 'settings'));
		add_action('wpcm_send_to_gateway_paypal', array(__CLASS__, 'run') );
		add_action('wpcm_order_successful_paypal', array(__CLASS__, 'capture_payment'), 10, 2 );
		add_action( 'wpcm_order_webhooks_notification_paypal', array(__CLASS__, 'IPN_capture'));
		add_action( 'webinane_commerce_process_refund_paypal', array(__CLASS__, 'process_refund'));

		add_filter('webinane_commerce/admin/paypal/meta', array(__CLASS__, 'admin_meta'), 20, 2);
	}
	static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	static function setup() {
		$gateways = array_get(wpcm_get_settings(), 'gateways.paypal_express_gateway');
		$gateways = ($gateways) ? $gateways : wpcm_get_settings();

		self::instance()->name = esc_html__( 'PayPal Standard', 'webinane-commerce' );
		self::instance()->desc = esc_html__( 'Pay using PayPal', 'webinane-commerce' );
		self::instance()->title = array_get($gateways, 'paypal_title');
		self::instance()->description = array_get($gateways, 'paypal_description');
		self::instance()->icon = WNCM_URL . 'assets/images/paypal-icon.png';
	}
	static function gateway($gateways = array()) {
		$gateways['paypal'] = self::instance();
		return $gateways;
	}
	/**
	 * Hook up gateway settings to WP Commerce gateway settings tab.
	 *
	 * @param  array  $settings [description]
	 * @return [type]           [description]
	 */
	static function settings($settings = array()) {
		$success_page = wpcm_get_settings()->get( 'success_page' );
		$url = add_query_arg(array('type' => 'notify', 'gateway' => 'paypal'), get_permalink($success_page) );

		$paypal_gateway = array_get(wpcm_get_settings(), 'gateways.paypal_express_gateway');

		$settings[] = array(
			'title'			=> esc_html__( 'PayPal Standard', 'webinane-commerce' ),
			'icon'			=> 'fa fa-th',
			'id'			=> 'paypal_express_gateway',
			'heading'	=> esc_html__('PayPal Standard', 'webinane-commerce'),
			"sandbox_help" => esc_html__('Enable sandbox mode to do test payments', 'webinane-commerce'),
			"has_sandbox"	=> true,
			'fields'		=> array(
				Text::make(esc_html__( 'Title', 'webinane-commerce' ), 'paypal_title', function() use ($paypal_gateway) {
					if($value = array_get(wpcm_get_settings(), 'paypal_title')) {
						return $value;
					}
					return array_get($paypal_gateway, 'paypal_title');
				})->setHelp(esc_html__( 'Title to show on the payment page', 'webinane-commerce' )),
				Text::make(esc_html__( 'Description', 'webinane-commerce' ), 'paypal_description', function() use ($paypal_gateway) {
					if($value = array_get(wpcm_get_settings(), 'paypal_description')) {
						return $value;
					}
					return array_get($paypal_gateway, 'paypal_description');
				})->setHelp(esc_html__( 'Description to show on the payment page', 'webinane-commerce' )),
				Text::make(esc_html__( 'PayPal Email', 'webinane-commerce' ), 'paypal_email', function() use ($paypal_gateway) {
					if($value = array_get(wpcm_get_settings(), 'paypal_email')) {
						return $value;
					}
					return array_get($paypal_gateway, 'paypal_email');
				})->setHelp(esc_html__( 'Please enter your PayPal email, it is need to take the orders', 'webinane-commerce' )),

				// API Username
				Text::make(esc_html__( 'API Username', 'webinane-commerce' ), 'paypal_api_username', function() use ($paypal_gateway) {
					if($value = array_get(wpcm_get_settings(), 'paypal_api_username')) {
						return $value;
					}
					return array_get($paypal_gateway, 'paypal_api_username');
				})->setHelp(__('Please enter your PayPal live API username, Learn how to access <a href="https://developer.paypal.com/webapps/developer/docs/classic/api/apiCredentials/#create-an-api-signature" target="_blank">PayPal API Credentials</a>', 'webinane-commerce')),
				// API Password
				Text::make(esc_html__( 'API Password', 'webinane-commerce' ), 'paypal_api_password', function() use ($paypal_gateway) {
					if($value = array_get(wpcm_get_settings(), 'paypal_api_password')) {
						return $value;
					}
					return array_get($paypal_gateway, 'paypal_api_password');
				})->setHelp(esc_html__('Please enter your PayPal live API password', 'webinane-commerce')),

				// Api Signature
				Text::make(esc_html__( 'API Signature', 'webinane-commerce' ), 'paypal_api_signature', function() use ($paypal_gateway) {
					if($value = array_get(wpcm_get_settings(), 'paypal_api_signature')) {
						return $value;
					}
					return array_get($paypal_gateway, 'paypal_api_signature');
				})->setHelp(sprintf(esc_html__( 'Please enter your PayPal live API signature, the IPN listening URL should be %s', 'webinane-commerce' ), $url ) ),

			)
		);
		return $settings;
	}
	/**
	 * Check form database options that gateway is enabled or not.
	 * 
	 * @return boolean [description]
	 */
	function is_active() {
		$settings = wpcm_get_settings();

		if ( isset($settings['active_gateways'] ) ) {
			$status = in_array('paypal', $settings['active_gateways']);
		} else {
			$status = wpcm_get_settings()->get('paypal_express_gateway');
		}

		if( ! $status ) {
			return false;
		}
		return ($status === 'false') ? false : true;
	}
	/**
	 * PayPal API Credentials configurations to call the API.
	 *
	 * @return [type] [description]
	 */
	static function config() {
		$settings = wpcm_get_settings();
		$paypal_express = array_get($settings, 'gateways.paypal_express_gateway');
		$paypal_express = is_array($paypal_express) ? $paypal_express : $settings;
		$mode = array_get($paypal_express, 'sandbox') ? 'sandbox' : 'live';
		$config = array(
			'mode' => 	$mode,
			// Signature Credential
			"acct1.UserName" => array_get($paypal_express, 'paypal_api_username'),
			"acct1.Password" => array_get($paypal_express, 'paypal_api_password'),
			"acct1.Signature" => array_get($paypal_express, 'paypal_api_signature'),
		);
		return $config;
	}
	/**
	 * [run description]
	 *
	 * @param  [type] $payment_data [description]
	 * @return [type]               [description]
	 */
	static function run($payment_data) {
		$settings = array_get(wpcm_get_settings(), 'gateways.paypal_express_gateway');

		$payment_data1 = collect($payment_data);
		$mode = array_get($settings, 'sandbox') ? 'sandbox' : 'live';
		/*
		 * The SetExpressCheckout API operation initiates an Express Checkout transaction
		 * This sample code uses Merchant PHP SDK to make API call
		 */
		$url = wpcm_get_success_page_url(array('key' => $payment_data['purchase_key']));
		
		$returnUrl = add_query_arg(array('type' => 'success'), $url) ;
		$cancelUrl = add_query_arg(array('type' => 'cancel'), $url); 
		$currencyCode = array_get($payment_data, 'post_data.currency');
		// total shipping amount
		$shippingTotal = new BasicAmountType($currencyCode, 0);
		//total handling amount if any
		$handlingTotal = new BasicAmountType($currencyCode, 0);
		//total insurance amount if any
		$insuranceTotal = new BasicAmountType($currencyCode, 0);
		// shipping address
		$address = new AddressType();
		$address->CityName = 'N/A';
		$address->Name = array_get($payment_data, 'user_info.first_name') . ' ' . array_get($payment_data, 'user_info.last_name');
		$address->Street1 = $payment_data1->get('address');
		$address->StateOrProvince = 'N/A';
		$address->PostalCode = 'N/A';
		$address->Country = '';
		$address->Phone = $payment_data1->get('phone');
		// details about payment
		$paymentDetails = new PaymentDetailsType();
		$itemTotalValue = 0;
		$taxTotalValue = 0;
		$item_count = 0;
		/*
		 * iterate trhough each item and add to atem detaisl
		 */
		foreach( $payment_data1->get('items') as $item ) {
			$itemAmount = new BasicAmountType($currencyCode, $item['price']);
			$itemTotalValue += $item['price'] * $item['quantity'];
			$itemDetails = new PaymentDetailsItemType();
			$itemDetails->Name = get_the_title($item['item_id']);
			$itemDetails->Amount = $itemAmount;
			$itemDetails->Quantity = $item['quantity'];
			$itemDetails->ItemCategory = apply_filters( 'paypal_standard_item_category', 'Physical' );
			$paymentDetails->PaymentDetailsItem[$item_count] = $itemDetails;
			$item_count++;	
		}
		
		/*
		 * The total cost of the transaction to the buyer. If shipping cost and tax charges are known, include them in this value. If not, this value should be the current subtotal of the order. If the transaction includes one or more one-time purchases, this field must be equal to the sum of the purchases. If the transaction does not include a one-time purchase such as when you set up a billing agreement for a recurring payment, set this field to 0.
		 */
		$orderTotalValue = $shippingTotal->value + $handlingTotal->value +
		$insuranceTotal->value +
		$itemTotalValue + $taxTotalValue;
		//Payment details
		$paymentDetails->ShipToAddress = $address;
		$paymentDetails->ItemTotal = new BasicAmountType($currencyCode, $itemTotalValue);
		$paymentDetails->TaxTotal = new BasicAmountType($currencyCode, $taxTotalValue);
		$paymentDetails->OrderTotal = new BasicAmountType($currencyCode, $orderTotalValue);
		/*
		 * How you want to obtain payment. When implementing parallel payments, this field is required and must be set to Order. When implementing digital goods, this field is required and must be set to Sale. If the transaction does not include a one-time purchase, this field is ignored. It is one of the following values:
		    Sale – This is a final sale for which you are requesting payment (default).
		    Authorization – This payment is a basic authorization subject to settlement with PayPal Authorization and Capture.
		    Order – This payment is an order authorization subject to settlement with PayPal Authorization and Capture.
		 */
		$paymentDetails->PaymentAction = 'Sale';//$_REQUEST['paymentType'];
		$paymentDetails->HandlingTotal = $handlingTotal;
		$paymentDetails->InsuranceTotal = $insuranceTotal;
		$paymentDetails->ShippingTotal = $shippingTotal;
		/*
		 *  Your URL for receiving Instant Payment Notification (IPN) about this transaction. If you do not specify this value in the request, the notification URL from your Merchant Profile is used, if one exists.
		 */
		if($url) {
			$paymentDetails->NotifyURL = add_query_arg(array('type' => 'notify'), $url);
		}
		$setECReqDetails = new SetExpressCheckoutRequestDetailsType();
		$setECReqDetails->PaymentDetails[0] = $paymentDetails;
		/*
		 * (Required) URL to which the buyer is returned if the buyer does not approve the use of PayPal to pay you. For digital goods, you must add JavaScript to this page to close the in-context experience.
		 */
		$setECReqDetails->CancelURL = $cancelUrl;
		/*
		 * (Required) URL to which the buyer's browser is returned after choosing to pay with PayPal. For digital goods, you must add JavaScript to this page to close the in-context experience.
		 */
		$setECReqDetails->ReturnURL = $returnUrl;
		/*
		 * Determines where or not PayPal displays shipping address fields on the PayPal pages. For digital goods, this field is required, and you must set it to 1. It is one of the following values:
		    0 – PayPal displays the shipping address on the PayPal pages.
		    1 – PayPal does not display shipping address fields whatsoever.
		    2 – If you do not pass the shipping address, PayPal obtains it from the buyer's account profile.
		 */
		$setECReqDetails->NoShipping = 1;
		/*
		 *  (Optional) Determines whether or not the PayPal pages should display the shipping address set by you in this SetExpressCheckout request, not the shipping address on file with PayPal for this buyer. Displaying the PayPal street address on file does not allow the buyer to edit that address. It is one of the following values:
		    0 – The PayPal pages should not display the shipping address.
		    1 – The PayPal pages should display the shipping address.
		 */
		$setECReqDetails->AddressOverride = 0;
		/*
		 * Indicates whether or not you require the buyer's shipping address on file with PayPal be a confirmed address. For digital goods, this field is required, and you must set it to 0. It is one of the following values:
		    0 – You do not require the buyer's shipping address be a confirmed address.
		    1 – You require the buyer's shipping address be a confirmed address.
		 */
		$setECReqDetails->ReqConfirmShipping = 0;
		// Billing agreement details
		$recurring = webinane_set( $payment_data, 'recurring' );
		if(is_string($recurring)) {
			$recurring = ($recurring === 'true') ? true : false;
		}
		if( $recurring === true ) {
			$billingAgreementDetails = new BillingAgreementDetailsType('RecurringPayments');
			$billingAgreementDetails->BillingAgreementDescription = esc_html__( 'Billing agreement', 'webinane-commerce' );
			$setECReqDetails->BillingAgreementDetails = array($billingAgreementDetails);
		}
		// Display options
		$setECReqDetails->BrandName = get_bloginfo('name');
		// Advanced options
		$setECReqDetails->AllowNote = true;//$_REQUEST['allowNote'];
		$setECReqType = new SetExpressCheckoutRequestType();
		$setECReqType->SetExpressCheckoutRequestDetails = $setECReqDetails;
		$setECReq = new SetExpressCheckoutReq();
		$setECReq->SetExpressCheckoutRequest = $setECReqType;
		/*
		 * 	 ## Creating service wrapper object
		Creating service wrapper object to make API call and loading
		Configuration::getAcctAndConfig() returns array that contains credential and config parameters
		*/
		$paypalService = new PayPalAPIInterfaceServiceService(self::config());
		try {
			/* wrap API method calls on the service object with a try catch */
			$setECResponse = $paypalService->SetExpressCheckout($setECReq);
		} catch (Exception $ex) {
			wp_send_json_error( esc_html__( $ex->getMessage(), 'webinane-commerce' ) );
		}
		if(isset($setECResponse)) {
			if($setECResponse->Ack =='Success') {
				$token = $setECResponse->Token;
				// Redirect to paypal.com here
				if( $mode == 'sandbox' ) {
					$payPalURL = 'https://www.sandbox.paypal.com/webscr?cmd=_express-checkout&token=' . $token;
				} else {
					$payPalURL = 'https://www.paypal.com/webscr?cmd=_express-checkout&token=' . $token;
				}
				$order_id = Orders::create($payment_data);
				wp_send_json( array('type' => 'redirect', 'url' => $payPalURL) );
			} else {
				if( $setECResponse->Ack == 'Failure') {
					wp_send_json_error( ['message' => $setECResponse->Errors[0]->LongMessage] );
				} else {
					wp_send_json_error( ['message' => esc_html__('Something went wrong', 'webinane-commerce') ] );
				}
			}
		}
		exit;
	}
	/**
	 * Finally capture the payment using token
	 * 
	 * @param  [type] $order [description]
	 * @param  [type] $key   [description]
	 * @return [type]        [description]
	 */
	static function capture_payment($order, $key) {
		$settings = array_get(wpcm_get_settings(), 'gateways.paypal_express_gateway');
		$submitted_data = get_post_meta($order->ID, '_wpcm_order_submitted_data', true);
		$mode = array_get($settings, 'sandbox') ? 'sandbox' : 'live';
		
		$return_type = webinane_set($_REQUEST, 'type');
		
		if( $return_type !== 'success' ) {
			echo '<div class="alert alert-danger">' . esc_html__( 'There is something went wrong', 'webinane-commerce' ) . '</div>';
			return;
		}
		if( self::is_payment_done($order)) {
			webinane_template('orders/paypal-order-detail.php', compact('order'));
			return;
		}
		$recurring = webinane_set( $submitted_data, 'recurring' );

		if(is_string($recurring)) {
			$recurring = ($recurring === 'true') ? true : false;
		}
		if( $recurring === true ) {
			self::recurring_payment($order);
			return;
		}

		/*
		 * The DoExpressCheckoutPayment API operation completes an Express Checkout transaction. If you set up a billing agreement in your SetExpressCheckout API call, the billing agreement is created when you call the DoExpressCheckoutPayment API operatio
		 */
		/*
		 * The total cost of the transaction to the buyer. If shipping cost (not applicable to digital goods) and tax charges are known, include them in this value. If not, this value should be the current sub-total of the order. If the transaction includes one or more one-time purchases, this field must be equal to the sum of the purchases. Set this field to 0 if the transaction does not include a one-time purchase such as when you set up a billing agreement for a recurring payment that is not immediately charged. When the field is set to 0, purchase-specific fields are ignored.
		 * For digital goods, the following must be true:
		 * total cost > 0
		 * total cost <= total cost passed in the call to SetExpressCheckout
		*/
		$token =urlencode( $_REQUEST['token'] );
		/*
		 *  Unique PayPal buyer account identification number as returned in the GetExpressCheckoutDetails response
		*/
		$payerId=urlencode(  $_REQUEST['PayerID'] );
		$paymentAction = urlencode('Sale'); 
		// ------------------------------------------------------------------
		// this section is optional if parameters required for DoExpressCheckout is retrieved from your database
		$getExpressCheckoutDetailsRequest = new GetExpressCheckoutDetailsRequestType($token);
		$getExpressCheckoutReq = new GetExpressCheckoutDetailsReq();
		$getExpressCheckoutReq->GetExpressCheckoutDetailsRequest = $getExpressCheckoutDetailsRequest;
		/*
		Configuration::getAcctAndConfig() returns array that contains credential and config parameters
		*/
		$paypalService = new PayPalAPIInterfaceServiceService(self::config());
		try {
			/* wrap API method calls on the service object with a try catch */
			$getECResponse = $paypalService->GetExpressCheckoutDetails($getExpressCheckoutReq);
		} catch (Exception $ex) {
			echo '<div class="alert alert-danger">' . $ex->getMessage() . '</div>';
		}
		//----------------------------------------------------------------------------
		/*
		 * The total cost of the transaction to the buyer. If shipping cost (not applicable to digital goods) and tax charges are known, include them in this value. If not, this value should be the current sub-total of the order. If the transaction includes one or more one-time purchases, this field must be equal to the sum of the purchases. Set this field to 0 if the transaction does not include a one-time purchase such as when you set up a billing agreement for a recurring payment that is not immediately charged. When the field is set to 0, purchase-specific fields are ignored.
		*/
		$orderTotal = new BasicAmountType();
		$orderTotal->currencyID = $getECResponse->GetExpressCheckoutDetailsResponseDetails->PaymentDetails[0]->OrderTotal->currencyID;
		$orderTotal->value = $getECResponse->GetExpressCheckoutDetailsResponseDetails->PaymentDetails[0]->OrderTotal->value;
		$paymentDetails= new PaymentDetailsType();
		$paymentDetails->OrderTotal = $orderTotal;
		/*
		 * Your URL for receiving Instant Payment Notification (IPN) about this transaction. If you do not specify this value in the request, the notification URL from your Merchant Profile is used, if one exists.
		 */
		if(isset($_REQUEST['notifyURL']))
		{
			$paymentDetails->NotifyURL = $_REQUEST['notifyURL'];
		}
		$DoECRequestDetails = new DoExpressCheckoutPaymentRequestDetailsType();
		$DoECRequestDetails->PayerID = $payerId;
		$DoECRequestDetails->Token = $token;
		$DoECRequestDetails->PaymentAction = $paymentAction;
		$DoECRequestDetails->PaymentDetails[0] = $paymentDetails;
		$DoECRequest = new DoExpressCheckoutPaymentRequestType();
		$DoECRequest->DoExpressCheckoutPaymentRequestDetails = $DoECRequestDetails;
		$DoECReq = new DoExpressCheckoutPaymentReq();
		$DoECReq->DoExpressCheckoutPaymentRequest = $DoECRequest;
		try {
			/* wrap API method calls on the service object with a try catch */
			$DoECResponse = $paypalService->DoExpressCheckoutPayment($DoECReq);
		} catch (Exception $ex) {
			echo '<div class="alert alert-danger">' . $ex->getMessage() . '</div>';
		}
		if(isset($DoECResponse)) {
			self::update_payment_info($DoECResponse, $order, $token);
		}
	}
	/**
	 * After getting response from PayPal update the payment detail in order meta and tokens.
	 * 
	 * @param  [type] $response [description]
	 * @param  [type] $order    [description]
	 * @param  [type] $token    [description]
	 * @return [type]           [description]
	 */
	static function update_payment_info($response, $order, $token) {
		global $wpdb;
		$table = $wpdb->prefix . 'wpcommerce_payment_tokens';
		$detail = $response->DoExpressCheckoutPaymentResponseDetails;
		if( isset( $detail->Token ) ) {
			$token = $detail->Token;
		}
		$res = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE token = %s AND user_id = %d", $token, get_current_user_id() ) );
		if( $res ) {
			$wpdb->update(
				$wpdb->prefix . 'wpcommerce_payment_tokens',
				array(
					'gateway_id' => 'paypal',
					'token'		=> $token,
					'user_id'	=> get_current_user_id(),
					'type'	=> 'express-checkout',
					'is_default'	=> 0
				),
				array( 'token_id' => $res->token_id)
			);
		} else {
			$wpdb->insert(
				$wpdb->prefix . 'wpcommerce_payment_tokens',
				array(
					'gateway_id' => 'paypal',
					'token'		=> $token,
					'user_id'	=> get_current_user_id(),
					'type'	=> 'express-checkout',
					'is_default'	=> 0
				)
			);
		}
		if( isset( $detail->PaymentInfo ) ) {
			$payment_info = $detail->PaymentInfo[0];
			if( isset( $order->ID ) ) {
				update_post_meta( $order->ID, '_order_transaction_id', $payment_info->TransactionID );
				update_post_meta( $order->ID, '_order_total', $payment_info->GrossAmount->value );
				update_post_meta( $order->ID, '_order_currency', $payment_info->GrossAmount->currencyID );
				update_post_meta( $order->ID, '_order_fee', $payment_info->FeeAmount->value );
				
				//Finally save complete response in meta
				update_post_meta( $order->ID, '_order_transaction_response', $response );
				
				if( $payment_info->PaymentStatus == 'Completed' ) {
					$order->post_status = 'completed';
					$post_id = wp_update_post( (array) $order, true );
					
					if( is_wp_error( $post_id ) ) {
						echo '<div class="alert alert-danger">' . $post_id->getMessage() . '</div>';
					}
				}
			}
		}
		wpcm_empty_cart();
		webinane_template('orders/paypal-order-detail.php', compact('order'));
	}
	/**
	 * [is_payment_done description]
	 *
	 * @param  [type]  $order [description]
	 * @return boolean        [description]
	 */
	static function is_payment_done($order) {
		global $wpdb;
		$table = $wpdb->prefix . 'wpcommerce_payment_tokens';
		$token = urlencode( $_REQUEST['token'] );
		$res = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE token = %s AND user_id = %d", $token, get_current_user_id() ) );
		if( $res ) {
			return true;
		}
		return false;
	}

	/**
	 * [recurring_payment description]
	 * @param  [type] $order [description]
	 * @return [type]        [description]
	 */
	static function recurring_payment($order) {
		$settings = array_get(wpcm_get_settings(), 'gateways.paypal_express_gateway');
		$submitted_data = get_post_meta($order->ID, '_wpcm_order_submitted_data', true);
		$total = get_post_meta($order->ID, '_wpcm_order_total', true);
		$extras = array_get( $submitted_data, 'post_data.extras');
		$recurring_start = webinane_set( $extras, 'recuring_start' );
		if($recurring_start) {
			$recurring_start = substr($recurring_start, 0, strpos($recurring_start, '('));
			$recurring_start = date(DATE_ATOM, strtotime($recurring_start));
		} else {
			$recurring_start = current_time(DATE_ATOM);
		}
		$cycles = webinane_set($extras, 'gifts_number', 12);
		$proflie_id = get_post_meta($order->ID, '_wpcm_recurring_profile_id', true );
		// printr(get_post_meta($order->ID));
		if( $proflie_id ) {
			self::get_recurring_profile_info($proflie_id);
			// echo 'Proflie Already created usign this token';
			webinane_template('orders/paypal-order-detail.php', compact('order'));			
			return;
		}
		/**
		 *
		 # CreateRecurringPaymentsProfile API
		 The CreateRecurringPaymentsProfile API operation creates a recurring
		 payments profile.
		 You must invoke the CreateRecurringPaymentsProfile API operation for each
		 profile you want to create. The API operation creates a profile and an
		 associated billing agreement.
		 `Note:
		 There is a one-to-one correspondence between billing agreements and
		 recurring payments profiles. To associate a recurring payments profile
		 with its billing agreement, you must ensure that the description in the
		 recurring payments profile matches the description of a billing
		 agreement. For version 54.0 and later, use SetExpressCheckout to initiate
		 creation of a billing agreement.`
		 This sample code uses Merchant PHP SDK to make API call
		*/
		$token = webinane_set( $_REQUEST, 'token' );
		$currencyCode = wpcm_get_settings()->get('base_currency', 'USD');
		/*
		 *  You can include up to 10 recurring payments profiles per request. The
		order of the profile details must match the order of the billing
		agreement details specified in the SetExpressCheckout request which
		takes mandatory argument:
		* `billing start date` - The date when billing for this profile begins.
		`Note:
		The profile may take up to 24 hours for activation.`
		*/
		$RPProfileDetails = new RecurringPaymentsProfileDetailsType();
		$RPProfileDetails->SubscriberName = $order->post_title;
		$RPProfileDetails->BillingStartDate = $recurring_start;
		
		$activationDetails = new ActivationDetailsType();
		/*
		 * (Optional) Initial non-recurring payment amount due immediately upon profile creation. Use an initial amount for enrolment or set-up fees.
		 */
		/*
		 *  (Optional) Action you can specify when a payment fails. It is one of the following values:
		    ContinueOnFailure – By default, PayPal suspends the pending profile in the event that the initial payment amount fails. You can override this default behavior by setting this field to ContinueOnFailure. Then, if the initial payment amount fails, PayPal adds the failed payment amount to the outstanding balance for this recurring payment profile.
		    When you specify ContinueOnFailure, a success code is returned to you in the CreateRecurringPaymentsProfile response and the recurring payments profile is activated for scheduled billing immediately. You should check your IPN messages or PayPal account for updates of the payment status.
		    CancelOnFailure – If this field is not set or you set it to CancelOnFailure, PayPal creates the recurring payment profile, but places it into a pending status until the initial payment completes. If the initial payment clears, PayPal notifies you by IPN that the pending profile has been activated. If the payment fails, PayPal notifies you by IPN that the pending profile has been canceled.
		 */
		$activationDetails->FailedInitialAmountAction = 'ContinueOnFailure';
		/*
		 *  Regular payment period for this schedule which takes mandatory
		params:
		* `Billing Period` - Unit for billing during this subscription period. It is one of the
		following values:
		* Day
		* Week
		* SemiMonth
		* Month
		* Year
		For SemiMonth, billing is done on the 1st and 15th of each month.
		`Note:
		The combination of BillingPeriod and BillingFrequency cannot exceed
		one year.`
		* `Billing Frequency` - Number of billing periods that make up one billing cycle.
		The combination of billing frequency and billing period must be less
		than or equal to one year. For example, if the billing cycle is
		Month, the maximum value for billing frequency is 12. Similarly, if
		the billing cycle is Week, the maximum value for billing frequency is
		52.
		`Note:
		If the billing period is SemiMonth, the billing frequency must be 1.`
		* `Billing Amount`
		*/
		
		$feq = array('Day' => 365, 'Week' => 52, 'SemiMonth' => 1, 'Month' => 12, 'Year' => 1);
		$period = self::getPeriod($submitted_data);
		$paymentBillingPeriod =  new BillingPeriodDetailsType();
		$paymentBillingPeriod->BillingFrequency = 1;//webinane_set( $feq, webinane_set($submitted_data, 'billing_period', 'Month'), 12);
		$paymentBillingPeriod->BillingPeriod = $period;//webinane_set( $submitted_data, 'billing_period', 'Month' );
		// $paymentBillingPeriod->TotalBillingCycles = 10;
		if($cycles) {
			$paymentBillingPeriod->TotalBillingCycles = $cycles;
		}
		$paymentBillingPeriod->Amount = new BasicAmountType($currencyCode, $total);
		$paymentBillingPeriod->TaxAmount = new BasicAmountType($currencyCode, 0);
		/*
		 * 	 Describes the recurring payments schedule, including the regular
		payment period, whether there is a trial period, and the number of
		payments that can fail before a profile is suspended which takes
		mandatory params:
		* `Description` - Description of the recurring payment.
		`Note:
		You must ensure that this field matches the corresponding billing
		agreement description included in the SetExpressCheckout request.`
		* `Payment Period`
		*/
		$scheduleDetails = new ScheduleDetailsType();
		$scheduleDetails->Description = esc_html__( 'Billing agreement', 'webinane-commerce' );
		$scheduleDetails->ActivationDetails = $activationDetails;
		//if( $_REQUEST['trialBillingFrequency'] != "" && $_REQUEST['trialAmount'] != "") {
		if( false ) {
			$trialBillingPeriod =  new BillingPeriodDetailsType();
			$trialBillingPeriod->BillingFrequency = $_REQUEST['trialBillingFrequency'];
			$trialBillingPeriod->BillingPeriod = $_REQUEST['trialBillingPeriod'];
			$trialBillingPeriod->TotalBillingCycles = $_REQUEST['trialBillingCycles'];
			$trialBillingPeriod->Amount = new BasicAmountType($currencyCode, $_REQUEST['trialAmount']);
			$trialBillingPeriod->ShippingAmount = new BasicAmountType($currencyCode, $_REQUEST['trialShippingAmount']);
			$trialBillingPeriod->TaxAmount = new BasicAmountType($currencyCode, $_REQUEST['trialTaxAmount']);
			$scheduleDetails->TrialPeriod  = $trialBillingPeriod;
		}
		$scheduleDetails->PaymentPeriod = $paymentBillingPeriod;
		
		$scheduleDetails->MaxFailedPayments =  100;
		
		/*if($_REQUEST['autoBillOutstandingAmount'] != "") {
			$scheduleDetails->AutoBillOutstandingAmount = $_REQUEST['autoBillOutstandingAmount'];
		}*/
		/*
		 * 	 `CreateRecurringPaymentsProfileRequestDetailsType` which takes
		mandatory params:
		* `Recurring Payments Profile Details`
		* `Schedule Details`
		*/
		$createRPProfileRequestDetail = new CreateRecurringPaymentsProfileRequestDetailsType();
		$createRPProfileRequestDetail->Token  = $token;
		
		$createRPProfileRequestDetail->ScheduleDetails = $scheduleDetails;
		$createRPProfileRequestDetail->RecurringPaymentsProfileDetails = $RPProfileDetails;
		$createRPProfileRequest = new CreateRecurringPaymentsProfileRequestType();
		$createRPProfileRequest->CreateRecurringPaymentsProfileRequestDetails = $createRPProfileRequestDetail;
		$createRPProfileReq =  new CreateRecurringPaymentsProfileReq();
		$createRPProfileReq->CreateRecurringPaymentsProfileRequest = $createRPProfileRequest;
		/*
		 *  ## Creating service wrapper object
		Creating service wrapper object to make API call and loading
		Configuration::getAcctAndConfig() returns array that contains credential and config parameters
		*/
		$paypalService = new PayPalAPIInterfaceServiceService(self::config());
		try {
			/* wrap API method calls on the service object with a try catch */
			$createRPProfileResponse = $paypalService->CreateRecurringPaymentsProfile($createRPProfileReq);
			webinane_template('orders/paypal-order-detail.php', compact('order'));
		} catch (Exception $ex) {
			echo '<div class="alert alert-danger">' . $ex->getMessage() . '</div>';
		}
		$proflie_id = '';
		if(isset($createRPProfileResponse)) {
			if(isset($createRPProfileResponse->Errors)) {
				echo '<div class="alert alert-danger">' . $createRPProfileResponse->Errors[0]->LongMessage . '</div>';
				return;
			} else {
				$proflie_id = $createRPProfileResponse->CreateRecurringPaymentsProfileResponseDetails->ProfileID;
				update_post_meta($order->ID, '_wpcm_recurring_profile_id', $proflie_id);
				update_post_meta($order->ID, '_wpcm_order_is_recurring', 'YES');
				update_post_meta($order->ID, '_wpcm_order_recurring_period', $period);
			}
		}
		if( $proflie_id ) {
			// self::capture_recurring_payment($order, $proflie_id);
		}
	}
	/**
	 * Get Billing period from submitted data.
	 * 
	 * @param  [type] $data [description]
	 * @return [type]       [description]
	 */
	static function getPeriod($data) {
		
		$def = array('daily' => 'Day', 'weekly' => 'Week', 'fortnightly' => 'SemiMonth', 'monthly' => 'Month', 'quarterly' => 'Year', 'half yearly' => 'Year', 'yearly' => 'Year' );
		$period = webinane_set($data, 'billing_period', 'monthly');
		return webinane_set($def, $period, 'Month');
	}
	/**
	 * [capture_recurring_payment description]
	 *
	 * @param  [type] $order      [description]
	 * @param  [type] $proflie_id [description]
	 * @return [type]             [description]
	 */
	static function capture_recurring_payment($order, $proflie_id) {
		$settings = wpcm_get_settings();
		$currencyCode = $settings->get('base_currency', 'USD');
		$submitted_data = get_post_meta($order->ID, '_wpcm_order_submitted_data', true);
		$total = get_post_meta($order->ID, '_wpcm_order_total', true);
		if( ! $proflie_id ) {
			echo '<div class="alert alert-danger">' . esc_html__( 'Invalid profile ID', 'webinane-commerce' ) . '</div>';
			return;
		}
		/*
		 * The BillOutstandingAmount API operation bills the buyer for the outstanding balance associated with a recurring payments profile. 
		 */
		$billOutstandingAmtReqestDetail = new BillOutstandingAmountRequestDetailsType();
		/*
		 * (Optional) The amount to bill. The amount must be less than or equal to the current outstanding balance of the profile. If no value is specified, PayPal attempts to bill the entire outstanding balance amount.
		 */
		$billOutstandingAmtReqestDetail->Amount = new BasicAmountType($currencyCode, $total);
		/*
		 *  (Required) Recurring payments profile ID returned in the CreateRecurringPaymentsProfile response.
		Note:The profile must have a status of either Active or Suspended. 
		 */
		$billOutstandingAmtReqestDetail->ProfileID = $proflie_id;
		$billOutstandingAmtReqest = new BillOutstandingAmountRequestType();
		$billOutstandingAmtReqest->BillOutstandingAmountRequestDetails = $billOutstandingAmtReqestDetail;
		$billOutstandingAmtReq =  new BillOutstandingAmountReq();
		$billOutstandingAmtReq->BillOutstandingAmountRequest = $billOutstandingAmtReqest;
		/*
		 * 	 ## Creating service wrapper object
		Creating service wrapper object to make API call and loading
		Configuration::getAcctAndConfig() returns array that contains credential and config parameters
		*/
		$paypalService = new PayPalAPIInterfaceServiceService(self::config());
		try {
			/* wrap API method calls on the service object with a try catch */
			$billOutstandingAmtResponse = $paypalService->BillOutstandingAmount($billOutstandingAmtReq);
			printr($billOutstandingAmtResponse);
		} catch (Exception $ex) {
			echo '<div class="alert alert-danger">' . $ex->getMessage() . '</div>';
		}
		if(isset($billOutstandingAmtResponse)) {
			echo "<pre>";
			print_r($billOutstandingAmtResponse);
			echo "</pre>";
		}
	}
	/**
	 * [get_recurring_profile_info description]
	 *
	 * @param  [type] $profile_id [description]
	 * @return [type]             [description]
	 */
	static function get_recurring_profile_info($profile_id) {
		/*
		 * Obtain information about a recurring payments profile. 
		 */
		$getRPPDetailsReqest = new GetRecurringPaymentsProfileDetailsRequestType();
		/*
		 * (Required) Recurring payments profile ID returned in the CreateRecurringPaymentsProfile response. 19-character profile IDs are supported for compatibility with previous versions of the PayPal API.
		 */
		$getRPPDetailsReqest->ProfileID = $profile_id;
		$getRPPDetailsReq = new GetRecurringPaymentsProfileDetailsReq();
		$getRPPDetailsReq->GetRecurringPaymentsProfileDetailsRequest = $getRPPDetailsReqest;
		/*
		 * 	 ## Creating service wrapper object
		Creating service wrapper object to make API call and loading
		Configuration::getAcctAndConfig() returns array that contains credential and config parameters
		*/
		$paypalService = new PayPalAPIInterfaceServiceService(self::config());
		try {
			/* wrap API method calls on the service object with a try catch */
			$getRPPDetailsResponse = $paypalService->GetRecurringPaymentsProfileDetails($getRPPDetailsReq);
		} catch (Exception $ex) {
			wp_send_json_error(array('message' => $ex));
		}
		if(isset($getRPPDetailsResponse)) {
			
		}
	}
	static function IPN_capture() {
		// first param takes ipn data to be validated. if null, raw POST data is read from input stream
		$ipnMessage = new PPIPNMessage(null, self::config()); 
		$raw = $ipnMessage->getRawData();
		if($ipnMessage->validate()) {
			if( webinane_set($raw, 'txn_type') == 'recurring_payment') {
				$profile_id = esc_attr( webinane_set( $raw, 'recurring_payment_id') );
				$query = new \WP_Query(array('post_type' => 'orders', 'meta_key' => '_wpcm_recurring_profile_id', 'meta_value' => $profile_id ) );
				if( $query->have_posts() ) {
					$post = (array)$query->post;
					$meta = self::parse_meta($query->post->ID);
					$post['post_date'] = date('Y-m-d h:i:s');
					$post['post_status'] = (webinane_set($raw, 'payment_status') == 'Completed') ? 'completed' : 'pending_payment';
					$order_id = wp_insert_post( $post, true );
					if( ! is_wp_error($order_id)) {
						$auth_key = defined( 'AUTH_KEY' ) ? AUTH_KEY : '';
						// Set up the unique purchase key. If we are resuming a payment, we'll overwrite this with the existing key.
						$purchase_key     = strtolower( md5( sanitize_email(webinane_set($raw, 'payer_email') ) . date( 'Y-m-d H:i:s' ) . $auth_key . uniqid( 'wpcommerce', true ) ) );
						if( $value = webinane_set( $meta, '_wpcm_order_customer_id') ) {
							update_post_meta( $order_id, '_wpcm_order_customer_id',  $value );
						}
						if( $value = webinane_set( $meta, '_wpcm_order_purchase_key') ) {
							update_post_meta( $order_id, '_wpcm_order_purchase_key',  $purchase_key );
						}
						if( $value = webinane_set( $meta, '_wpcm_order_total') ) {
							update_post_meta( $order_id, '_wpcm_order_total',  webinane_set($raw, 'amount') );
						}
						if( $value = webinane_set( $meta, '_wpcm_order_customer_ip') ) {
							update_post_meta( $order_id, '_wpcm_order_customer_ip',  $value );
						}
						update_post_meta( $order_id, '_wpcm_order_gateway',  'paypal' );
						update_post_meta( $order_id, '_order_transaction_id',  esc_attr(webinane_set($raw, 'txn_id')) );
						update_post_meta( $order_id, '_order_subscription_id',  $profile_id );
						$sandbox = wpcm_get_settings()->get('paypal_sandbox_status') ? true : false;
						update_post_meta( $order_id, '_order_sandbox',  $sandbox );
						update_post_meta( $order_id, '_wpcm_is_recurring',  1 );
						update_post_meta( $order_id, '_wpcm_recurring_cycle',  array_get($meta, '_wpcm_recurring_cycle', 'Month') );
						update_post_meta( $order_id, '_wpcm_recurring_frequency',  array_get($meta, '_wpcm_recurring_frequency', 12) );
						
					} else {
						self::write_log($order_id->get_error_message() );
					}
				}
			}
		} else {
			
		}
		// Finally the response in the log file.
		$file = WPCM_PATH . "includes/Gateways/logs/".date('Y').'/'.date('m').'/paypal_ipn_'.date('d_h-m').".log";
        webinane_filesystem()->mkdir(dirname($file));
		webinane_filesystem()->put_contents($file, wp_json_encode($raw), FS_CHMOD_FILE);
	}

	/**
	 * Parse Meta.
	 *
	 * @param  [type] $post_id [description]
	 * @return [type]          [description]
	 */
	static function parse_meta($post_id) {
		$meta = get_post_meta($order->ID);
		$new_meta = array();
		foreach( $new_meta as $key => $mt) {
			$new_meta[$key] = $mt[0];
		}
		return $new_meta;
	}
	/**
	 * Set the order items.
	 * @param [type] $old_order [description]
	 * @param [type] $new_order [description]
	 */
	static function set_new_order_items($old_order, $new_order) {
		global $wpdb;
		$res = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}wpcommerce_order_item WHERE order_id = %d", $old_order), ARRAY_A);
		if( $res ) {
			foreach( $res as $re ) {
				$re['order_id'] = $new_order->ID;
				unset( $re['order_item_id'] );
				$wpdb->insert($wpdb->prefix.'wpcommerce_order_ite', $re);
			}
		}
	}
	/**
	 * Process refund.
	 * 
	 * @param  array $order  An array of order data.
	 * @return [type]        [description]
	 */
	static function process_refund($order) {
		$arr = webinane_array($order);
		$posted_data = webinane_array(get_post_meta($arr->get('ID'), '_wpcm_order_submitted_data', true));
		$amount = get_post_meta($arr->get('ID'), '_wpcm_order_total', true);
		$currency = get_post_meta($arr->get('ID'), '_wpcm_order_currency_code', true);
		$currency = (! $amount) ? wpcm_get_settings()->get('base_currency', 'USD') : $currency;
		$transaction_id = get_post_meta($arr->get('ID'), '_order_transaction_id', true);
		if( $arr->get('post_status') !== 'completed' ) {
			wp_send_json_error( array('message' => esc_html__('Payment was not completed, cannot give refund', 'webinane-commerce') ) );
		}
		if( ! $transaction_id ) {
			wp_send_json_error( array('message' => 'Could not find transaction ID, please check your paypal account', 'webinane-commerce') );
		}
		/*
		 * The RefundTransaction API operation issues a refund to the PayPal account holder associated with a transaction. 
		 This sample code uses Merchant PHP SDK to make API call
		 */
		$refundReqest = new RefundTransactionRequestType();
		$refundReqest->Amount = new BasicAmountType($currency, $amount);
		$refundReqest->RefundType = 'Full';
		/*
		 *  Either the `transaction ID` or the `payer ID` must be specified.
				 PayerID is unique encrypted merchant identification number
				 For setting `payerId`,
				 `refundTransactionRequest.setPayerID("A9BVYX8XCR9ZQ");`
				 Unique identifier of the transaction to be refunded.
		 */
		$refundReqest->TransactionID = $transaction_id;
		$refundReq = new RefundTransactionReq();
		$refundReq->RefundTransactionRequest = $refundReqest;
		/*
		 * 	 ## Creating service wrapper object
		Creating service wrapper object to make API call and loading
		Configuration::getAcctAndConfig() returns array that contains credential and config parameters
		*/
		$paypalService = new PayPalAPIInterfaceServiceService(self::config());
		try {
			/* wrap API method calls on the service object with a try catch */
			$refundResponse = $paypalService->RefundTransaction($refundReq);
		} catch (Exception $ex) {
			wp_send_json_error( array('message' => $ex->getMessage()) );
		}
		if(isset($refundResponse) && isset($refundResponse->Ack)) {
			if( $refundResponse->Ack == 'Success') {
				$order['post_status'] = 'refunded';
				$res = wp_update_post( $order, true );
				if( is_wp_error( $res )) {
					wp_send_json_error( array('message' => $res->get_error_message() ) );
				}
				update_post_meta( $arr->get('ID'), '_wpcm_order_refund_id', $refundResponse->RefundTransactionID );
			}
		}
		wp_send_json_success( array('message' => esc_html__('Refund is processed successfully in PayPal', 'webinane-commerce') ) );
	}

	/**
	 * [admin_meta description]
	 * 
	 * @param  [type] $meta_data [description]
	 * @param  [type] $order     [description]
	 * @return [type]            [description]
	 */
	static function admin_meta($meta_data, $order) {

		$profile_id = get_post_meta($order->ID, '_wpcm_recurring_profile_id', true);

		$meta_data[] = [
			'label'	=>  esc_html__('Recurring', 'webinane-commerce'),
			'value'	=> ($profile_id) ? esc_html__('YES', 'webinane-commerce') : esc_html__('NO', 'webinane-commerce')
		];

		if($profile_id) {
			$cycle = get_post_meta($order->ID, '_wpcm_recurring_cycle', true);
			$frequency = get_post_meta($order->ID, '_wpcm_recurring_frequency', true);
			
			$period = get_post_meta($order->ID, '_wpcm_order_recurring_period', true);
			
			$meta_data[] = [
				'label'	=>  esc_html__('Recurring Cycle', 'webinane-commerce'),
				'value'	=> $period
			];
			$meta_data[] = [
				'label'	=>  esc_html__('Recurring Frequency', 'webinane-commerce'),
				'value'	=> $frequency
			];
			
		}
		
		return $meta_data;
	}
}
