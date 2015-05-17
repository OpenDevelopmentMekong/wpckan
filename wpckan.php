<?php
/**
 * Plugin Name: wpckan
 * Plugin URI: http://www.lifeformapps.com/portfolio/wpckan/
 * Description: wpckan is a wordpress plugin that exposes a series of functionalities to bring content stored in CKAN to Wordpress' UI and also provide mechanisms for archiving content generated on Wordpress into a CKAN instance.
 * Version: 1.2.0
 * Author: Alex Corbi (mail@lifeformapps.com)
 * Author URI: http://www.lifeformapps.com
 * License: GPLv3
 */

 require 'vendor/autoload.php';
 include_once plugin_dir_path( __FILE__ ) . 'utils/wpckan_exceptions.php' ;
 include_once plugin_dir_path( __FILE__ ) . 'utils/wpckan_utils.php' ;
 include_once plugin_dir_path( __FILE__ ) . 'utils/wpckan_api.php' ;

if(!class_exists('wpckan'))
{
    class wpckan
    {
        /**
         * Construct the plugin object
         */
        public function __construct()
        {
          add_action('admin_init', array(&$this, 'wpckan_admin_init'));
          add_action('admin_menu', array(&$this, 'wpckan_add_menu'));
          add_action('admin_enqueue_scripts', array( &$this, 'wpckan_register_plugin_styles' ) );
          add_action('edit_post', array(&$this, 'wpckan_edit_post'));
          add_action('save_post', array(&$this, 'wpckan_save_post'));
          add_action('add_meta_boxes', array(&$this, 'wpckan_add_meta_boxes'));
          add_shortcode('wpckan_related_datasets', array(&$this, 'wpckan_do_shortcode_get_related_datasets'));
          add_shortcode('wpckan_number_of_related_datasets', array(&$this, 'wpckan_do_shortcode_get_number_of_related_datasets'));
          add_shortcode('wpckan_query_datasets', array(&$this, 'wpckan_do_shortcode_query_datasets'));
        }

        public function wpckan_register_plugin_styles($hook) {
          wpckan_log("wpckan_register_plugin_styles");

          wp_register_style( 'wpckan_css', plugins_url( 'wpckan/css/wpckan_style.css'));
          wp_enqueue_style( 'wpckan_css' );
        }

        function wpckan_do_shortcode_get_related_datasets($atts) {
          wpckan_log("wpckan_do_shortcode_get_related_datasets: " . print_r($atts,true));

          if (!wpckan_validate_settings_read()) return;

          $atts["post_id"] = get_the_ID();
          return wpckan_show_related_datasets($atts);
        }

        function wpckan_do_shortcode_get_number_of_related_datasets($atts) {
          wpckan_log("wpckan_do_shortcode_get_number_of_related_datasets: " . print_r($atts,true));

          if (!wpckan_validate_settings_read()) return;

          $atts["post_id"] = get_the_ID();
          return wpckan_show_number_of_related_datasets($atts);
        }

        function wpckan_do_shortcode_query_datasets($atts) {
          wpckan_log("wpckan_do_query_related_datasets: " . print_r($atts,true));

          if (!wpckan_validate_settings_read()) die;

          return wpckan_show_query_datasets($atts);
        }

        function wpckan_add_meta_boxes($post_type) {
          wpckan_log("wpckan_add_meta_boxes: " . $post_type . " " . print_r(get_post_types(),true));

          $post_types = apply_filters('wpckan_filter_post_types', get_post_types());
          if (in_array( $post_type, $post_types ) && wpckan_is_supported_post_type($post_type)) {
           add_meta_box('wpckan_add_related_datasets',__( 'Add related CKAN content', 'wpckan_add_related_datasets_title' ),array(&$this, 'wpckan_render_dataset_meta_box'),$post_type,'side','high');
           add_meta_box('wpckan_archive_post',__( 'Archive Post as CKAN dataset', 'wpckan_archive_post_title' ),array(&$this, 'wpckan_render_archive_post_meta_box'),$post_type,'side','high');
          }

          wp_register_script( 'wpckan_bloodhound', plugins_url( 'wpckan/vendor/twitter/typeahead.js/dist/bloodhound.min.js'), array('jquery') );
          wp_enqueue_script( 'wpckan_bloodhound');
          wp_register_script( 'wpckan_typeahead', plugins_url( 'wpckan/vendor/twitter/typeahead.js/dist/typeahead.jquery.js'), array('jquery') );
          wp_enqueue_script( 'wpckan_typeahead');
          wp_register_script( 'wpckan_js', plugins_url( 'wpckan/js/wpckan_metabox_logic.js'), array('jquery') );
          wp_enqueue_script( 'wpckan_js');
        }

        function wpckan_render_dataset_meta_box( $post ) {
          wpckan_log("wpckan_render_dataset_meta_box: " . print_r($post,true));

          wp_nonce_field('wpckan_add_related_datasets', 'wpckan_add_related_datasets_nonce');
          $related_datasets_json = get_post_meta( $post->ID, 'wpckan_related_datasets', true );
          $related_datasets = array();
          if (!wpckan_is_null_or_empty_string($related_datasets_json))
            $related_datasets = json_decode($related_datasets_json,true);

          //We do not use wpckan_output_template here, just require.
          require 'templates/related_datasets_metabox.php';
        }

        function wpckan_render_archive_post_meta_box( $post ) {
          wpckan_log("wpckan_render_archive_post_meta_box: " . print_r($post,true));

          wp_nonce_field('wpckan_archive_post', 'wpckan_archive_post_nonce');
          $archive_orga = get_post_meta( $post->ID, 'wpckan_archive_post_orga', true );
          $archive_group = get_post_meta( $post->ID, 'wpckan_archive_post_group', true );
          $archive_freq = get_post_meta( $post->ID, 'wpckan_archive_post_freq', true );
          //We do not use wpckan_output_template here, just require.
          require 'templates/archive_post_metabox.php';
        }

        function wpckan_save_post( $post_ID ) {
          wpckan_log("wpckan_save_post: " . $post_ID);

          wpckan_edit_post_logic_dataset_metabox($post_ID);
          wpckan_edit_post_logic_archive_post_metabox($post_ID);
        }

        function wpckan_edit_post( $post_ID ) {
          wpckan_log("wpckan_edit_post: " . $post_ID);

          // If this is an autosave, our form has not been submitted,
          //     so we don't want to do anything.
          if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
             return $post_ID;

          // Check the user's permissions.
          if ( 'page' == $_POST['post_type'] ) {

            if ( ! current_user_can( 'edit_page', $post_ID ) )
              return $post_ID;

          } else {

            if ( ! current_user_can( 'edit_post', $post_ID ) )
              return $post_ID;
          }

          wpckan_edit_post_logic_dataset_metabox($post_ID);
          wpckan_edit_post_logic_archive_post_metabox($post_ID);
        }

        /**
         * Activate the plugin
         */
        public static function activate()
        {
            // Do nothing
            wpckan_log('wpckan plugin activated');
        }

        /**
         * Deactivate the plugin
         */
        public static function deactivate()
        {
            // Do nothing
            wpckan_log('wpckan plugin deactivated');
        }

        /**
         * hook into WP's admin_init action hook
         */
        public function wpckan_admin_init()
        {
            $this->init_settings();

        }

        /**
         * Initialize some custom settings
         */
        public function init_settings()
        {
            register_setting('wpckan-group', 'setting_ckan_url' , 'wpckan_sanitize_url');
            register_setting('wpckan-group', 'setting_ckan_api');
            register_setting('wpckan-group', 'setting_archive_freq');
            register_setting('wpckan-group', 'setting_ckan_organization');
            register_setting('wpckan-group', 'setting_ckan_group');
            register_setting('wpckan-group', 'setting_ckan_valid_settings_read');
            register_setting('wpckan-group', 'setting_ckan_valid_settings_write');
            register_setting('wpckan-group', 'setting_log_path');
            register_setting('wpckan-group', 'setting_log_enabled');

            foreach (get_post_types() as $post_type){
             $settings_name =  "setting_supported_post_types_" . $post_type;
             register_setting('wpckan-group', $settings_name);
            }
        }

        /**
         * add a menu
         */
        public function wpckan_add_menu()
        {
            add_options_page('WPCKAN Settings', 'wpckan', 'manage_options', 'wpckan', array(&$this, 'plugin_settings_page'));
        }

        /**
         * Menu Callback
         */
        public function plugin_settings_page()
        {
            if(!current_user_can('manage_options'))
            {
                wp_die(__('You do not have sufficient permissions to access this page.'));
            }

            include(sprintf("%s/templates/settings.php", dirname(__FILE__)));
        }

    }
}


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
