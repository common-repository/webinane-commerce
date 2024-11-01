<?php
/**
 * Offline order
 *
 * @package WordPress
 */
$settings = wpcm_get_settings();
$offline_gateway = array_get($settings, 'gateways.offline_gateway');
$offline_gateway = ($offline_gateway) ? $offline_gateway : $settings;
$description = array_get($offline_gateway, 'offline_payment_description' );
$instructions = array_get($offline_gateway, 'offline_payment_instruction' );

$total = get_post_meta( $order->ID, '_wpcm_order_total', true );

$currency = get_post_meta( $order->ID, '_order_currency', true );
$order_data = WebinaneCommerce\Classes\Orders::order_data( $order );

$orders_label = apply_filters( 'wpcm_orders_admin_menu_label', esc_html__( 'Orders', 'webinane-commerce' ) );
$order_label = apply_filters( 'wpcm_order_admin_menu_label', esc_html__( 'Order', 'webinane-commerce' ) )
?>

<div class="wpcm-order-success-detail wpcm-wrapper alignfull">
	<h3>
		<?php
			// translators: The orders label.
			printf( esc_html__( '%s Detail', 'webinane-commerce' ), esc_html( $order_label ) );
		?>
	</h3>

	<table class="table">
		<tbody>
			<tr>
				<th><?php esc_html_e( 'Description', 'webinane-commerce' ); ?></th>
				<td><?php echo wp_kses_post( $description ); ?></td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Gateway', 'webinane-commerce' ); ?></th>
				<td><?php esc_html_e( 'Offline', 'webinane-commerce' ); ?></td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Transaction Status', 'webinane-commerce' ); ?></th>
				<td><?php echo esc_attr( ucwords( $order->post_status ) ); ?></td>
			</tr>
			<tr>
				<th>
					<?php
					// translators: Order label.
					printf( esc_html__( '%s Total', 'webinane-commerce' ), esc_attr( $order_label ) );
					?>
				</th>
				<td><?php echo esc_attr( $total ); ?></td>
			</tr>
			<?php if ( $currency ) : ?>
				<tr>
					<th><?php esc_html_e( 'Currency', 'webinane-commerce' ); ?></th>
					<td><?php echo esc_attr( $currency ); ?></td>
				</tr>
			<?php endif; ?>
		</tbody>
	</table>

	<h4><?php esc_html_e( 'Offline Payment Instructions', 'webinane-commerce' ); ?></h4>
	<p><?php echo wp_kses_post( $description ); ?></p>
	<p><?php echo wp_kses_post( $instructions ); ?></p>

	<?php if ( isset( $order_data->order_items ) ) : ?>
		<table class="table table-bordered">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Item Name', 'webinane-commerce' ); ?></th>
					<th><?php esc_html_e( 'Price', 'webinane-commerce' ); ?></th>
					<th><?php esc_html_e( 'Quantity', 'webinane-commerce' ); ?></th>
					<th><?php esc_html_e( 'Total', 'webinane-commerce' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $order_data->order_items as $item ) : ?>
					<tr>
						<td><?php echo esc_attr( $item->order_item_name ); ?></td>
						<td><?php echo esc_attr( $item->price ); ?></td>
						<td><?php echo esc_attr( $item->qty ); ?></td>
						<td><?php echo wp_kses( webinane_cm_price_with_symbol( $item->qty * $item->price ), wp_kses_allowed_html( 'post' ) ); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>
</div>
