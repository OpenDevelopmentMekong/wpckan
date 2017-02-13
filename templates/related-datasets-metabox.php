<?php if (is_null($related_datasets)) die(); ?>

<?php if (wpckan_validate_settings_read()){ ?>

  <label for="wpckan_related_datasets_add_field"><b><?php _e('Add related datasets','wpckan') ?></b></label>
  <p>
    <input disabled id="wpckan_related_datasets_add_field" class="new typeahead" onInput="wpckan_related_dataset_metabox_on_input();" wpckan-base-url="<?php echo $GLOBALS['wpckan_options']->get_option('wpckan_setting_ckan_url'); ?>" wpckan-api-url="<?php echo wpckan_get_ckan_settings()["baseUrl"]; ?>" placeholder="Type for suggestions" type="text" name="wpckan_related_datasets_add_field" value="" size="25" />
    <input id="wpckan_related_datasets_add_button" class="button add disabled" type="button" value="Add" onClick="wpckan_related_dataset_metabox_add();" />
  </p>
  <div id="wpckan_related_datasets_list"></div>
  <input id="wpckan_add_related_datasets_datasets" name="wpckan_add_related_datasets_datasets" type="hidden" value='<?php echo $related_datasets_json; ?>'/>

<?php } else { ?>

  <p class="error"><?php _e( 'wpckan is not correctly configured. Please, check the ', 'wpckan' ) ?><a href="options-general.php?page=wpckan">Settings</a></p>

<?php }?>
