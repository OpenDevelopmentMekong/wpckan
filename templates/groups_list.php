<?php if (is_null($data)) die(); ?>

<?php
  if (count($data)==0){ ?>
    <p><?php _e('No groups available on the specified CKAN instance','wpckan_api_show_groups_dropdown_no_groups') ?></p>
<?php } else { ?>

  <select name="setting_ckan_group" id="setting_ckan_group" <?php if (!wpckan_api_ping()) echo "DISABLED";?>>
    <option <?php if(get_option('setting_ckan_group') == -1) echo 'selected="selected"' ?> value="-1"><?php _e('None','wpckan_api_show_groups_dropdown_none')?></option>
    <?php foreach ($data as $value){ ?>
      <option <?php if(get_option('setting_ckan_group') == $value["id"]) echo 'selected="selected"' ?> value="<?php echo $value["id"] ?>"><?php echo $value["display_name"] . " (" . $value["id"] . ")" ?></option>
    <?php } ?>
  </select>

<?php } ?>
