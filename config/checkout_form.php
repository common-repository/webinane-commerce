<?php

return array(

	array(
		'title'			=> esc_html__( 'WP Commerce', 'webinane-commerce' ),
		'id'			=> 'wpcommerce_frontend_checkout_form_customer_info',
		'object_types'	=> array('none'),
		'hookup'		=> false,
		'save_fields'	=> false,
		'fields'		=> apply_filters( 'wpcm_frotend_checkout_form_cutomer_info', array(
			array(
				'name'       => esc_html__( 'First Name', 'webinane-commerce' ),
				'desc'       => esc_html__( 'Enter the first name', 'webinane-commerce' ),
				'id'         => 'first_name',
				'type'       => 'text',
			),
			array(
				'name'       => esc_html__( 'Last Name', 'webinane-commerce' ),
				'desc'       => esc_html__( 'Please enter your last name', 'webinane-commerce' ),
				'id'         => 'last_name',
				'type'       => 'text',
			),
			array(
				'name'       => esc_html__( 'Address Line 1', 'webinane-commerce' ),
				'desc'       => esc_html__( 'Enter the store address', 'webinane-commerce' ),
				'id'         => 'address_line_1',
				'type'       => 'text',
			),
			array(
				'name'       => esc_html__( 'Address Line 2', 'webinane-commerce' ),
				'desc'       => esc_html__( 'Enter the store address', 'webinane-commerce' ),
				'id'         => 'address_line_2',
				'type'       => 'text',
			),
			array(
				'name'       => esc_html__( 'City', 'webinane-commerce' ),
				'desc'       => esc_html__( 'Enter the store city', 'webinane-commerce' ),
				'id'         => 'city',
				'type'       => 'text',
			),
			array(
				'name'       => esc_html__( 'Base Country', 'webinane-commerce' ),
				'desc'       => esc_html__( 'Choose the base country', 'webinane-commerce' ),
				'id'         => 'base_country',
				'type'       => 'select',
				'options'	 => wpcm_countries()->toArray()
			),
			array(
				'name'       => esc_html__( 'Postcode / ZIP', 'webinane-commerce' ),
				'desc'       => esc_html__( 'Enter the postcode or ZIP', 'webinane-commerce' ),
				'id'         => 'zip',
				'type'       => 'text',
			),

		))
	),
);
