<?php $uniqid = uniqid(); ?>
<?php if ( $enable_ajax ) : ?>
	<div class="wpcm-add-cart-button button-ajax">

		<a href="javascript:void(0);" 
		  id="<?php echo esc_attr($uniqid) ?>"
		  data-id="<?php echo esc_attr( $item_id ) ?>" 
		  data-quantity="<?php echo esc_attr( $quantity ) ?>"
		  data-price="<?php echo esc_attr( $price ) ?>"
		  class="<?php echo esc_attr( $class ) ?> wpmc_add_to_cart ajax_add_to_cart">
			<?php echo wp_kses_post( $content ); ?>  	
		</a>
		<i class="wpcm-ajax-processing hide"></i>
	</div>
<?php else : ?>
	<form action="" method="post" enctype="">
		<input type="hidden" name="item_id" <?php echo esc_attr( $item_id ) ?>>
		<input type="hidden" name="quantity" <?php echo esc_attr( $quantity ) ?>>
		<input type="hidden" name="price" <?php echo esc_attr( $price ) ?>>

		<button type="submit" class="<?php echo esc_attr( $class ) ?> wpmc_add_to_cart"><?php echo wp_kses_post( $content ) ?></button>
	</form>
<?php endif; ?>

<?php 
$data = array( $uniqid => array(
	'item_id'	=> $item_id,
	'quantity'	=> $quantity,
	'price'		=> $price,
	'nonce'		=> wp_create_nonce( WPCM_GLOBAL_KEY )
) );
wp_localize_script( 'wpcm-add-to-cart', 'wpcm_cart_button', $data );
