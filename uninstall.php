<?php

 if ( !defined( 'WP_UNINSTALL_PLUGIN' ) )
  exit;

 if ( get_option( 'setting_ckan_url' ) != false ) {
  delete_option( 'setting_ckan_url' );
 }

 if ( get_option( 'setting_ckan_api' ) != false ) {
  delete_option( 'setting_ckan_api' );
 }

 if ( get_option( 'setting_archive_freq' ) != false ) {
  delete_option( 'setting_archive_freq' );
 }

 if ( get_option( 'setting_ckan_organization' ) != false ) {
  delete_option( 'setting_ckan_organization' );
 }

 if ( get_option( 'setting_ckan_group' ) != false ) {
  delete_option( 'setting_ckan_group' );
 }

 if ( get_option( 'setting_ckan_valid_settings_read' ) != false ) {
  delete_option( 'setting_ckan_valid_settings_read' );
 }

 if ( get_option( 'setting_ckan_valid_settings_write' ) != false ) {
  delete_option( 'setting_ckan_valid_settings_write' );
 }

 if ( get_option( 'setting_log_path' ) != false ) {
  delete_option( 'setting_log_path' );
 }

 if ( get_option( 'setting_log_enabled' ) != false ) {
  delete_option( 'setting_log_enabled' );
 }
?>
