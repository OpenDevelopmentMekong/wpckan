<?php if (is_null($related_datasets)) die(); ?>

<?php if (wpckan_validate_settings_read()){ ?>

  <label for="wpckan_related_datasets_add_field"><b><?php _e('Add related datasets','wpckan_related_datasets_add_title') ?></b></label>
  <p>
    <input id="wpckan_related_datasets_add_field" class="new typeahead" onInput="wpckan_related_dataset_metabox_on_input();" wpckan-base-url="<?php echo get_option('setting_ckan_url'); ?>" wpckan-api-url="<?php echo wpckan_get_ckan_settings()["baseUrl"]; ?>" placeholder="Type for suggestions" type="text" name="wpckan_related_datasets_add_field" value="" size="25" />
    <input id="wpckan_related_datasets_add_button" class="button add disabled" type="button" value="Add" onClick="wpckan_related_dataset_metabox_add();" />
  </p>
  <div id="wpckan_related_datasets_list"></div>
  <input id="wpckan_add_related_datasets_datasets" name="wpckan_add_related_datasets_datasets" type="hidden" value='<?php echo $related_datasets_json; ?>'/>

<?php } else { ?>

  <p class="error"><?php _e( 'wpckan is not correctly configured. Please, check the ', 'related_datasets_metabox_config_error' ) ?><a href="options-general.php?page=wpckan">Settings</a></p>

<?php }?>
