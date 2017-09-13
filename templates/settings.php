<div class="wrap">
    <h2>WPCKAN -  A plugin for integrating CKAN and WP</h2>
    <form method="post" action="options.php">
        <?php @settings_fields('wpckan-group'); ?>
        <?php @do_settings_fields('wpckan-group'); ?>

        <?php
          wpckan_log("Rendering settings.php");
          $ckan_url = $GLOBALS['wpckan_options']->get_option('wpckan_setting_ckan_url');
          $ckan_api = $GLOBALS['wpckan_options']->get_option('wpckan_setting_ckan_api');
          $supported_fields = $GLOBALS['wpckan_options']->get_option('wpckan_setting_supported_fields');
					$supported_fields_additional = $GLOBALS['wpckan_options']->get_option('wpckan_setting_supported_fields_additional');
          $field_mappings = $GLOBALS['wpckan_options']->get_option('wpckan_setting_field_mappings');
          $field_mappings_values = $GLOBALS['wpckan_options']->get_option('wpckan_setting_field_mappings_values');
          $supported_datatables = $GLOBALS['wpckan_options']->get_option('wpckan_setting_supported_datatables');
          $linked_fields = $GLOBALS['wpckan_options']->get_option('wpckan_setting_linked_fields');
          $redirect_enabled = $GLOBALS['wpckan_options']->get_option('wpckan_setting_redirect_enabled');
          $target_blank_enabled = $GLOBALS['wpckan_options']->get_option('wpckan_setting_target_blank_enabled');
          $logging_path = $GLOBALS['wpckan_options']->get_option('wpckan_setting_log_path');
          if (!isset($logging_path)):
            $logging_path = WPCKAN_DEFAULT_LOG_PATH;
          endif;
          $logging_enabled = $GLOBALS['wpckan_options']->get_option('wpckan_setting_log_enabled');
          $cache_path = $GLOBALS['wpckan_options']->get_option('wpckan_setting_cache_path');
          if (!isset($cache_path)):
            $cache_path = WPCKAN_DEFAULT_CACHE_PATH;
          endif;
          $cache_time = $GLOBALS['wpckan_options']->get_option('wpckan_setting_cache_time');
          if (!isset($cache_time)):
            $cache_time = WPCKAN_DEFAULT_CACHE_TIME;
          endif;
          $cache_enabled = $GLOBALS['wpckan_options']->get_option('wpckan_setting_cache_enabled');
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
                <input class="full-width" type="text" name="wpckan_setting_ckan_api" id="wpckan_setting_ckan_api" value="<?php echo $GLOBALS['wpckan_options']->get_option('wpckan_setting_ckan_api'); ?>"/>
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
              <p><input type="checkbox" name="<?php echo $settings_name ?>" id="<?php echo $settings_name ?>" <?php if ($GLOBALS['wpckan_options']->get_option($settings_name))  echo 'checked="true"'; ?>><?php echo $post_type ?></input></p>
             <?php } ?>
           </td>
          </tr>
          <!-- Supported fields -->
          <th scope="row"><label><h3><?php _e('Dataset detail pages','wpckan') ?></h3></label></th>
          <tr valign="top">
            <th scope="row"><label for="wpckan_setting_supported_fields"><?php _e('Supported fields','wpckan') ?></label></th>
            <td>
              <input class="full-width" type="text" name="wpckan_setting_supported_fields" id="wpckan_setting_supported_fields" placeholder="<?php _e('title, description, license_url, ...','wpckan') ?>" value="<?php echo $supported_fields ?>"></input>
              <p class="description"><?php _e('Specify a list of Comma-separated field names to show on the main metadata section. Mind order.','wpckan') ?></p>
            </td>
          </tr>
					<tr valign="top">
            <th scope="row"><label for="wpckan_setting_supported_fields_additional"><?php _e('Supported fields (Additional)','wpckan') ?></label></th>
            <td>
              <input class="full-width" type="text" name="wpckan_setting_supported_fields_additional" id="wpckan_setting_supported_fields_additional" placeholder="<?php _e('title, description, license_url, ...','wpckan') ?>" value="<?php echo $supported_fields_additional ?>"></input>
              <p class="description"><?php _e('Specify a list of Comma-separated field names to show on the additional metadata section. Mind order.','wpckan') ?></p>
            </td>
          </tr>
          <!-- Field mappings for keys-->
          <tr valign="top">
            <th scope="row"><label for="wpckan_setting_field_mappings"><?php _e('Field mappings for keys','wpckan') ?></label></th>
            <td>
              <textarea class="full-width" name="wpckan_setting_field_mappings" placeholder="owner_org  =>  Organization"><?php echo $field_mappings;?></textarea>
              <p class="description"><?php _e('Specify a list key => value mappings, separated by line breaks which define the mapping of a metadata field key to its label','wpckan') ?></p>
            </td>
          </tr>
          <!-- Field mappings for values-->
          <tr valign="top">
            <th scope="row"><label for="wpckan_setting_field_mappings_values"><?php _e('Field mappings for values','wpckan') ?></label></th>
            <td>
              <textarea class="full-width" name="wpckan_setting_field_mappings_values" placeholder="owner_org  =>  Organization"><?php echo $field_mappings_values;?></textarea>
              <p class="description"><?php _e('Specify a list value => value mappings, separated by line breaks which define the mapping of a metadata field value to its label','wpckan') ?></p>
            </td>
          </tr>
          <!-- Supported datatables-->
          <tr valign="top">
            <th scope="row"><label for="wpckan_setting_supported_datatables"><?php _e('Supported datatables','wpckan') ?></label></th>
            <td>
              <textarea class="full-width" name="wpckan_setting_supported_datatables" placeholder="owner_org  =>  Organization"><?php echo $supported_datatables;?></textarea>
              <p class="description"><?php _e('Specify a list field_id => respurce_id mappings, separated by line breaks which define the mapping of a metadata field value to its label','wpckan') ?></p>
            </td>
          </tr>
          <!-- Fields containing ids to link to other datasets -->
          <tr valign="top">
            <th scope="row"><label for="wpckan_setting_linked_fields"><?php _e('Linked fields','wpckan') ?></label></th>
            <td>
              <input class="full-width" type="text" name="wpckan_setting_linked_fields" id="wpckan_setting_linked_fields" placeholder="<?php _e('odm_laws_primary_policy_reference_point, odm_laws_previous_legal_document, odm_laws_parent_document, ...','wpckan') ?>" value="<?php echo $linked_fields ?>"></input>
              <p class="description"><?php _e('Specify a list of Comma-separated field names containing ids to link to other datasets.','wpckan') ?></p>
            </td>
          </tr>
          <!-- Redirect-->
          <tr valign="top">
            <th scope="row"><label for="wpckan_setting_redirect_enabled"><?php _e('Enable url redirection','wpckan') ?></label></th>
            <td>
              <input type="checkbox" name="wpckan_setting_redirect_enabled" id="wpckan_setting_redirect_enabled" <?php if ($redirect_enabled)  echo 'checked="true"'; ?>/>
              <p class="description"><?php _e('if checked, links to datasets, resources and organizations will be redirected to /','wpckan') ?></p>
            </td>
          </tr>
          <tr valign="top">
            <th scope="row"><label for="wpckan_setting_target_blank_enabled"><?php _e('Open datasets in new tab','wpckan') ?></label></th>
            <td>
              <input type="checkbox" name="wpckan_setting_target_blank_enabled" id="wpckan_setting_target_blank_enabled" <?php if ($target_blank_enabled)  echo 'checked="true"'; ?>/>
              <p class="description"><?php _e('if checked, links to datasets will be opened in a new tab/window','wpckan') ?></p>
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
              <p class="description"><?php _e('Path where cached files are going to be stored. Mind permissions. and make sure the path ends with trailing slash "/"','wpckan') ?></p>
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
              <p class="description"><?php _e('Path to file storing the logs. Mind permissions, file will be created if it does not exist.','wpckan') ?></p>
            </td>
          </tr>
        </table>
        <?php @submit_button(); ?>
    </form>
</div>
