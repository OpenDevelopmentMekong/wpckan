<div class="wrap">
    <h2>WPCKAN -  A plugin for integrating CKAN and WP</h2>
    <form method="post" action="options.php">
        <?php @settings_fields('wpckan-group'); ?>
        <?php @do_settings_fields('wpckan-group'); ?>

        <table class="form-table">
            <tr valign="top">
                <th scope="row"><label for="setting_ckan_url">CKAN Url</label></th>
                <td><input type="text" name="setting_ckan_url" id="setting_ckan_url" value="<?php echo get_option('setting_ckan_url'); ?>" /></td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="setting_ckan_api">CKAN Apikey</label></th>
                <td><input type="text" name="setting_ckan_api" id="setting_ckan_api" value="<?php echo get_option('setting_ckan_api'); ?>" /></td>
            </tr>
            <tr valign="top">
              <th scope="row"><label for"setting_archive_freq">Archive contents when:</label></th>
              <td>
                <select name="setting_archive_freq" id="setting_archive_freq">
                  <option value="0" <?php if(get_option('setting_ckan_api') == 0) echo 'selected="selected"' ?>>Post is modified</option>
                  <option value="1" <?php if(get_option('setting_ckan_api') == 1) echo 'selected="selected"' ?>>Daily</option>
                  <option value="2" <?php if(get_option('setting_ckan_api') == 2) echo 'selected="selected"' ?>>Weekly</option>
                </select>
              </td>
            </tr>
        </table>
        <?php @submit_button(); ?>
    </form>
</div>
