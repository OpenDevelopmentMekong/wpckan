<div class="wrap">
    <h2>WPCKAN -  A plugin for integrating CKAN and WP</h2>
    <form method="post" action="options.php">
        <?php @settings_fields('wpckan-group'); ?>
        <?php @do_settings_fields('wpckan-group'); ?>

        <?php
          wpckan_log("Rendering settings.php");
          $ckan_url = get_option('setting_ckan_url');
          $ckan_api = get_option('setting_ckan_api');
          $logging_path = get_option('setting_ckan_log_path');
          if (!$logging_path)
            $logging_path = DEFAULT_LOG;
          $valid_connection = wpckan_validate_settings();
          update_option('setting_ckan_valid_settings',$valid_connection);

          wpckan_api_user_show($ckan_api);
        ?>

        <table class="form-table">
          <th scope="row"><label><h3><?php _e('Connecting to CKAN','wpckan_settings_ckan_connection_header') ?></h3></label></th>
          <tr valign="top">
              <th scope="row"><label for="setting_ckan_url"><?php _e('CKAN Url','wpckan_settings_ckan_url_title') ?></label></th>
              <td>
                <input type="text" name="setting_ckan_url" id="setting_ckan_url" value="<?php echo $ckan_url ?>"/>
                <p class="description"><?php _e('Specify protocol such as http:// or https://. Do not include trailing slash at the end of the url!','wpckan_settings_ckan_url_summary') ?>.</p>
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
            <?php if ($valid_connection){ ?>
              <td><p class="ok"><?php _e('Successfully connected to CKAN instance','wpckan_settings_valid_connection_ok') ?></p></th>
            <?php } else { ?>
              <td><p class="error"><?php _e('Problem connecting to CKAN instance. Please, check the specified data.','wpckan_settings_valid_connection_error') ?></p></th>
            <?php } ?>
          </tr>
          <!-- Connection status end -->
          <th scope="row"><label><h3><?php _e('Logging','wpckan_settings_logging_header') ?></h3></label></th>
          <tr valign="top">
            <th scope="row"><label for="setting_ckan_log_path"><?php _e('Path','wpckan_settings_ckan_logging_path_title') ?></label></th>
            <td>
              <input type="text" name="setting_ckan_log_path" id="setting_ckan_log_path" value="<?php echo $logging_path ?>"/>
              <p class="description"><?php _e('Path where logs are going to be stored. Mind permissions.','wpckan_settings_ckan_logging_path_summary') ?>.</p>
            </td>
          </tr>
        </table>
        <?php @submit_button(); ?>
    </form>
</div>
