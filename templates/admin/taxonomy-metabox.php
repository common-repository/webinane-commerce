<?php 
	$mb = webinane_array($args);
	$fields = $mb->get('fields' );
	$meta_id = $mb->get( 'id' );
	$options = isset($term->term_id) ? get_term_meta($term->term_id, $meta_id, true) : ['' => ''];
	$taxonomy = get_current_screen()->taxonomy ? get_current_screen()->taxonomy : '';
?>
<div class="wpcm-metabox-wrapper wpcm-wrapper" meta_id="<?php echo esc_attr( $meta_id) ?>" options='<?php echo wp_json_encode( $options ) ?>'>
	<input type="hidden" name="webinane_commer_meta_key" value="<?php echo esc_attr($id) ?>">
	<input type="hidden" name="webinane_commer_metabox_action" ref="metabox_action" value="_wpcommerce_admin_taxonomy_metabox_data">
	<input type="hidden" name="webinane_commer_metabox_object_type" ref="metabox_object_type" value="<?php echo esc_attr($taxonomy) ?>">
	<div v-if="fields" v-for="field in fields">
		<component :is="field.is" :field="field" :options="options" :dependencies="dependencies" v-on:depedency_event_change="depedency_event_change"></component>
	</div>
</div>
