<?php if (is_null($data)) die(); ?>

<?php if (wpckan_validate_settings()){ ?>

  <label for="wpckan_dataset_url_field">
    <?php _e( 'Dataset\'s URL', 'myplugin_textdomain' ) ?>
  </label>
  <input onChange="wpckan_related_dataset_metabox_on_change();" type="text" id="wpckan_dataset_url_field" name="wpckan_dataset_url_field" value="<?php echo esc_attr( $data )?>" size="25" />

<?php } else { ?>

  <p class="error"><?php _e( 'wpckan is not correctly configured. Please, check the ', 'related_datasets_metabox_config_error' ) ?><a href="options-general.php?page=wpckan">Settings</a></p>

<?php }?>
