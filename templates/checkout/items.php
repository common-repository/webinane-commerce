
<section class="wpcm-sec wpcm-cart-sec" v-if="items">
	<div class="wpcm-cart-table">
		<div class="wpcm-table-responsive">
			<table class="wpcm-table">
				<thead class="wpcm-sec-titlebar wpcm-cart-heading">
		            <tr class="wpcm-d-flex">
		                <th class="wpcm-col-6"><?php esc_html_e('Item', 'webinane-commerce' ); ?></th>
		                <th class="wpcm-col-2"><?php esc_html_e('Cost', 'webinane-commerce' ); ?></th>
		                <th class="wpcm-col-2"><?php esc_html_e('Qty', 'webinane-commerce' ); ?></th>
		                <th class="wpcm-col-2"><?php esc_html_e('Total', 'webinane-commerce' ); ?></th>
		            </tr>
		        </thead>
		        <tbody>
		            <tr class="wpcm-d-flex wpcm-cart-item" v-for="(item, index) in items">
		               <td class="wpcm-col1">
							<div class="wpcm-cart-img">
								<a :href="item.link" :title="item.title" class="wpcm-secondary-colr"><i class="fa fa-times"></i></a>
								<img :src="item.thumb[0]" alt="">
							</div>
							<div class="wpcm-item-title">
								<span>
									<a :href="item.link" :title="item.title">{{ item.title }}</a>
								</span>
							</div>
						</td>
						<td class="wpcm-col2"><span class="wpcm-price">{{cart.symbol}}{{ item.price }}</span></td>
						<td class="wpcm-col2">
							<form class="qty-select">
								<el-input-number v-model="item.qty" size="small" @change="updateItems"></el-input-number>
							</form>
						</td>
						<td class="wpcm-col2"><span class="wpcm-price-total">{{cart.symbol}} {{ (item.price*item.qty) }}</span></td>
		            </tr>
		            
		        </tbody>
		        
			</table>
		</div>
	</div>
</section>