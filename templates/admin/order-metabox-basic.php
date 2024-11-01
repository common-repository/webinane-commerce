<?php $address_fields = array(
	'address_line_1' 	=> esc_html__( 'Address Line 1', 'webinane-commerce' ),
	'address_line_2' 	=> esc_html__( 'Address Line 2', 'webinane-commerce' ),
	'city' 				=> esc_html__( 'City', 'webinane-commerce' ),
	'base_country'		=> esc_html__( 'Country', 'webinane-commerce' ),
	'zip'				=> esc_html__( 'Zip Code', 'webinane-commerce' ),
); ?>

<div class="wpcm-order-detail wpcm-wrapper">
	
	<notif v-on:done="onDone" :result="result" :type="result_type" :msg="result_msg"></notif>
	<div class="wpcm-content" v-loading="loading">
		<general title="<?php esc_html_e('Order Action', 'webinane-commerce' ) ?>"></general>
		<div class="wpcm-row">
			<div class="wpcm-col-sm-6">
				<billing-address />
			</div>
			<div class="wpcm-col-sm-6">
				<shipping-address />
			</div>
		</div>

		<order-items></order-items>
		<order-notes></order-notes>
	</div>
</div>