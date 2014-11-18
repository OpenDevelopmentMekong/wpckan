<?php

  define("FREQ_POST_PUBLISHED","0");
  define("FREQ_DAILY","1");
  define("FREQ_WEEKLY","2");

  require 'vendor/autoload.php';
  use Silex\ckan\CkanClient;
  use Analog\Analog;

  function wpckan_log($text) {
    Analog::log ($text);
  }

  function wpckan_post_should_be_archived($post_ID){
    return (get_option('setting_archive_freq') == FREQ_POST_PUBLISHED);
  }

  function wpckan_sanitize_url($input) {
    return esc_url($input);
  }

?>
