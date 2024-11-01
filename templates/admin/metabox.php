<?php 
	$mb = webinane_array($config);
	$fields = $mb->get('fields' );
	$meta_id = $mb->get( 'id' );

	$options = (array) get_post_meta(get_the_id(), $meta_id, true);
	$options = array_filter($options) ? $options : ['' => ''];

?>
<div class="wpcm-metabox-wrapper wpcm-wrapper" meta_id="<?php echo esc_attr( $meta_id) ?>" options='<?php echo wp_json_encode( $options) ?>'>

	<div v-if="fields" v-for="field in fields">
		<component :is="field.is" :field="field" :options="options" :dependencies="dependencies" v-on:depedency_event_change="depedency_event_change"></component>
	</div>
</div>
