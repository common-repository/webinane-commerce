<front-shipping-address>
	<div class="wpcm-checkout-biling" v-show="ship_diff">
		<h4 class="wpcm-order-detail-header"><?php esc_html_e('Shipping Address', 'webinane-commerce' ) ?></h4>
		<div class="wpcm-checkout-biling-form">
			<form action="">
				<div class="wpcm-checkout-biling-group wpcm-name-field">
					<label><?php esc_html_e( 'Complete Name', 'webinane-commerce' ) ?></label>
					<input type="text" class="wpcm-form-input" v-model="shipping_fields.first_name"  placeholder="<?php esc_html_e( 'First Name', 'webinane-commerce' ); ?>">
					<input type="text" class="wpcm-form-input" v-model="shipping_fields.last_name" placeholder="<?php esc_html_e( 'Last Name', 'webinane-commerce' ); ?>">
				</div>
				<div class="wpcm-checkout-biling-group">
					<label><?php esc_html_e( 'Company Name', 'webinane-commerce' ) ?></label>
					<input type="text" name="" class="wpcm-form-input" v-model="shipping_fields.company">
				</div>
				<div class="wpcm-checkout-biling-group">
					<label><?php esc_html_e( 'Country', 'webinane-commerce' ) ?></label>
					<div class="wpcm-custom-select">
						<select class="wpcm-form-input" v-model="shipping_fields.base_country">
							<option v-for="(country, country_id) in countries" :value="country_id">{{ country }}</option>
						</select>
					</div>
				</div>
				<div class="wpcm-checkout-biling-group">
					<label><?php esc_html_e( 'Street Address', 'webinane-commerce' ); ?></label>
					<input type="text"class="wpcm-form-input" placeholder="<?php esc_html_e( 'House Number and Street Name', 'webinane-commerce' ); ?>" v-model="shipping_fields.address_line_1">
					<input type="text" class="wpcm-form-input" placeholder="<?php esc_html_e( 'Apartment, suits, unit etc. (options)', 'webinane-commerce' ); ?>" v-model="shipping_fields.address_line_2">
				</div>
				<div class="wpcm-checkout-biling-group">
					<label><?php esc_html_e( 'Town and City', 'webinane-commerce' ) ?></label>
					<input type="text" class="wpcm-form-input" placeholder="<?php esc_html_e( 'House Number and Street Name', 'webinane-commerce' ); ?>" v-model="shipping_fields.city">
				</div>
				<div class="wpcm-checkout-biling-group">
					<label><?php esc_html_e( 'State / Country', 'webinane-commerce' ) ?></label>
					<input type="text" class="wpcm-form-input" placeholder="<?php esc_html_e( 'State / Country', 'webinane-commerce' ); ?>" v-model="shipping_fields.state">
				</div>
				<div class="wpcm-checkout-biling-group">
					<label><?php esc_html_e( 'Postcode / Zip', 'webinane-commerce' ) ?></label>
					<input type="text" class="wpcm-form-input" v-model="shipping_fields.zip">
				</div>
				<div class="wpcm-checkout-biling-group">
					<label><?php esc_html_e( 'Phone No', 'webinane-commerce' ) ?></label>
					<input type="text" class="wpcm-form-input" v-model="shipping_fields.phone">
				</div>
			</form>
		</div>	
	</div>

</front-shipping-address>