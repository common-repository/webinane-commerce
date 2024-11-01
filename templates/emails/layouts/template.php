<?php 
	$settings = wpcm_get_settings();
	$new_order = wpcm_order($order);

	$items = $new_order->items(); 
	$item = $items->first();
	$meta = $new_order->meta->pluck('meta_value', 'meta_key');
	
	$customers = new \WebinaneCommerce\Classes\Customers($customer_id);
	$customer = $customers->customer;
	$orders_label = apply_filters('wpcm_orders_admin_menu_label', esc_html__('Orders', 'webinane-commerce'));
	$order_label = apply_filters('wpcm_order_admin_menu_label', esc_html__('Order', 'webinane-commerce'));
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
	<style type="text/css">
		@import url('https://fonts.googleapis.com/css?family=Barlow:400,500,600,700&display=swap');

		@media only screen and (max-width:480px){
			.emailImage{
				height:auto !important;
				max-width:100% !important;
				width: 100% !important;
			}
			#emailContainer,
			#emailBody {
				width: 100% !important;
			}
			.bodyColumn {
				width: auto !important;
				display: block !important;
			}
		}
	</style> 
</head>
<body style="margin: 0; padding: 0; background-color: #ededed;">
	<table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%" id="bodyTable" style="margin: 40px 0;">
		<tr>
			<td align="center" valign="top">
				<table border="0" cellpadding="0" cellspacing="0" width="535" id="emailContainer" style="box-shadow: 0 0 65px rgba(0, 0, 0, 0.08); border-radius: 12px;">
					<tr>
						<td align="center" valign="top">
							<table border="0" cellpadding="5" cellspacing="0" width="100%" id="emailHeader">
								<tr>
									<td align="center" valign="top">
										<img src="{{header_logo}}" alt="image" style="border-top-right-radius: 6px; border-top-left-radius: 6px;" class="emailImage" />
									</td>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td align="center" valign="top">
							<table border="0" cellpadding="35" cellspacing="0" width="100%" id="emailBody" bgcolor="#ffffff">
								<tr>
									<td align="left" valign="middle" width="55%" class="bodyColumn">
										<table border="0" cellpadding="0" cellspacing="0" width="100%">
											<tr>
												<td align="left" valign="top">
													<span style="font-family: 'Barlow', sans-serif; color: #8b8b8b; font-weight: 500; font-size: 15px;">{{price_label}}</span>
													<span style="font-family: 'Barlow', sans-serif; color: #1aa0e6; font-weight: 600; font-size: 20px;">{{order_total}}</span>
												</td>
											</tr>
											<?php if( $settings->get('customer_email_show_item_info') ) : ?>
												<tr>
													<td align="left" valign="top" width="100%" style="font-family: 'Barlow', sans-serif; color: #1a1a1a; font-weight: 600; font-size: 22px; padding-top: 15px; padding-bottom: 16px;">
														<?php echo get_the_title($item->post_id) ?>
													</td>
												</tr>
											<?php endif; ?>
											<tr>
												<td align="left" valign="top" style="font-family: 'Barlow', sans-serif; color: #999999; font-size: 17px; line-height: 30px;">
													{{email_body}}
												</td>
											</tr>
											<?php if( $customer->address && $settings->get('customer_email_show_address') ) : ?>
												<tr>
													<td align="left" valign="top" style="font-family: 'Barlow', sans-serif; color: #999999; font-size: 17px; line-height: 30px;">
														{{customer_address}}<br/> 
														{{customer_city}}<br/>
														{{customer_state}} {{customer_country}}
													</td>
												</tr>
											<?php endif; ?>
										</table>
									</td>
									<td align="left" valign="top" width="40%" class="bodyColumn">
										<table cellpadding="0" cellspacing="0" width="100%">
											<tr>
												<td align="left" valign="top">
													<table cellspacing="" cellpadding="22" width="100%" style="border: 3px solid #ededed; border-radius: 4px;" bgcolor="#ffffff">
														<tr>
															<td align="left" valign="top">
																<?php if($settings->get('customer_email_show_qty')) : ?>
																	<table cellpadding="8" border="0" cellspacing="0" width="100%">
																		<tr>
																			<td align="left" valign="top" style="font-family: 'Barlow', sans-serif; color: #666666; font-size: 15px;">
																				<?php esc_html_e('Quantity', 'webinane-commerce'); ?>:  
																			</td>
																			<td align="right" valign="top" style="font-family: 'Barlow', sans-serif; color: #666666; font-size: 15px;">
																				<?php echo esc_attr($new_order->all_qty) ?>
																			</td>
																		</tr>
																	</table>
																<?php endif; ?>
																<table cellspacing="8" boder="0" cellpadding="0" width="100%" style="border-top: 1px solid #ededed; margin-top: 14px; padding-top: 10px;">
																	<tr>
																		<td align="left" valign="top" style="font-family: 'Barlow', sans-serif; color: #1aa0e6; font-size: 15px; font-weight: 600;">
																			<?php esc_html_e('Total', 'webinane-commerce'); ?>: 
																		</td>
																		<td align="right" valign="top" style="font-family: 'Barlow', sans-serif; color: #1aa0e6; font-size: 15px; font-weight: 600;">
																			{{total_amount}}
																		</td>
																	</tr>
																</table>
															</td>
														</tr>
													</table>
													<table cellspacing="0" cellpadding="0" width="100%" style="background-color: #1aa0e6; border-radius: 3px; margin-top: 12px;">
														<tr>
															<td align="center" valign="middle" style="color:#FFFFFF; font-family: 'Barlow', sans-serif; font-size:14px; font-weight:bold; line-height:150%; padding-top:17px; padding-right:40px; padding-bottom:17px; padding-left:40px;">
																<a href="{{admin_order_url}}" target="_blank" style="color:#FFFFFF; text-decoration:none;"><?php printf(esc_html__('View %s', 'webinane-commerce'), $orders_label); ?></a>
															</td>
														</tr>
													</table>
												</td>
											</tr>
										</table>
									</td>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td align="left" valign="top">
							<table border="0" cellpadding="0" cellspacing="0" width="100%" id="emailFooter" bgcolor="#f4f4f4" style="border-bottom-right-radius: 12px; border-bottom-left-radius: 12px;">
								<tr>
									<td align="top" valign="middle" width="75%">
										<table border="0" cellpadding="40" cellspacing="0" width="100%">
											<tr>
												<td align="left" valign="top">
													<span style="color: #1b5e82; font-size: 15px; font-family: 'Barlow', sans-serif;">{{footer_text}}</span>
												</td>
											</tr>
										</table>
									</td>
									<td align="right" valign="middle" width="25%">
										<table border="0" cellpadding="30" cellspacing="20" width="100%">
											<tr>
												<td align="center" valign="middle" bgcolor="#ffffff">
													<img src="{{footer_logo}}" alt="{{site_name}}">
												</td>
											</tr>
										</table>
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
</body>
</html>