<div class="wrap">
    <h2>WPCKAN -  A plugin for integrating CKAN and WP</h2>
    <form method="post" action="options.php">
        <?php @settings_fields('wpckan-group'); ?>
        <?php @do_settings_fields('wpckan-group'); ?>

        <?php
          wpckan_log("Rendering settings.php");
          $ckan_url = get_option('wpckan_setting_ckan_url');
          $ckan_api = get_option('wpckan_setting_ckan_api');
          $logging_path = get_option('wpckan_setting_log_path');
          $logging_enabled = get_option('wpckan_setting_log_enabled');
          if (!$logging_path)
            $logging_path = WPCKAN_DEFAULT_LOG;
          $valid_connection_read = wpckan_validate_settings_read();
          $valid_connection_write = wpckan_validate_settings_write();
          update_option('wpckan_setting_ckan_valid_settings_write',$valid_connection_read);
          update_option('wpckan_setting_ckan_valid_settings_write',$valid_connection_write);
        ?>

        <table class="form-table">
          <th scope="row"><label><h3><?php _e('Connecting to CKAN','wpckan') ?></h3></label></th>
          <tr valign="top">
              <th scope="row"><label for="wpckan_setting_ckan_url"><?php _e('CKAN Url','wpckan') ?></label></th>
              <td>
                <input type="text" name="wpckan_setting_ckan_url" id="wpckan_setting_ckan_url" value="<?php echo $ckan_url ?>"/>
                <p class="description"><?php _e('Specify protocol such as http:// or https://.','wpckan') ?>.</p>
              </td>
          </tr>
          <tr valign="top">
              <th scope="row"><label for="wpckan_setting_ckan_api"><?php _e('CKAN Api key','wpckan') ?></label></th>
              <td>
                <input type="text" name="wpckan_setting_ckan_api" id="wpckan_setting_ckan_api" value="<?php echo get_option('wpckan_setting_ckan_api'); ?>"/>
                <p class="description"><?php _e('Available under the profile page of a CKAN user with Admin rights.','wpckan') ?>.</p>
              </td>
          </tr>
          <!-- Connection status -->
          <tr valign="top">
            <th scope="row"><label><?php _e('Connection status','wpckan') ?></label></th>
            <td>
              <?php if ($valid_connection_read){ ?>
                <p class="ok"><?php _e('CKAN URL specified correctly.','wpckan') ?></p>
              <?php } else { ?>
                <p class="error"><?php _e('Problem connecting to CKAN instance. Please, check the specified URL.','wpckan') ?></p>
              <?php } ?>
              <?php if ($valid_connection_write){ ?>
                <p class="ok"><?php _e('CKAN API Key specified correctly.','wpckan') ?></p>
              <?php } else { ?>
                <p class="error"><?php _e('Please, specify a valid CKAN API Key.','wpckan') ?></p>
              <?php } ?>
            </td>
          </tr>
          <!-- Related datasets -->
          <tr valign="top">
            <th scope="row"><label for="settings_supported_post_types"><?php _e('Supported post types','wpckan') ?></label></th>
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
          <th scope="row"><label><h3><?php _e('Logging','wpckan') ?></h3></label></th>
          <tr valign="top">
            <th scope="row"><label for="wpckan_setting_log_enabled"><?php _e('Enable log','wpckan') ?></label></th>
            <td>
              <input type="checkbox" name="wpckan_setting_log_enabled" id="wpckan_setting_log_enabled" <?php if ($logging_enabled)  echo 'checked="true"'; ?>/>
            </td>
          </tr>
          <tr valign="top">
            <th scope="row"><label for="wpckan_setting_log_path"><?php _e('Path','wpckan') ?></label></th>
            <td>
              <input type="text" name="wpckan_setting_log_path" id="wpckan_setting_log_path" value="<?php echo $logging_path ?>"/>
              <p class="description"><?php _e('Path where logs are going to be stored. Mind permissions.','wpckan') ?></p>
            </td>
          </tr>
        </table>
        <?php @submit_button(); ?>
    </form>
</div>
