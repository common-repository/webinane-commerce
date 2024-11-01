<?php 
	$has_items = wpcm_get_cart_content(); 
	$dis_class = ($has_items) ? 'none' : 'block';
?>
<div class="wpcm-wrapper wpcm-sec-mrg wpcm-checkout-wrapper alignfull">
	<div class="wpcm-container" v-loading="loading" v-show="isLoaded" style="display: <?php echo esc_attr($dis_class) ?>">
		<?php if( $has_items ) : ?>
			<div class="wpcm-checkout" >
				<div class="wpcm-row">
					<div class="wpcm-col-md-12 wpcm-col-lg-7">
						<div class="wpcm-billing-area">
							<?php include WNCM_PATH . 'templates/checkout/billing.php' ?>
							<?php include WNCM_PATH . 'templates/checkout/shipping.php' ?>
						</div>
					</div>
					<div class="wpcm-col-md-12 wpcm-col-lg-5">
						<front-checkout-order v-on:save_changed="checkout">
							<?php include WNCM_PATH . 'templates/checkout/orders.php' ?>
						</front-checkout-order>
					</div>
				</div>
				<front-checkout-items>
					<?php include WNCM_PATH . 'templates/checkout/items.php' ?>
				</front-checkout-items>
				
			</div>
		<?php else : ?>
			<div class="alert alert-info">
				<p><?php esc_html_e('Your cart is empty, please visit shop to to purchase items', 'webinane-commerce') ?></p>
			</div>
		<?php endif; ?>
	</div>
</div>
