<?php if (is_null($data)) die(); ?>

<?php
  if (count($data)==0){ ?>
    <p><?php _e('No organizations available on the specified CKAN instance','wpckan_api_show_organizations_dropdown_no_organizations') ?></p>
<?php } else { ?>

  <select name="setting_ckan_organization" id="setting_ckan_organization" <?php if (!wpckan_api_ping()) echo "DISABLED";?>>
    <option <?php if(get_option('setting_ckan_organization') == -1) echo 'selected="selected"' ?> value="-1"><?php _e('None','wpckan_api_show_organizations_dropdown_none')?></option>
    <?php foreach ($data as $value){ ?>
      <option <?php if(get_option('setting_ckan_organization') == $value["id"]) echo 'selected="selected"' ?> value="<?php echo $value["id"] ?>"><?php echo $value["display_name"] . " (" . $value["id"] . ")" ?></option>
    <?php } ?>
  </select>

<?php } ?>
