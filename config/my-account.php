<?php

return array(

	array(
		'title'			=> esc_html__( 'Personal Profile', 'webinane-commerce' ),
		'icon'			=> 'fa fa-user-alt',
		'id'			=> 'profile_settings',
		'component'		=> 'myaccount-profile',
		'fields'		=> apply_filters( 'webinane_frontend_my_account_profile', array(
			array(
				'name'		 => esc_html__( 'Profile Image', 'webinane-commerce' ),
				'desc'       => esc_html__( 'JPG, GIF or PNG 100x100 px', 'webinane-commerce' ),
				'id'         => 'profile_image',
				'type'       => 'media',
				'is'       	 => 'wpcm-image',
				'main_heading' => esc_html__( 'Personal Information', 'webinane-commerce' ),
				'options' => array(
					'new' => esc_html( 'Add Image', 'webinane-donation' ),
					'update' => esc_html__( 'Update Image', 'webinane-commerce' )
				),
				'value_key'		=> 'user.data.meta.avatar'
			),
			array(
				'name'       => esc_html__( 'Account Name', 'webinane-commerce' ),
				'desc'       => esc_html__( 'Enter the account name', 'webinane-commerce' ),
				'id'         => 'account_name',
				'type'       => 'text',
				'is'	     => 'wpcm-text',
				'value_key'		=> 'user.data.user_login'
			),
			array(
				'name'       => esc_html__( 'Email Address', 'webinane-commerce' ),
				'desc'       => esc_html__( 'Enter the email Address', 'webinane-commerce' ),
				'id'         => 'email_address',
				'type'       => 'text',
				'is'	     => 'wpcm-text',
				'value_key'		=> 'user.data.user_email'
			),
			array(
				'name'       => esc_html__( 'Password', 'webinane-commerce' ),
				'desc'       => esc_html__( 'Enter the new password if you want to change', 'webinane-commerce' ),
				'desc_confirm'=> esc_html__( 'Enter the password again to confirm whether it is correct', 'webinane-commerce' ),
				'id'         => 'password',
				'type'       => 'password',
				'is'	     => 'wpcm-password',
			),
			array(
				'name'       => esc_html__( 'Website', 'webinane-commerce' ),
				'desc'       => esc_html__( 'Enter the website url', 'webinane-commerce' ),
				'id'         => 'website',
				'type'       => 'text',
				'is'	     => 'wpcm-text',
				'value_key'		=> 'user.data.user_url'
			),
			array(
				'name'       => esc_html__( 'Author Bio', 'webinane-commerce' ),
				'desc'       => esc_html__( 'Short Description of your Self.', 'webinane-commerce' ),
				'id'         => 'author_bio',
				'type'       => 'textarea',
				'is'	     => 'wpcm-textarea',
				'default'	 => '.',
				'value_key'		=> 'user.data.meta.description'
			),
			// Billing Information
			array(
				'name'		 => esc_html__( 'First Name', 'webinane-commerce' ),
				'desc'       => esc_html__( 'Enter the first name', 'webinane-commerce' ),
				'id'         => 'first_name',
				'type'       => 'text',
				'is'       	 => 'wpcm-text',
				'main_heading' => esc_html__( 'Billing Information', 'webinane-commerce' ),
				'value_key'		=> 'user.data.meta.first_name'
			),
			array(
				'name'		 => esc_html__( 'Last Name', 'webinane-commerce' ),
				'desc'       => esc_html__( 'Enter the last name', 'webinane-commerce' ),
				'id'         => 'last_name',
				'type'       => 'text',
				'is'       	 => 'wpcm-text',
				'value_key'		=> 'user.data.meta.last_name'
			),
			array(
				'name'		 => esc_html__( 'Company Name', 'webinane-commerce' ),
				'desc'       => esc_html__( 'Enter the company name', 'webinane-commerce' ),
				'id'         => 'company_name',
				'type'       => 'text',
				'is'       	 => 'wpcm-text',
				'value_key'		=> 'customer.meta.billing_company'
			),
			array(
				'name'       => esc_html__( 'Country', 'webinane-commerce' ),
				'desc'       => esc_html__( 'Choose the base country', 'webinane-commerce' ),
				'id'         => 'billing_country',
				'type'       => 'multi-select',
				'is'	     => 'wpcm-country',
				'default'	=> 'USA',
				'options'	 => [],
				'value_key'		=> 'customer.meta.billing_base_country'
			),

			array(
				'name'		 => esc_html__( 'Street Address', 'webinane-commerce' ),
				'desc'       => esc_html__( 'Enter the house number and street name', 'webinane-commerce' ),
				'id'         => 'street_address',
				'type'       => 'text',
				'is'       	 => 'wpcm-text',
				'value_key'		=> 'customer.meta.billing_address_line_1'
			),
			array(
				'name'		 => esc_html__( 'Street Address 2', 'webinane-commerce' ),
				'desc'       => esc_html__( 'Enter the house number and street name', 'webinane-commerce' ),
				'id'         => 'street_address_2',
				'type'       => 'text',
				'is'       	 => 'wpcm-text',
				'value_key'		=> 'customer.meta.billing_address_line_2'
			),
			array(
				'name'		 => esc_html__( 'Town And City', 'webinane-commerce' ),
				'desc'       => esc_html__( 'Enter the name of town and city', 'webinane-commerce' ),
				'id'         => 'town_city',
				'type'       => 'text',
				'is'       	 => 'wpcm-text',
				'value_key'		=> 'customer.meta.billing_city'
			),
			array(
				'name'       => esc_html__( 'Postcode / ZIP', 'webinane-commerce' ),
				'desc'       => esc_html__( 'Enter the postcode or ZIP', 'webinane-commerce' ),
				'id'         => 'zip',
				'type'       => 'text',
				'is'	     => 'wpcm-text',
				'value_key'		=> 'customer.meta.billing_zip'
			),
			array(
				'name'       => esc_html__( 'Phone No', 'webinane-commerce' ),
				'desc'       => esc_html__( 'Enter the Phone No', 'webinane-commerce' ),
				'id'         => 'phone_no',
				'type'       => 'text',
				'is'	     => 'wpcm-text',
				'value_key'		=> 'customer.meta.billing_phone'
			),
			// Social profiles
			array(
				'name'       => esc_html__( 'Facebook', 'webinane-commerce' ),
				'desc'       => esc_html__( 'Enter the facebook url', 'webinane-commerce' ),
				'id'         => 'facebook',
				'type'       => 'text',
				'is'	     => 'wpcm-text',
				'main_heading' => esc_html__( 'Social Profiles', 'webinane-commerce' ),
				'value_key'		=> 'user.data.meta.facebook'
			),
			array(
				'name'       => esc_html__( 'Twitter', 'webinane-commerce' ),
				'desc'       => esc_html__( 'Enter the twitter url', 'webinane-commerce' ),
				'id'         => 'twitter',
				'type'       => 'text',
				'is'	     => 'wpcm-text',
				'value_key'		=> 'user.data.meta.twitter'
			),
			array(
				'name'       => esc_html__( 'Linkedin', 'webinane-commerce' ),
				'desc'       => esc_html__( 'Enter the linkedin url', 'webinane-commerce' ),
				'id'         => 'linkedin',
				'type'       => 'text',
				'is'	     => 'wpcm-text',
				'value_key'		=> 'user.data.meta.linkedin'
			),
			array(
				'name'       => esc_html__( 'Pinterest', 'webinane-commerce' ),
				'desc'       => esc_html__( 'Enter the pinterest url', 'webinane-commerce' ),
				'id'         => 'pinterest',
				'type'       => 'text',
				'is'	     => 'wpcm-text',
				'value_key'		=> 'user.data.meta.pinterest'
			),
		))
	),
	array(
		'title'			=> apply_filters( 'wpcm_orders_admin_menu_label', esc_html__( 'My Orders', 'webinane-commerce' ) ),
		'icon'			=> 'fa fa-user-alt',
		'id'			=> 'profile_settings',
		'component'		=> 'myaccount-orders',
		'fields'		=> apply_filters( 'webinane_frontend_my_account_orders', array(
			array()
		))
	),

	array(
		'title'			=> esc_html__( 'Payment Methods', 'webinane-commerce' ),
		'icon'			=> 'fa fa-dollar-sign',
		'id'			=> 'payment_methods_settings',
		'component'		=> 'myaccount-payment-methods',
		'fields'		=> apply_filters( 'webinane_frontend_may_account_payment_methods', array(
			array()
		))
	)
);
