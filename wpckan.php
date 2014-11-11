<?php
/**
 * Plugin Name: wpckan
 * Plugin URI:
 * Description: wpckan is a wordpress plugin that exposes a series of functionalities to bring content stored in CKAN to Wordpress' UI and also provide mechanisms for archiving content generated on Wordpress into a CKAN instance.
 * Version: 0.1
 * Author: Alex Corbi
 * Author URI: http://www.open-steps.org
 * License: TBD
 */

if(!class_exists('wpckan'))
{
    class wpckan
    {
        /**
         * Construct the plugin object
         */
        public function __construct()
        {
            // register actions
            add_action('admin_init', array(&$this, 'admin_init'));
            add_action('admin_menu', array(&$this, 'add_menu'));
        } // END public function __construct

        /**
         * Activate the plugin
         */
        public static function activate()
        {
            // Do nothing
        } // END public static function activate

        /**
         * Deactivate the plugin
         */
        public static function deactivate()
        {
            // Do nothing
        } // END public static function deactivate


        /**
         * hook into WP's admin_init action hook
         */
        public function admin_init()
        {
            // Set up the settings for this plugin
            $this->init_settings();
            // Possibly do additional admin_init tasks
        }

        /**
         * Initialize some custom settings
         */
        public function init_settings()
        {
            // register the settings for this plugin
            register_setting('wpckan-group', 'setting_ckan_url' , 'sanitize_url');
            register_setting('wpckan-group', 'setting_ckan_api');
            register_setting('wpckan-group', 'setting_archive_freq');
        }

        function sanitize_url($input) {
          return esc_url($input);
        }

        /**
         * add a menu
         */
        public function add_menu()
        {
            add_options_page('WPCKAN Settings', 'wpckan', 'manage_options', 'wpckan', array(&$this, 'plugin_settings_page'));
        } // END public function add_menu()

        /**
         * Menu Callback
         */
        public function plugin_settings_page()
        {
            if(!current_user_can('manage_options'))
            {
                wp_die(__('You do not have sufficient permissions to access this page.'));
            }

            // Render the settings template
            include(sprintf("%s/templates/settings.php", dirname(__FILE__)));
        }

    } // END class wpckan-Plugin
} // END if(!class_exists('wpckan'))

if(class_exists('wpckan'))
{
    // Installation and uninstallation hooks
    register_activation_hook(__FILE__, array('wpckan', 'activate'));
    register_deactivation_hook(__FILE__, array('wpckan', 'deactivate'));

    // instantiate the plugin class
    $wpckan = new wpckan();

    // Add a link to the settings page onto the plugin page
    if(isset($wpckan))
    {
        // Add the settings link to the plugins page
        function plugin_settings_link($links)
        {
            $settings_link = '<a href="options-general.php?page=wpckan">Settings</a>';
            array_unshift($links, $settings_link);
            return $links;
        }

        $plugin = plugin_basename(__FILE__);
        add_filter("plugin_action_links_$plugin", 'plugin_settings_link');
    }
}

?>
