<?php 
 ?>
<div class="wpcm-wrapper alignfull">
	<div class="wpcm-my-account-wrapper">
		<div class="wpcm-dashboard-wrapper">
				<div class="wpcm-dashboard-tabs">
					<?php if ( is_user_logged_in() ) : ?>
						<myaccount-tabs></myaccount-tabs>
					<?php else: ?>
						<login-register-form 
						  inline-template 
						  nonce="<?php echo wp_create_nonce( WPCM_GLOBAL_KEY ) ?>"
						  success_string="<?php esc_html_e('Success', 'webinane-commerce') ?>"
						  error_string="<?php esc_html_e('Error', 'webinane-commerce') ?>">
							<div class="wpcm-login-register" v-loading="loading">
								<div class="row">
									<div class="col-md-6">
										<div class="wpcm-login">
											<h3><?php esc_html_e('Login to the website', 'webinane-commerce') ?></h3>
											<p><?php esc_html_e('Enter username and password to login.', 'webinane-commerce') ?></p>
											
											<div class="wpcm-option-row">
												<div class="wpcm-row">
													<div class="wpcm-field-input wpcm-col-sm-12">
														<input type="text" required v-model="login_form.username" class="wpcm-form-input" placeholder="<?php esc_html_e( 'Email / Username', 'webinane-commerce' ) ?>"> 
													</div>
												</div>
											</div>
											<div class="wpcm-option-row">
												<div class="wpcm-row">
													<div class="wpcm-field-input wpcm-col-sm-12">
														<input type="password" required v-model="login_form.password" class="wpcm-form-input" placeholder="<?php esc_html_e( 'Password', 'webinane-commerce' ) ?>">
													</div>
												</div>
											</div>
											<div class="wpcm-option-row">
												<div class="wpcm-row">
													<div class="wpcm-field-input wpcm-col-sm-12">
														<span class="wpcm-custom-checkbox">
															<el-checkbox v-model="login_form.rememberme"></el-checkbox>
															<label class="wpcm-field-label" for="rememberme">
																<span>
																	<?php esc_html_e( 'Remember Me', 'webinane-commerce' ) ?>
																</span>
															</label>
														</span>
													</div>
												</div>
											</div>
											<div class="wpcm-option-row">
												<div class="wpcm-row">
													<div class="wpcm-field-input wpcm-col-sm-12">
														<button @click.prevent="login()" class="wpcm-btn"><?php esc_html_e('Login', 'webinane-commerce') ?></button>
													</div>
												</div>
											</div>
										</div>
									</div>
									<div class="col-md-6">
										<div class="wpcm-register">
											<h3><?php esc_html_e('Sign Up Now', 'webinane-commerce') ?></h3>
											<p><?php esc_html_e('Fill in the form below to get instant access.' ,'webinane-commerce') ?></p>
											<div class="wpcm-option-row">
												<div class="wpcm-row">
													<div class="wpcm-field-input wpcm-col-sm-12">
														<input type="text" v-model="register_form.username" required class="wpcm-form-input" placeholder="<?php esc_html_e('Username', 'webinane-commerce' ) ?>">
													</div>
												</div>
											</div>
											<div class="wpcm-option-row">
												<div class="wpcm-row">
													<div class="wpcm-field-input wpcm-col-sm-12">
														<input v-model="register_form.email" type="email" required class="wpcm-form-input" placeholder="<?php esc_html_e( 'Email', 'webinane-commerce' ) ?>">
													</div>
												</div>
											</div>
											<div class="wpcm-option-row">
												<div class="wpcm-row">
													<div class="wpcm-field-input wpcm-col-sm-12">
														<input v-model="register_form.password" type="password" required class="wpcm-form-input" placeholder="<?php esc_html_e( 'Password', 'webinane-commerce' ) ?>">
													</div>
												</div>
											</div>
											<div class="wpcm-option-row">
												<div class="wpcm-row">
													<div class="wpcm-field-input wpcm-col-sm-12">
														<input v-model="register_form.confirm_password" type="password" required class="wpcm-form-input" placeholder="<?php esc_html_e( 'Confirm Password', 'webinane-commerce' ) ?>">
													</div>
												</div>
											</div>
											
											<div class="wpcm-option-row">
												<div class="wpcm-row">
													<div class="wpcm-field-input wpcm-col-sm-12">
														<button @click.prevent="register()" class="wpcm-btn"><?php esc_html_e('Register', 'webinane-commerce') ?></button>
													</div>
												</div>
											</div>

										</div>
									</div>
								</div>
							</div>	
						</login-register-form>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>