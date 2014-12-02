<?php

  define("FREQ_POST_PUBLISHED","0");
  define("FREQ_DAILY","1");
  define("FREQ_WEEKLY","2");

  require 'vendor/autoload.php';
  use Silex\ckan\CkanClient;
  use Analog\Analog;

  function wpckan_log($text) {
    // Logs to /tmp/analog.txt
    Analog::log ($text);
  }

  function wpckan_post_should_be_archived($post_ID){
    return (get_option('setting_archive_freq') == FREQ_POST_PUBLISHED);
  }

  function wpckan_sanitize_url($input) {
    return esc_url($input);
  }

  function wpckan_do_get_related_datasets($post_id) {
    return "<p>Related datasets for post with id: ". $post_id . "</p>";
  }

  function wpckan_do_query_datasets($query,$organization,$group) {

    if (is_null(wpckan_get_ckan_settings()))
      return wpckan_api_settings_error("wpckan_do_query_datasets");

    try{

      $ckanClient = CkanClient::factory(wpckan_get_ckan_settings());
      $commandName = 'PackageSearch';
      $arguments = array('q' => $query);
      $filter = "";
      if ($organization) $filter = $filter . "+owner_org:" . $organization;
      if ($group) $filter = $filter . "+groups:" . $group;
      if ($filter != ""){
        $arguments["fq"] = $filter;
      }
      $command = $ckanClient->getCommand($commandName,$arguments);
      $response = $command->execute();

      if ($response["success"]==false){
        return wpckan_api_call_error("wpckan_do_query_datasets");
      }

    } catch (Exception $e){

      wpckan_log("wpckan_do_query_datasets: " . $e->getMessage());

    }

    return wpckan_api_show_dataset_list($response["result"]["results"]);

  }

  function wpckan_do_get_organizations_list() {

    if (is_null(wpckan_get_ckan_settings()))
      return wpckan_api_settings_error("wpckan_do_get_organizations");

    try{

      $ckanClient = CkanClient::factory(wpckan_get_ckan_settings());
      $commandName = 'GetGroups';
      $arguments = array('all_fields' => true);
      $command = $ckanClient->getCommand($commandName,$arguments);
      $response = $command->execute();

      if ($response["success"]==false){
        return wpckan_api_call_error("wpckan_do_get_organizations");
      }

    } catch (Exception $e){

      wpckan_log("wpckan_do_query_datasets: " . $e->getMessage());
      return wpckan_api_call_error("wpckan_do_get_organizations");

    }

    return wpckan_api_show_organizations_dropdown($response["result"]);

  }

  function wpckan_api_show_dataset_list($dataset_array){
    require 'templates/dataset_list.php';
  }

  function wpckan_api_show_dataset_detail($dataset){
    require 'templates/dataset_detail.php';
  }

  function wpckan_api_show_organizations_dropdown($organizations){
    require 'templates/organization_list.php';
  }

  function wpckan_api_archive_post_as_dataset($post){

    if (is_null(wpckan_get_ckan_settings()))
      return wpckan_api_settings_error("wpckan_api_archive_post_as_dataset");

    try {

      $ckanClient = CkanClient::factory(wpckan_get_ckan_settings());
      $commandName = 'PackageCreate';
      $arguments = array('name' => $post->post_name);
      $arguments["title"] = $post->post_title;
      $arguments["notes"] = $post->post_content;
      if (get_option('setting_ckan_orga')) $arguments["owner_org "] = get_option('setting_ckan_orga');

      $command = $ckanClient->getCommand($commandName,$arguments);
      $response = $command->execute();

      if ($response["success"]==false){
        return wpckan_api_call_error("wpckan_api_archive_post_as_dataset");
      }

    } catch (Exception $e){

      wpckan_log("wpckan_api_archive_post_as_dataset: " . $e->getMessage());

    }

  }

  function wpckan_api_call_error($function){
    $error_log = "ERROR API CALL on " . $function;
    $error_message = "Something went wrong";
    wpckan_log($error_log);
    return __($error_log,'wpckan_api_call_error');
  }

  function wpckan_api_settings_error($function){
    $error_log = "ERROR SETTINGS on " . $function;
    $error_message = "Please, specify CKAN URL and API Key";
    wpckan_log($error_log);
    return __($error_message,'wpckan_api_settings_error');
  }

  function wpckan_get_ckan_settings(){

    if ( (get_option('setting_ckan_url') == "http://") || (get_option('setting_ckan_api') == "") ) return null;

    $settings = array(
      'baseUrl' => get_option('setting_ckan_url') . "/api/" ,
      'scheme' => 'http',
      'apiKey' => get_option('setting_ckan_api')
    );

    return $settings;
  }

?>
