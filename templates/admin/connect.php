<?php
$connect = get_option('webinane_commerce_user_connect');
$expires = array_get($connect, 'expires_in');
$token = array_get($connect, 'access_token');
$plugins = array_keys(get_plugins());
$active_plugs = array_values(get_option('active_plugins'));

?>
<h3 class="hndle" style="display: none;"><?php esc_html_e('Connect', 'webinane-commerce') ?></h3>


<div id="wpcm-admin-live-connect">
	<?php if($token && $expires > time() ) : ?>
		<connect
		  inline-template
		  nonce="<?php echo wp_create_nonce( WPCM_AJAX_ACTION ) ?>"
		  :active_plugins='<?php echo json_encode($active_plugs) ?>'
		  :inst_plugins='<?php echo json_encode($plugins) ?>'
		  :is_active="true">
			<div v-show="loaded" style="display: none; max-width: 95%;">
				<div class="extensions-head" style="width: 100%;display: block;position: relative;">
					<h3 style="margin-bottom: 30px;"><?php esc_html_e('Our Extensions', 'webinane-commerce') ?></h3>
					<el-button @click="login_out()" :loading="loading" style="position: absolute; top: -8px; left: 140px;" type="danger"><?php esc_html_e('Disconnect', 'webinane-commerce'); ?></el-button>
				</div>

				<el-row :gutter="30" v-loading="loading">
					<el-col :span="6" v-for="item in items" :style="{marginBottom: '30px'}">
						<el-card :body-style="{ padding: '0px' }">
					      <img :src="item.thumb" class="image">
					      <div style="padding: 14px;">
					        <h4>{{ item.title }}</h4>
					        <div class="bottom clearfix">
					          <span class="price">$ {{ item.price }}</span>
					          
					          <a v-if="is_purchased(item.wp_slug) && !is_installed(item.wp_slug) " :href="item.url" target="_blank" class="button " @click.prevent="installPlugin(item)">
					          	<?php esc_html_e('Install', 'webinane-commerce') ?>
					          </a>
					          <a v-else-if="is_installed(item.wp_slug) && !plugin_active(item.wp_slug)" :href="item.url" target="_blank" class="button" @click.prevent="activatePlugin(item)">
					          	<?php esc_html_e('Activate', 'webinane-commerce') ?>
					          </a>
					          <a v-else-if="!is_purchased(item.wp_slug) && !is_installed(item.wp_slug) " :href="item.url" target="_blank" class="button">
					          	<?php esc_html_e('Buy Now', 'webinane-commerce') ?>
					          </a>

					        </div>
					      </div>
					    </el-card>
					</el-col>
				</el-row>
			</div>
		</connect>
	<?php else:  ?>
		<connect inline-template nonce="<?php echo wp_create_nonce( WPCM_AJAX_ACTION ) ?>" :is_active="false">
			<el-row>
				<el-col :span="10">
					<el-card header="<?php esc_html_e('Login in to Connect', 'webinane-commerce') ?>" v-loading="loading">
						<el-form :model="form">
							<el-form-item>
								<el-input v-model="form.email" placeholder="<?php esc_html_e('Email', 'webinane-commerce') ?>"></el-input>
							</el-form-item>
							<el-form-item>
								<el-input placeholder="<?php esc_html_e('Password', 'webinane-commerce') ?>" type="password" v-model="form.password"></el-input>
							</el-form-item>
							<el-row :gutter="30">
								<el-col :span="12">
									<?php printf( __('Don\'t have account? <a href="%s" target="_blank">Create one</a>', 'webinane-commerce'), 'https://www.webinane.com/register') ?>
								</el-col>
								<el-col :span="12" :style="{textAlign: 'right'}">
									<el-button @click="login()" type="primary"><?php esc_html_e('Login', 'webinane-commerce') ?></el-button>
								</el-col>
							</el-row>
						</el-form>
					</el-card>
				</el-col>
			</el-row>
		</connect>
	<?php endif; ?>
</div>
