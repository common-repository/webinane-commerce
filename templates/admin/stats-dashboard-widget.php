<div class="wpcm-wrapper">
	<div class="wpcm-month-net-sale">
		<div class="wpcm-row">
			<div class="wpcm-col-sm-4">
				<a href="#">
					<i class="dashicons dashicons-chart-bar"></i>
					<?php echo esc_attr( webinane_set( $stats, 'total') ) ?>
				</a>
				<small><?php echo apply_filters( 'wpcommerce_dashboard_stats_widget_sales_text', esc_html__('Sales this month', 'webinane-commerce') ) ?></small>
			</div>
			<div class="wpcm-col-sm-4">
				<a href="#">
					<i class="dashicons dashicons-migrate"></i>
					<?php
						$pend = webinane_set( $stats, 'pending'); 
						printf(_n('%d Order', '%d Orders', $pend, 'webinane-commerce' ), esc_attr( $pend ) ) 
					?>
				</a>
				<small><?php echo apply_filters( 'wpcommerce_dashboard_stats_widget_pending_text', esc_html__('Pending Transactions', 'webinane-commerce') ) ?></small>
			</div>
			<div class="wpcm-col-sm-4">
				<a href="#">
					<i class="dashicons dashicons-warning"></i>
					<?php
						$pend = webinane_set( $stats, 'hold'); 
						printf(_n('%d Order', '%d Orders', $pend, 'webinane-commerce' ), esc_attr( $pend ) ) 
					?>
				</a>
				<small><?php echo apply_filters( 'wpcommerce_dashboard_stats_widget_hold_text', esc_html__('Transactions on Hold', 'webinane-commerce') ) ?></small>
			</div>
		</div>
	</div>
</div>