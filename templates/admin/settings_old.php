
<div class="wrap wpcm-options-page option-<?php echo $wpcmm_options->option_key; ?>">
	<?php if ( get_admin_page_title() ) : ?>
		<h2><?php echo wp_kses_post( get_admin_page_title() ); ?></h2>
	<?php endif; ?>
	<h2 class="nav-tab-wrapper">
		<?php foreach ( $tabs as $option_key => $tab_title ) : ?>
			<a class="nav-tab<?php if ( isset( $_GET['page'] ) && $option_key === $_GET['page'] ) : ?> nav-tab-active<?php endif; ?>" href="<?php menu_page_url( $option_key ); ?>"><?php echo wp_kses_post( $tab_title ); ?></a>
		<?php endforeach; ?>
	</h2>
	<form class="wpcmm-form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="POST" id="<?php echo $wpcmm_options->wpcmm->wpcmm_id; ?>" enctype="multipart/form-data" encoding="multipart/form-data">
		<input type="hidden" name="action" value="<?php echo esc_attr( $wpcmm_options->option_key ); ?>">
		<?php $wpcmm_options->options_page_metabox(); ?>
		<?php submit_button( esc_attr( $wpcmm_options->wpcmm->prop( 'save_button' ) ), 'primary', 'submit-wpcmm' ); ?>
	</form>
</div>