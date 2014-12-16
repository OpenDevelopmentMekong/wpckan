<?php if (is_null($archive_orga) || is_null($archive_group) || is_null($archive_freq)) die(); ?>

<?php if (wpckan_validate_settings_write()){ ?>

  <label for="wpckan_archive_post_orga"><b><?php _e('CKAN Organization','wpckan_setting_ckan_organization_title') ?></b></label>
  <p><select name="wpckan_archive_post_orga" id="wpckan_archive_post_orga">
    <option <?php if($archive_orga == -1) echo 'selected="selected"' ?> value="-1"><?php _e('None','wpckan_archive_post_orga_none')?></option>
    <?php foreach (wpckan_api_get_organizations_list() as $value){ ?>
      <option <?php if($archive_orga == $value["id"]) echo 'selected="selected"' ?> value="<?php echo $value["id"] ?>"><?php echo $value["display_name"]?></option>
    <?php } ?>
  </select></p>
  <label for="setting_ckan_group"><b><?php _e('CKAN Group','wpckan_setting_ckan_group_title') ?></b></label>
  <p><select name="wpckan_archive_post_group" id="wpckan_archive_post_group">
    <option <?php if($archive_group == -1) echo 'selected="selected"' ?> value="-1"><?php _e('None','wpckan_archive_post_group_none')?></option>
    <?php foreach (wpckan_api_get_groups_list() as $value){ ?>
      <option <?php if($archive_group == $value["id"]) echo 'selected="selected"' ?> value="<?php echo $value["id"] ?>"><?php echo $value["display_name"]?></option>
    <?php } ?>
  </select></p>
  <label for"wpckan_archive_post_freq"><b><?php _e('Archive contents when:','wpckan_archive_post_freq') ?></b></label>
  <p><select name="wpckan_archive_post_freq" id="wpckan_archive_post_freq">
    <option value="0" <?php if($archive_freq == 0) echo 'selected="selected"' ?>><?php _e('Never archive','wpckan_archive_post_freq_0' )?></option>
    <option value="1" <?php if($archive_freq == 1) echo 'selected="selected"' ?>><?php _e('Post is published','wpckan_archive_post_freq_1' )?></option>
    <option value="2" <?php if($archive_freq == 2) echo 'selected="selected"' ?>><?php _e('Post is saved','wpckan_archive_post_freq_2') ?></option>
  </select></p>

<?php } else { ?>

  <p class="error"><?php _e( 'wpckan is not correctly configured. Please, check the ', 'related_datasets_metabox_config_error' ) ?><a href="options-general.php?page=wpckan">Settings</a></p>

<?php }?>
