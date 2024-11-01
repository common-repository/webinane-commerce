
<div class="wpcm-gateways">
	
	<?php foreach( $names as $name ) : ?>
		
		<?php 
			if ( ! wpcm_is_active_gateway($name, $gateways) ) {
				continue;
			} 
		?>

		<div id="wpcm_payment_gateway-<?php echo esc_attr( $name ); ?>">
			<label for="wpcm_payment_gateway_field-<?php echo esc_attr( $name ); ?>">
				<input type="radio" id="wpcm_payment_gateway_field-<?php echo esc_attr( $name ); ?>" name="wpcm_payment_gateway" value="<?php echo esc_attr( $name ) ?>">
				<?php echo esc_attr( webinane_set( $gateways, $name . '_title' ) ); ?>
				<small><?php echo esc_attr( webinane_set( $gateways, $name . '_description' ) ); ?></small>
			</label>
		</div>
	<?php endforeach; ?>
</div>