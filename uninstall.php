<?php

 if ( !defined( 'WP_UNINSTALL_PLUGIN' ) )
  exit;

 if ( get_option( 'setting_ckan_api' ) != false ) {
  delete_option( 'setting_ckan_api' );
  delete_site_option( 'setting_ckan_api' );
 }

 if ( get_option( 'setting_ckan_url' ) != false ) {
  delete_option( 'setting_ckan_url' );
  delete_site_option( 'setting_ckan_url' );
 }

 if ( get_option( 'setting_log_enabled' ) != false ) {
  delete_option( 'setting_log_enabled' );
  delete_site_option( 'setting_log_enabled' );
 }

 if ( get_option( 'setting_log_path' ) != false ) {
  delete_option( 'setting_log_path' );
  delete_site_option( 'setting_log_path' );
 }

 foreach (get_post_types() as $post_type) {
  $option_name = "setting_supported_post_types_" . $post_type;
  if ( get_option( $option_name ) != false ) {
   delete_option( $option_name );
   delete_site_option( $option_name );
  }
 }

?>
