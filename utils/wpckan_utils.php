<?php

  define("FREQ_NEVER","0");
  define("FREQ_POST_PUBLISHED","1");
  define("FREQ_POST_SAVED","2");
  define("FREQ_DAILY","3");
  define("FREQ_WEEKLY","4");
  define("DEFAULT_LOG","/tmp/wpckan.log");

  use Analog\Analog;
  use Analog\Handler;

  function wpckan_do_get_related_datasets($post_id) {
    return "<p>Related datasets for post with id: ". $post_id . "</p>";
  }

  /*
  * Templates
  */

  function wpckan_api_show_dataset_list($dataset_array){
    return wpckan_output_template( plugin_dir_path( __FILE__ ) . '../templates/dataset_list.php',$dataset_array);
  }

  function wpckan_api_show_dataset_detail($dataset){
    return wpckan_output_template(plugin_dir_path( __FILE__ ) . '../templates/dataset_detail.php',$dataset);
  }

  function wpckan_api_show_organizations_dropdown($organizations){
    return wpckan_output_template(plugin_dir_path( __FILE__ ) . '../templates/organization_list.php',$organizations);
  }

  function wpckan_api_show_groups_dropdown($groups){
    return wpckan_output_template(plugin_dir_path( __FILE__ ) . '../templates/groups_list.php',$groups);
  }

  /*
  * Logging
  */

  function wpckan_log($text) {
    if (!IsNullOrEmptyString(get_option('setting_ckan_log_path')))
      Analog::handler(Handler\File::init (get_option('setting_ckan_log_path')));
    else
      Analog::handler(Handler\File::init (DEFAULT_LOG));

    Analog::log ($text);
  }

  /*
  * Utilities
  */

  function wpckan_output_template($template_url,$data){
    ob_start();
    require $template_url;
    $output = ob_get_contents();
    ob_end_clean();
    return $output;
  }

  function wpckan_validate_settings(){
    return wpckan_api_ping();
  }

  function wpckan_get_ckan_settings(){

      $settings = array(
        'baseUrl' => get_option('setting_ckan_url') . "/api/" ,
        'scheme' => 'http',
        'apiKey' => get_option('setting_ckan_api')
      );

      return $settings;
    }

  function wpckan_post_should_be_archived_on_publish($post_ID){
    return (get_option('setting_archive_freq') == FREQ_POST_PUBLISHED);
  }

  function wpckan_post_should_be_archived_on_save($post_ID){
    return (get_option('setting_archive_freq') == FREQ_POST_SAVED);
  }

  function wpckan_sanitize_url($input) {
    return esc_url($input);
  }

  function IsNullOrEmptyString($question){
    return (!isset($question) || trim($question)==='');
  }

?>
