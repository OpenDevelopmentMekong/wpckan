<div class="wrap">
    <h2>WPCKAN -  A plugin for integrating CKAN and WP</h2>
    <form method="post" action="options.php">
        <?php @settings_fields('wpckan-group'); ?>
        <?php @do_settings_fields('wpckan-group'); ?>

        <?php
          wpckan_log("Rendering settings.php");
          $ckan_url = get_option('wpckan_setting_ckan_url');
          //$ckan_url_redirection = get_option('wpckan_setting_ckan_url_redirection');
          $ckan_api = get_option('wpckan_setting_ckan_api');
          $supported_fields = get_option('wpckan_setting_supported_fields');
          $multilingual_fields = get_option('wpckan_setting_multilingual_fields');
          $field_mappings = get_option('wpckan_setting_field_mappings');
          $logging_path = get_option('wpckan_setting_log_path');
          if (!isset($logging_path)):
            $logging_path = WPCKAN_DEFAULT_LOG_PATH;
          endif;
          $logging_enabled = get_option('wpckan_setting_log_enabled');
          $cache_path = get_option('wpckan_setting_cache_path');
          if (!isset($cache_path)):
            $cache_path = WPCKAN_DEFAULT_CACHE_PATH;
          endif;
          $cache_time = get_option('wpckan_setting_cache_time');
          if (!isset($cache_time)):
            $cache_time = WPCKAN_DEFAULT_CACHE_TIME;
          endif;
          $cache_enabled = get_option('wpckan_setting_cache_enabled');
          $valid_connection_read = wpckan_validate_settings_read();
          $valid_connection_write = wpckan_validate_settings_write();
          update_option('wpckan_setting_ckan_valid_settings_read',$valid_connection_read);
          update_option('wpckan_setting_ckan_valid_settings_write',$valid_connection_write);
        ?>

        <table class="form-table">
          <th scope="row"><label><h3><?php _e('Connecting to CKAN','wpckan') ?></h3></label></th>
          <tr valign="top">
              <th scope="row"><label for="wpckan_setting_ckan_url"><?php _e('CKAN Url','wpckan') ?></label></th>
              <td>
                <input class="full-width" type="text" name="wpckan_setting_ckan_url" id="wpckan_setting_ckan_url" value="<?php echo $ckan_url ?>"/>
                <p class="description"><?php _e('Specify protocol such as http:// or https://.','wpckan') ?>.</p>
              </td>
          </tr>
          <tr valign="top">
              <th scope="row"><label for="wpckan_setting_ckan_api"><?php _e('CKAN Api key','wpckan') ?></label></th>
              <td>
                <input class="full-width" type="text" name="wpckan_setting_ckan_api" id="wpckan_setting_ckan_api" value="<?php echo get_option('wpckan_setting_ckan_api'); ?>"/>
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
          <!-- Supported fields -->
          <tr valign="top">
            <th scope="row"><label for="wpckan_setting_supported_fields"><?php _e('Supported fields','wpckan') ?></label></th>
            <td>
              <input class="full-width" type="text" name="wpckan_setting_supported_fields" id="wpckan_setting_supported_fields" value="<?php echo $supported_fields ?>"></input>
              <p class="description"><?php _e('Specify a list of Comma-separated field names to show on the additional data section','wpckan') ?></p>
            </td>
          </tr>
          <!-- Multilingual fields -->
          <?php if (wpckan_is_qtranslate_available()): ?>
            <tr valign="top">
              <th scope="row"><label for="wpckan_setting_multilingual_fields"><?php _e('Fluent fields','wpckan') ?></label></th>
              <td>
                <input class="full-width" type="text" name="wpckan_setting_multilingual_fields" id="wpckan_setting_multilingual_fields" value="<?php echo $multilingual_fields ?>"></input>
                <p class="description"><?php _e('Specify a list of Comma-separated field names which are marked as fluent. See https://github.com/open-data/ckanext-fluent/','wpckan') ?></p>
              </td>
            </tr>
          <?php endif; ?>
          <!-- Field mappings-->
          <tr valign="top">
            <th scope="row"><label for="wpckan_setting_field_mappings"><?php _e('Field mappings','wpckan') ?></label></th>
            <td>
              <textarea class="full-width" name="wpckan_setting_field_mappings" placeholder="owner_org  =>  Organization"><?php echo $field_mappings;?></textarea>
              <p class="description"><?php _e('Specify a list key => value mappings, separated by line breaks which define the mapping of a metadata field to its label','wpckan') ?></p>
            </td>
          </tr>
          <!-- Caching -->
          <th scope="row"><label><h3><?php _e('Caching','wpckan') ?></h3></label></th>
          <tr valign="top">
            <th scope="row"><label for="wpckan_setting_cache_enabled"><?php _e('Enable caching for API requests','wpckan') ?></label></th>
            <td>
              <input type="checkbox" name="wpckan_setting_cache_enabled" id="wpckan_setting_cache_enabled" <?php if ($cache_enabled)  echo 'checked="true"'; ?>/>
            </td>
          </tr>
          <tr valign="top">
            <th scope="row"><label for="wpckan_setting_cache_path"><?php _e('Cache file path','wpckan') ?></label></th>
            <td>
              <input type="text" name="wpckan_setting_cache_path" id="wpckan_setting_cache_path" value="<?php echo $cache_path ?>"/>
              <p class="description"><?php _e('Path where cached files are going to be stored. Mind permissions.','wpckan') ?></p>
            </td>
          </tr>
          <tr valign="top">
            <th scope="row"><label for="wpckan_setting_cache_time"><?php _e('Cache time','wpckan') ?></label></th>
            <td>
              <input type="number" name="wpckan_setting_cache_time" id="wpckan_setting_cache_time" value="<?php echo (isset($cache_time) ? $cache_time : WPCKAN_DEFAULT_CACHE_TIME); ?>"/>
              <p class="description"><?php _e('Time in milisecons cached versions will be kept.','wpckan') ?></p>
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
            <th scope="row"><label for="wpckan_setting_log_path"><?php _e('Log file path','wpckan') ?></label></th>
            <td>
              <input type="text" name="wpckan_setting_log_path" id="wpckan_setting_log_path" value="<?php echo $logging_path ?>"/>
              <p class="description"><?php _e('Path where logs are going to be stored. Mind permissions.','wpckan') ?></p>
            </td>
          </tr>
        </table>
        <?php @submit_button(); ?>
    </form>
</div>
