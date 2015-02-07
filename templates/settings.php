<div class="wrap">
    <h2>WPCKAN -  A plugin for integrating CKAN and WP</h2>
    <form method="post" action="options.php">
        <?php @settings_fields('wpckan-group'); ?>
        <?php @do_settings_fields('wpckan-group'); ?>

        <?php
          wpckan_log("Rendering settings.php");
          $ckan_url = get_option('setting_ckan_url');
          $ckan_api = get_option('setting_ckan_api');
          $logging_path = get_option('setting_log_path');
          $logging_enabled = get_option('setting_log_enabled');
          if (!$logging_path)
            $logging_path = DEFAULT_LOG;
          $valid_connection_read = wpckan_validate_settings_read();
          $valid_connection_write = wpckan_validate_settings_write();
          update_option('setting_ckan_valid_settings_read',$valid_connection_read);
          update_option('setting_ckan_valid_settings_write',$valid_connection_write);
        ?>

        <table class="form-table">
          <th scope="row"><label><h3><?php _e('Connecting to CKAN','wpckan_settings_ckan_connection_header') ?></h3></label></th>
          <tr valign="top">
              <th scope="row"><label for="setting_ckan_url"><?php _e('CKAN Url','wpckan_settings_ckan_url_title') ?></label></th>
              <td>
                <input type="text" name="setting_ckan_url" id="setting_ckan_url" value="<?php echo $ckan_url ?>"/>
                <p class="description"><?php _e('Specify protocol such as http:// or https://.','wpckan_settings_ckan_url_summary') ?>.</p>
              </td>
          </tr>
          <tr valign="top">
              <th scope="row"><label for="setting_ckan_api"><?php _e('CKAN Api','wpckan_settings_ckan_api_title') ?></label></th>
              <td>
                <input type="text" name="setting_ckan_api" id="setting_ckan_api" value="<?php echo get_option('setting_ckan_api'); ?>"/>
                <p class="description"><?php _e('Available under the profile page of a CKAN user with Admin rights.','wpckan_settings_ckan_api_summary') ?>.</p>
              </td>
          </tr>
          <!-- Connection status -->
          <tr valign="top">
            <th scope="row"><label><?php _e('Connection status','wpckan_settings_valid_connection_title') ?></label></th>
            <td>
              <?php if ($valid_connection_read){ ?>
                <p class="ok"><?php _e('CKAN URL specified correctly.','wpckan_settings_valid_connection_read_ok') ?></p>
              <?php } else { ?>
                <p class="error"><?php _e('Problem connecting to CKAN instance. Please, check the specified URL.','wpckan_settings_valid_connection_read_error') ?></p>
              <?php } ?>
              <?php if ($valid_connection_write){ ?>
                <p class="ok"><?php _e('CKAN API Key specified correctly.','wpckan_settings_valid_connection_write_ok') ?></p>
              <?php } else { ?>
                <p class="error"><?php _e('Please, specify a valid CKAN API Key.','wpckan_settings_valid_connection_write_error') ?></p>
              <?php } ?>
            </td>
          </tr>
          <!-- Related datasets -->
          <tr valign="top">
            <th scope="row"><label for="settings_supported_post_types"><?php _e('Supported post types','wpckan_settings_supported_post_types_title') ?></label></th>
            <td>
             <?php
              foreach (get_post_types() as $post_type) {
              $settings_name =  "setting_supported_post_types_" . $post_type;
             ?>
              <p><input type="checkbox" name="<?php echo $settings_name ?>" id="<?php echo $settings_name ?>" <?php if (get_option($settings_name))  echo 'checked="true"'; ?>><?php echo $post_type ?></input></p>
             <?php } ?>
           </td>
          </tr>
          <!-- Logging -->
          <th scope="row"><label><h3><?php _e('Logging','wpckan_settings_logging_header') ?></h3></label></th>
          <tr valign="top">
            <th scope="row"><label for="setting_log_enabled"><?php _e('Enable log','wpckan_settings_log_enabled_title') ?></label></th>
            <td>
              <input type="checkbox" name="setting_log_enabled" id="setting_log_enabled" <?php if ($logging_enabled)  echo 'checked="true"'; ?>/>
            </td>
          </tr>
          <tr valign="top">
            <th scope="row"><label for="setting_log_path"><?php _e('Path','wpckan_settings_log_path_title') ?></label></th>
            <td>
              <input type="text" name="setting_log_path" id="setting_log_path" value="<?php echo $logging_path ?>"/>
              <p class="description"><?php _e('Path where logs are going to be stored. Mind permissions.','wpckan_settings_ckan_logging_path_summary') ?></p>
            </td>
          </tr>
        </table>
        <?php @submit_button(); ?>
    </form>
</div>
