<div class="wrap">
    <h2>WPCKAN -  A plugin for integrating CKAN and WP</h2>
    <form method="post" action="options.php">
        <?php @settings_fields('wpckan-group'); ?>
        <?php @do_settings_fields('wpckan-group'); ?>

        <?php
          $ckan_url = get_option('setting_ckan_url');
          $ckan_api = get_option('setting_ckan_api');
          $logging_path = get_option('setting_ckan_log_path');
          if (!$logging_path)
            $logging_path = DEFAULT_LOG;
          update_option('setting_ckan_valid_settings',wpckan_api_ping());
        ?>

        <table class="form-table">
          <th scope="row"><label><h3><?php _e('Connecting to CKAN','wpckan_settings_ckan_connection_header') ?></h3></label></th>
          <tr valign="top">
              <th scope="row"><label for="setting_ckan_url"><?php _e('CKAN Url','wpckan_settings_ckan_url_title') ?></label></th>
              <td>
                <input type="text" name="setting_ckan_url" id="setting_ckan_url" value="<?php echo $ckan_url ?>"/>
                <p class="description"><?php _e('Specify protocol such as http:// or https://','wpckan_settings_ckan_url_summary') ?>.</p>
              </td>
          </tr>
          <tr valign="top">
              <th scope="row"><label for="setting_ckan_api"><?php _e('CKAN Api','wpckan_settings_ckan_api_title') ?></label></th>
              <td>
                <input type="text" name="setting_ckan_api" id="setting_ckan_api" value="<?php echo get_option('setting_ckan_api'); ?>"/>
                <p class="description"><?php _e('Available under the profile page of a CKAN user with Admin rights.','wpckan_settings_ckan_api_summary') ?>.</p>
              </td>
          </tr>
          <?php if ($ckan_url && $ckan_api){ ?>
            <th scope="row"><label><h3><?php _e('Archiving','wpckan_settings_archiving_header') ?></h3></label></th>
            <tr valign="top">
              <th scope="row"><label for="setting_ckan_organization"><?php _e('CKAN Organization','wpckan_setting_ckan_organization_title') ?></label></th>
              <td>
                <?php echo wpckan_do_get_organizations_list(); ?>
              </td>
            </tr>
            <tr valign="top">
              <th scope="row"><label for="setting_ckan_group"><?php _e('CKAN Group','wpckan_setting_ckan_group_title') ?></label></th>
              <td>
                <?php echo wpckan_do_get_groups_list(); ?>
              </td>
            </tr>
            <tr valign="top" >
              <th scope="row"><label for"setting_archive_freq"><?php _e('Archive contents when:','wpckan_settings_archive_freq') ?></label></th>
              <td>
                <select name="setting_archive_freq" id="setting_archive_freq" <?php if (!get_option('setting_ckan_valid_settings')) echo "DISABLED";?>>
                  <option value="0" <?php if(get_option('setting_archive_freq') == 0) echo 'selected="selected"' ?>><?php _e('Never archive','wpckan_settings_archive_freq_0' )?></option>
                  <option value="1" <?php if(get_option('setting_archive_freq') == 1) echo 'selected="selected"' ?>><?php _e('Post is published','wpckan_settings_archive_freq_1' )?></option>
                  <option value="2" <?php if(get_option('setting_archive_freq') == 2) echo 'selected="selected"' ?>><?php _e('Post is saved','wpckan_settings_archive_freq_2') ?></option>
                  <option value="3" <?php if(get_option('setting_archive_freq') == 3) echo 'selected="selected"' ?>><?php _e('Daily','wpckan_settings_archive_freq_3') ?></option>
                  <option value="4" <?php if(get_option('setting_archive_freq') == 4) echo 'selected="selected"' ?>><?php _e('Weekly','wpckan_settings_archive_freq_4') ?></option>
                </select>
              </td>
            </tr>
            <th scope="row"><label><h3><?php _e('Logging','wpckan_settings_logging_header') ?></h3></label></th>
            <tr valign="top">
              <th scope="row"><label for="setting_ckan_log_path"><?php _e('Path','wpckan_settings_ckan_logging_path_title') ?></label></th>
              <td>
                <input type="text" name="setting_ckan_log_path" id="setting_ckan_log_path" value="<?php echo $logging_path ?>"/>
                <p class="description"><?php _e('Path where logs are going to be stored. Mind permissions.','wpckan_settings_ckan_logging_path_summary') ?>.</p>
              </td>
            </tr>
          <?php } ?>
        </table>
        <?php @submit_button(); ?>
    </form>
</div>
