<?php
/**
 * Plugin Name: wp-ckan
 * Plugin URI:
 * Description: wp-ckan is a wordpress plugin that exposes a series of functionalities to bring content stored in CKAN to Wordpress' UI and also provide mechanisms for archiving content generated on Wordpress into a CKAN instance.
 * Version: 0.1
 * Author: Alex Corbi
 * Author URI: http://www.open-steps.org
 * License: TBD
 */

if(!class_exists('wp-ckan'))
{
    class wp-ckan
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

        // Installation and uninstallation hooks
        register_activation_hook(__FILE__, array('wp-ckan', 'activate'));
        register_deactivation_hook(__FILE__, array('wp-ckan', 'deactivate'));

        // instantiate the plugin class
        $wp-ckan = new wp-ckan();

        // Add a link to the settings page onto the plugin page
        if(isset($wp-ckan))
        {
            // Add the settings link to the plugins page
            function plugin_settings_link($links)
            {
                $settings_link = '<a href="options-general.php?page=wp-ckan">Settings</a>';
                array_unshift($links, $settings_link);
                return $links;
            }

            $plugin = plugin_basename(__FILE__);
            add_filter("plugin_action_links_$plugin", 'plugin_settings_link');
        }


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
            register_setting('wp-ckan-group', 'setting_a');
            register_setting('wp-ckan-group', 'setting_b');
        }

        /**
         * add a menu
         */
        public function add_menu()
        {
            add_options_page('WP-CKAN Settings', 'WP-CKAN', 'manage_options', 'wp-ckan', array(&$this, 'plugin_settings_page'));
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

    } // END class WP-CKAN-Plugin
} // END if(!class_exists('WP-CKAN-Plugin'))

?>
