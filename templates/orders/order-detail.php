<?php
	global $wpdb; 
	if( ! $order ) {
		return;
	} 
?>
<div class="success-page-orders">
	
	<h3><?php esc_html_e('Order Detail', 'webinane-commerce' ); ?></h3>
	<table class="wpcm-table">
		<thead>
			<tr>
				<th><?php esc_html_e('Item Name', 'webinane-commerce' ); ?></th>
				<th><?php esc_html_e('Quantity', 'webinane-commerce' ); ?></th>
				<th><?php esc_html_e('Price', 'webinane-commerce' ); ?></th>
				<th><?php esc_html_e('Total', 'webinane-commerce' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach( $order->order_items as $order_item ) : ?>
				<tr>
					<td><?php echo esc_attr( $order_item->order_item_name ) ?></td>
					<td><?php echo esc_attr( $order_item->itemmeta['quantity'] ) ?></td>
					<td><?php echo esc_attr( round( $order_item->itemmeta['price'] / $order_item->itemmeta['quantity'], 2 ) ) ?></td>
					<td><?php echo esc_attr( $order_item->itemmeta['price'] ) ?></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>