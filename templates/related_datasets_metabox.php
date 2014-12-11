<?php if (is_null($data)) die(); ?>

<?php if (wpckan_validate_settings()){ ?>

  <b><?php _e( 'Dataset\'s URL', 'wpckan_related_datasets_entries_title' ) ?></b>
  <div id="wpckan_related_datasets_entries">
    <p>
      <input class="new" onChange="wpckan_related_dataset_metabox_on_change();" type="text" id="wpckan_dataset_url_field_0" name="wpckan_dataset_url_field_0" value="<?php echo esc_attr( $data[0] )?>" size="25" />
      <input class="button delete" type="button" value="Delete">
    </p>
  </div>
  <!-- <input type="button" class="button add" value="Add" onClick="wpckan_related_dataset_metabox_add();"> -->
  <div id="wpckan_related_datasets_options">
    <p><b><?php _e( 'Select what to show', 'wpckan_related_datasets_options_title' ) ?></b></p>
    <input type="checkbox" name="wpckan_related_datasets_options_0" value="Title"> Title<br>
    <input type="checkbox" name="wpckan_related_datasets_options_1" value="Description" checked> Description<br>
    <input type="checkbox" name="wpckan_related_datasets_options_2" value="License"> License<br>
    <input type="checkbox" name="wpckan_related_datasets_options_3" value="Author"> License<br>
    <input type="checkbox" name="wpckan_related_datasets_options_4" value="Resources"> Resources<br>
  </div>

<?php } else { ?>

  <p class="error"><?php _e( 'wpckan is not correctly configured. Please, check the ', 'related_datasets_metabox_config_error' ) ?><a href="options-general.php?page=wpckan">Settings</a></p>

<?php }?>
