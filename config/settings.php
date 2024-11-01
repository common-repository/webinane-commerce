<?php
use WebinaneCommerce\Fields\Checkbox;
use WebinaneCommerce\Fields\Country;
use WebinaneCommerce\Fields\Media;
use WebinaneCommerce\Fields\Select;
use WebinaneCommerce\Fields\Switcher;
use WebinaneCommerce\Fields\Text;
use WebinaneCommerce\Fields\Textarea;

return array(

	array(
		'title'			=> esc_html__( 'General', 'webinane-commerce' ),
		'icon'			=> 'el-icon-setting',
		'id'			=> 'general_settings',
		'children'		=> array(
			apply_filters( 'webinane_commerce/settings/address_fields', array(
				'id'	=> 'address-info',
				'title'	=> esc_html__('Address Info', 'webinane-commerce'),
				'heading'	=> esc_html__('Address Information', 'webinane-commerce'),
				'fields'	=> array(
					
					Country::make(__('Select Country and State', 'webinane-commerce'), 'base_country')
						->default(['country' => 'USA', 'state' => ''])
						->setHelp(esc_html__( 'Choose the base country and state', 'webinane-commerce' )),
					Text::make(esc_html__('City', 'webinane-commerce'), 'base_city')
						->default('New York')
						->setHelp(esc_html__( 'Enter the base city', 'webinane-commerce' )),
					Text::make(esc_html__('Add Address', 'webinane-commerce'), 'address_line_1')
						->default('Webinane Plaza, 3rd Floor NY')
						->setHelp(esc_html__( 'Enter the business address', 'webinane-commerce' )),
					Text::make(esc_html__('Address Line 2', 'webinane-commerce'), 'address_line_2')
						->setHelp(esc_html__( 'Enter the business address', 'webinane-commerce' )),
					Text::make(esc_html__('ZIP Code', 'webinane-commerce'), 'zip_code')
						->default('10200')
						->setHelp(esc_html__( 'Enter the ZIP / Postal Code', 'webinane-commerce' )),
					
				)
			)),
			apply_filters( 'webinane_commerce/settings/currency_info_fields', array(
				'id'	=> 'currency-info',
				'title'	=> esc_html__('Currency Info', 'webinane-commerce'),
				'heading'	=> esc_html__('Currency Information', 'webinane-commerce'),
				'fields'	=> array(
					
					Select::make(esc_html__('Select Currency', 'webinane-commerce'), 'base_currency')
						->default('USD')
						->setOptions(wpcm_currency_assos_data())
						->setHelp(esc_html__( 'Choose the base currency', 'webinane-commerce' )),
					Select::make(esc_html__('Currency Symbol Position', 'webinane-commerce'), 'currency_position')
						->default('left')
						->setOptions([
							'left'		=> esc_html__( 'Left (eg: $2,000.00)', 'webinane-commerce' ),
							'right'		=> esc_html__( 'Right (eg: 2,000.00$)', 'webinane-commerce' ),
							'left_s'	=> esc_html__( 'Left with Space (eg: $ 2,000.00)', 'webinane-commerce' ),
							'right_s'	=> esc_html__( 'Right with Space (eg: 2,000.00 $)', 'webinane-commerce' ),
						])
						->setHelp(esc_html__( 'Choose the currency symbol position', 'webinane-commerce' )),
					
					Text::make(esc_html__('Thousand Saparate', 'webinane-commerce'), 'thousand_saparator')
						->default(',')
						->setHelp(esc_html__( 'Enter the thousand amount saparator', 'webinane-commerce' )),
					Text::make(esc_html__('Decimal Separator', 'webinane-commerce'), 'decimal_saparator')
						->default('.')
						->setHelp(esc_html__( 'Enter the decimal saparator', 'webinane-commerce' )),
					Text::make(esc_html__('Number of decimals', 'webinane-commerce'), 'number_decimals')
						->default('.')
						->setHelp(esc_html__( 'Enter the number of decimals', 'webinane-commerce' )),
					
				)
			))
		),
		
	),
	array(
		'title'			=> esc_html__( 'Payments', 'webinane-commerce' ),
		'icon'			=> 'fa fa-th',
		'id'			=> 'payment_settings',
		'children'		=> apply_filters( 'wpcommerce_payment_gateways_setting_tabs', array(array(
			'title'			=> esc_html__( 'General', 'webinane-commerce' ),
			'icon'			=> 'fa fa-th',
			'id'			=> 'general_gateways_settings',
			'heading'	=> esc_html__('Gateway Settings', 'webinane-commerce'),
			'fields'		=> array(
				Switcher::make(esc_html__('Test Mode', 'webinane-commerce'), 'gateways_test_mode')
				->setHelp(esc_html__( 'While in the test mode no live payments are processed. To fully use test mode, you must have a sandbox(test) account for payment gateway', 'webinane-commerce' )),
				Checkbox::make(esc_html__('Gateways', 'webinane-commerce'), 'active_gateways')
				->setOptions(function() {
					$gateways = apply_filters( 'wpcommerce_payment_gateways', array() );
					$return = [];
					foreach($gateways as $gateway) {
						$return[$gateway->id] = $gateway->name;
					}
					return $return;
				})->withMeta(['class' => 'display-block'])
				->setHelp(sprintf(__( 'Enable your payment gateway. Want to get more payment gateways? <a href="%s" target="_blank">Click Here</a>', 'webinane-commerce' ), 'https://www.webinane.com/plugins')),

				//Default gateway
				Select::make(esc_html__('Default Gateway', 'webinane-commerce'), 'default_gateway')
				->setOptions(function() {
					$gateways = apply_filters( 'wpcommerce_payment_gateways', array() );
					$return = [];
					foreach($gateways as $gateway) {
						$return[$gateway->id] = $gateway->name;
					}
					return $return;
				})
				->setHelp(esc_html__( 'Choose the default gateway. The gateway will be select by default.', 'webinane-commerce' )),
				
			)
		) ) )
	),
	array(
		'title'			=> esc_html__( 'Display', 'webinane-commerce' ),
		'icon'			=> 'fa fa-laptop',
		'id'			=> 'display_settings',
		'fields'		=> apply_filters( 'webinane_commerce/settings/display_settings', array(
			
			Select::make(esc_html__('Checkout Page', 'webinane-commerce'), 'checkout_page')
				->setOptions(wpcm_posts_data( array( 'post_type' => 'page', 'posts_per_page' => 100 ) ))
				->setHelp(esc_html__( 'Choose the checkout page', 'webinane-commerce' )),

			Select::make(esc_html__('Order Success Page', 'webinane-commerce'), 'success_page')
				->setOptions(wpcm_posts_data( array( 'post_type' => 'page', 'posts_per_page' => 100 ) ))
				->setHelp(esc_html__( 'Choose the to show when an order is successfull', 'webinane-commerce' )),

			Select::make(esc_html__('My Account Page', 'webinane-commerce'), 'my_account_page')
				->setOptions(wpcm_posts_data( array( 'post_type' => 'page', 'posts_per_page' => 100 ) ))
				->setHelp(esc_html__( 'Choose the my account page', 'webinane-commerce' )),

			Switcher::make(esc_html__('Redirect to Checkout', 'webinane-commerce'), 'redirect_to_checkout')
				->setHelp(esc_html__( 'Redirect user to checkout page after add to cart', 'webinane-commerce' )),
		) )
	),
	array(
		'title'			=> esc_html__( 'Emails', 'webinane-commerce' ),
		'icon'			=> 'fa fa-envelope',
		'id'			=> 'emails_settings',
		'children'		=> array(
			apply_filters( 'webinane_commerce/settings/customer_email_settings', array(
				'id'	=> 'customer_email_settings',
				'title'	=> esc_html__('Customer Email', 'webinane-commerce'),
				'heading'	=> esc_html__('Email Setting for Customers', 'webinane-commerce'),
				'fields'	=> array(
					
					Text::make(__('Subject', 'webinane-commerce'), 'customer_email_subject')
						->setHelp(__( 'Enter the subject for customer\'s email. You can use placeholders <pre>{{customer_name}} {{customer_email}} {{site_name}} {{site_url}}</pre>  <pre>{{admin_email}} {{customer_account_url}} {{admin_order_url}}</pre> <pre>{{total_amount}}</pre>' , 'webinane-commerce' )),
					Media::make(esc_html__('Header Logo', 'webinane-commerce'), 'customer_email_header_logo')
						->setAddText(esc_html__( 'Add Logo', 'webinane-commerce' ))
						->setUpdateText(esc_html__( 'Change Logo', 'webinane-commerce' ))
						->setHelp(esc_html__( 'Choose the logo you want to show in the email header', 'webinane-commerce' )),
					Media::make(esc_html__('Footer Logo', 'webinane-commerce'), 'customer_email_footer_logo')
						->setAddText(esc_html__( 'Add Logo', 'webinane-commerce' ))
						->setUpdateText(esc_html__( 'Change Logo', 'webinane-commerce' ))
						->setHelp(esc_html__( 'Choose the logo you want to show in the email footer', 'webinane-commerce' )),
					Text::make(esc_html__('Greeting Text', 'webinane-commerce'), 'customer_email_greeting_text')
						->default('Thanks for your Donation!')
						->setHelp(esc_html__( 'Enter the greeting text of the email', 'webinane-commerce' )),
					Textarea::make(esc_html__('Email Body', 'webinane-commerce'), 'customer_email_body')
						->setHelp(esc_html__( 'You can use HTML Tags.', 'webinane-commerce' )),
					Textarea::make(esc_html__('Footer Text', 'webinane-commerce'), 'customer_email_footer_text')
						->default(sprintf('<p>%s</p>', get_bloginfo('name')))
						->setHelp(esc_html__( 'Enter the text you want to show in footer', 'webinane-commerce' )),
					Switcher::make(esc_html__('Show Quantity', 'webinane-commerce'), 'customer_email_show_qty')
						->default(true)
						->setHelp(esc_html__( 'Whether to show the total quantity in email', 'webinane-commerce' )),
					Switcher::make(esc_html__('Show Customer Address', 'webinane-commerce'), 'customer_email_show_address')
						->default(true)
						->setHelp(esc_html__( 'Whether to show the customer address detail in email', 'webinane-commerce' )),
					Switcher::make(esc_html__('Show Item Detail', 'webinane-commerce'), 'customer_email_show_item_info')
						->default(true)
						->setHelp(esc_html__( 'Whether to show the item or donation detail in email', 'webinane-commerce' )),
					
				)
			)),
			apply_filters( 'webinane_commerce/settings/owner_email_settings', array(
				'id'	=> 'owner_email_settings',
				'title'	=> esc_html__('Admin Email', 'webinane-commerce'),
				'heading'	=> esc_html__('Email Setting for Admin', 'webinane-commerce'),
				'fields'	=> array(
					
					Text::make(__('Subject', 'webinane-commerce'), 'admin_email_subject')
						->setHelp(__( 'Enter the subject for admin\'s email. You can use placeholders <pre>{{customer_name}} {{customer_email}} {{site_name}} {{site_url}}</pre>  <pre>{{admin_email}} {{customer_account_url}} {{admin_order_url}}</pre> <pre>{{total_amount}}</pre>' , 'webinane-commerce' )),
					Text::make(esc_html__('Greeting Text', 'webinane-commerce'), 'admin_email_greeting_text')
						->default('Thanks for your Donation!')
						->setHelp(esc_html__( 'Enter the greeting text of the email', 'webinane-commerce' )),
					Textarea::make(esc_html__('Email Body', 'webinane-commerce'), 'admin_email_body')
						->setHelp(esc_html__( 'You can use HTML Tags.', 'webinane-commerce' )),
					Textarea::make(esc_html__('Footer Text', 'webinane-commerce'), 'admin_email_footer_text')
						->default(sprintf('<p>%s</p>', get_bloginfo('name')))
						->setHelp(esc_html__( 'Enter the text you want to show in footer', 'webinane-commerce' )),					
				)
			)),
		)
	),
	
);
