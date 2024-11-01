<div class="wpcm-order-review">
	<div class="wpcm-edit-order">
		<h4><?php esc_html_e('Your Order', 'webinane-commerce' ); ?></h4>
	</div>
	<ul class="wpcm-order-review-table">

		<li class="wpcm-cart-subtotal wpcm-tbl-list">
			<span class="wpcm-tbl-col1"><?php esc_html_e('Subtotal', 'webinane-commerce' ); ?></span>
			<span class="wpcm-product-price wpcm-tble-col2">{{cart.symbol}}{{ cartTotal() }}</span>	
		</li>
		<li class="wpcm-order-total">
			<span>Total</span>
			<span class="wpcm-primary-colr">{{cart.symbol}}{{ cartTotal() }}</span>
		</li>
	</ul>
	<div class="wpcm-checkout-payment">
		<?php 
			$gateways = wpcm_get_active_gateways();
			$count = 0;
		?>
		<ul class="wpcm-payment-methods">
			<?php foreach( $gateways as $gateway ) : ?>

				<?php 
					if ( ! $gateway->is_active() ) {
						continue;
					}
					$checked = ($count == 0) ? true : false;
				?>

				<li>
					<label class="wpcm-custom-radio wpcm-bank-radio">
						<?php echo esc_attr( $gateway->getTitle() ) ?>
					  	<input type="radio" name="payment" <?php checked( $checked, true ) ?> value="<?php echo esc_attr( $gateway->id ) ?>" v-model="payment_method">
					  	<span class="wpcm-radiomark"></span>
					</label>
					<div id="test-<?php echo esc_attr( $gateway->id ) ?>" class="wpcm-payment-box">
						<p><?php echo wp_kses_post( $gateway->getDesc() ) ?>
						</p>
					</div>
				</li>

				<?php $count++ ?>
			<?php endforeach; ?>
		</ul>
		<?php do_action('webinane_checkout_payment_gateway_data') ?>
		<div class="wpcm-place-order">
			<a href="#" title="<?php esc_html_e('Place Order', 'webinane-commerce' ); ?>" @click.prevent="checkout">
				<?php esc_html_e('Place Order', 'webinane-commerce' ); ?>
				<i class="wpcm-ajax-processing white" v-show="loading"></i>
			</a>
		</div>
	</div>
</div>


