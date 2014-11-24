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

include_once "wpckan_utils.php";

if(!class_exists('wpckan'))
{
    class wpckan
    {
        /**
         * Construct the plugin object
         */
        public function __construct()
        {
            add_action('admin_init', array(&$this, 'admin_init'));
            add_action('admin_menu', array(&$this, 'add_menu'));
            add_action('publish_post', array(&$this, 'wpckan_publish_post'));
            add_action('save_post', array(&$this, 'wpckan_save_post'));
            add_action('add_meta_boxes', array(&$this, 'wpckan_add_dataset_meta_box'));
            add_shortcode('wpckan_related_dataset', array(&$this, 'wpckan_do_get_related_dataset'));
        }

        function wpckan_do_get_related_dataset($atts) {

          wpckan_log("wpckan_do_get_related_dataset: " . $atts['post_id']);
          $post_id = $atts['post_id'];
          return "Related datasets for post with id: ". $post_id;

        }

        function wpckan_add_dataset_meta_box($post_type) {

          $post_types = array( 'post', 'page' );
          if ( in_array( $post_type, $post_types )) {
              add_meta_box('wpckan_add_related_dataset',__( 'Add related CKAN content', 'wpckan_add_related_dataset_title' ),array(&$this, 'wpckan_render_dataset_meta_box'),$post_type,'side','high');
          }

        }

        function wpckan_render_dataset_meta_box( $post ) {

          // Add an nonce field so we can check for it later.
          wp_nonce_field( 'wpckan_add_related_dataset', 'wpckan_add_related_dataset_nonce' );

          $value = get_post_meta( $post->ID, 'wpckan_related_dataset_url', true );

          echo '<label for="wpckan_dataset_url_field">';
          _e( 'Dataset\'s URL', 'myplugin_textdomain' );
          echo '</label> ';
          echo '<input type="text" id="wpckan_dataset_url_field" name="wpckan_dataset_url_field" value="' . esc_attr( $value ) . '" size="25" />';
        }

        function wpckan_publish_post( $post_ID ) {

          wpckan_log("wpckan_publish_post: " . $post_ID);

          if (wpckan_post_should_be_archived( $post_ID )){
            //TODO
          }

        }

        function wpckan_save_post( $post_ID ) {

          wpckan_log("wpckan_save_post: " . $post_ID);

          // Check if our nonce is set.
          if ( ! isset( $_POST['wpckan_add_related_dataset_nonce'] ) )
            return $post_ID;

          $nonce = $_POST['wpckan_add_related_dataset_nonce'];

          if ( ! wp_verify_nonce( $nonce, 'wpckan_add_related_dataset' ) )
            return $post_ID;

          // If this is an autosave, our form has not been submitted,
          //     so we don't want to do anything.
          if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
             return $post_ID;

          // Check the user's permissions.
          if ( 'page' == $_POST['post_type'] ) {

            if ( ! current_user_can( 'edit_page', $post_id ) )
              return $post_ID;

          } else {

            if ( ! current_user_can( 'edit_post', $post_id ) )
              return $post_ID;
          }

          /* OK, its safe for us to save the data now. */

          // Sanitize the user input.
          $dataset_url = wpckan_sanitize_url( $_POST['wpckan_dataset_url_field'] );

          // Update the meta field.
          update_post_meta( $post_ID, 'wpckan_related_dataset_url', $dataset_url );

          if (wpckan_post_should_be_archived( $post_ID )){
              //TODO
          }

        }

        /**
         * Activate the plugin
         */
        public static function activate()
        {
            // Do nothing
        }

        /**
         * Deactivate the plugin
         */
        public static function deactivate()
        {
            // Do nothing
        }

        /**
         * hook into WP's admin_init action hook
         */
        public function admin_init()
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
        }

        /**
         * add a menu
         */
        public function add_menu()
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
