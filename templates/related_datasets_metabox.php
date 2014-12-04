<?php if (is_null($related_dataset)) die(); ?>

<label for="wpckan_dataset_url_field">
  <?php _e( 'Dataset\'s URL', 'myplugin_textdomain' ) ?>
</label>
<input onChange="wpckan_related_dataset_metabox_on_change();" type="text" id="wpckan_dataset_url_field" name="wpckan_dataset_url_field" value="<?php esc_attr( $related_dataset )?>" size="25" />
