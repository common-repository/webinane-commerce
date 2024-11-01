<div class="wpcm-wrapper wpcm-ssec-mrg wpcm-settings-wrapper wpcm-wrapper">
	<div class="wpcm-container">
		<?php if( isset( $_GET['wpcm_db_update'] ) ): ?>
			<?php  \WebinaneCommerce\WPCM_Database_Upgrade::set_status_running(); ?>
		<?php endif; ?>
		<div class="wpcm-dashboard-wrapper">
			<settings></settings>
		</div>
	</div>
</div>