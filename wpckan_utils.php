<?php

  define("FREQ_POST_PUBLISHED","0");
  define("FREQ_POST_SAVED","1");
  define("FREQ_DAILY","2");
  define("FREQ_WEEKLY","3");

  require 'vendor/autoload.php';
  use Silex\ckan\CkanClient;
  use Analog\Analog;
  use Analog\Handler;

  function wpckan_do_get_related_datasets($post_id) {
    return "<p>Related datasets for post with id: ". $post_id . "</p>";
  }

  function wpckan_do_query_datasets($atts) {

    if (is_null(wpckan_get_ckan_settings()))
      return wpckan_api_settings_error("wpckan_do_query_datasets");

    if (!isset($atts['query']))
      return wpckan_api_call_error("wpckan_do_query_datasets",null);

    try{

      $ckanClient = CkanClient::factory(wpckan_get_ckan_settings());
      $commandName = 'PackageSearch';
      $arguments = array('q' => $atts['query']);

      $filter = null;
      if (isset($atts['organization'])) $filter = $filter . "+owner_org:" . $atts['organization'];
      if (isset($atts['organization']) && isset($atts['group'])) $filter = $filter . " ";
      if (isset($atts['group'])) $filter = $filter . "+groups:" . $atts['group'];
      if (!is_null($filter)){
        $arguments["fq"] = $filter;
      }
      $command = $ckanClient->getCommand($commandName,$arguments);
      $response = $command->execute();

      wpckan_log("wpckan_do_query_datasets commandName: " . $commandName . " arguments: " . print_r($arguments,true));

      if ($response["success"]==false){
        return wpckan_api_call_error("wpckan_do_query_datasets",null);
      }

    } catch (Exception $e){
      return wpckan_api_call_error("wpckan_do_query_datasets",$e->getMessage());
    }

    return wpckan_api_show_dataset_list($response["result"]["results"]);

  }

  function wpckan_do_get_organizations_list() {

    if (is_null(wpckan_get_ckan_settings()))
      return wpckan_api_settings_error("wpckan_do_get_organizations");

    try{
      $ckanClient = CkanClient::factory(wpckan_get_ckan_settings());
      $commandName = 'GetOrganizations';
      $arguments = array('all_fields' => true);
      $command = $ckanClient->getCommand($commandName,$arguments);
      $response = $command->execute();

      wpckan_log("wpckan_do_get_organizations_list commandName: " . $commandName . " arguments: " . print_r($arguments,true));

      if ($response["success"]==false){
        return wpckan_api_call_error("wpckan_do_get_organizations",null);
      }
    } catch (Exception $e){
      return wpckan_api_call_error("wpckan_do_get_organizations_list",$e->getMessage());
    }

    return wpckan_api_show_organizations_dropdown($response["result"]);

  }

  function wpckan_do_get_groups_list() {

    if (is_null(wpckan_get_ckan_settings()))
    return wpckan_api_settings_error("wpckan_do_get_groups");

    try{
      $ckanClient = CkanClient::factory(wpckan_get_ckan_settings());
      $commandName = 'GetGroups';
      $arguments = array('all_fields' => true);
      $command = $ckanClient->getCommand($commandName,$arguments);
      $response = $command->execute();

      wpckan_log("wpckan_do_get_groups_list commandName: " . $commandName . " arguments: " . print_r($arguments,true));

      if ($response["success"]==false){
        return wpckan_api_call_error("wpckan_do_get_groups",null);
      }
    } catch (Exception $e){
      return wpckan_api_call_error("wpckan_do_get_groups_list",$e->getMessage());
    }

    return wpckan_api_show_groups_dropdown($response["result"]);

  }

  function wpckan_api_archive_post_as_dataset($post){

    if (is_null(wpckan_get_ckan_settings()))
      return wpckan_api_settings_error("wpckan_api_archive_post_as_dataset");

    if (!isset($post))
      return wpckan_api_call_error("wpckan_api_archive_post_as_dataset",null);

    $ckanClient = CkanClient::factory(wpckan_get_ckan_settings());
    $data = array('name' => $post->post_name,
                  'title' => $post->post_title,
                  'notes' => $post->post_content);
    if (get_option('setting_ckan_organization') && get_option('setting_ckan_organization')!=-1)
      $data['owner_org'] = get_option('setting_ckan_organization');
    if (get_option('setting_ckan_group') && get_option('setting_ckan_group')!==-1)
      $data['groups'] = array(array('id' => get_option('setting_ckan_group')));

    if (count(wpckan_api_search_package_with_id($post->post_title))==0){
      $commandName = 'PackageCreate';
    }else{
      $commandName = 'PackageUpdate';
      $data['id'] = $post->post_name;
    }

    try {

      $arguments = array('data' => json_encode($data));
      $command = $ckanClient->getCommand($commandName,$arguments);
      $response = $command->execute();

      wpckan_log("wpckan_api_archive_post_as_dataset commandName: " . $commandName . " arguments: " . print_r($arguments,true));

      if ($response["success"]==false){
        return wpckan_api_call_error("wpckan_api_archive_post_as_dataset",null);
      }
    } catch (Exception $e){
      return wpckan_api_call_error("wpckan_api_archive_post_as_dataset",$e->getMessage());
    }

  }

  /*
  * Templates
  */

  function wpckan_api_show_dataset_list($dataset_array){
    return wpckan_output_template('templates/dataset_list.php',$dataset_array);
  }

  function wpckan_api_show_dataset_detail($dataset){
    return wpckan_output_template('templates/dataset_detail.php',$dataset);
  }

  function wpckan_api_show_organizations_dropdown($organizations){
    return wpckan_output_template('templates/organization_list.php',$organizations);
  }

  function wpckan_api_show_groups_dropdown($groups){
    return wpckan_output_template('templates/groups_list.php',$groups);
  }

  /*
  * Api
  */

  // function wpckan_api_get_config() {
  //
  //   try{
  //
  //     $ckanClient = CkanClient::factory(wpckan_get_ckan_settings());
  //     $commandName = 'StatusShow';
  //     $arguments = array();
  //     $command = $ckanClient->getCommand($commandName,$arguments);
  //     $response = $command->execute();
  //
  //     wpckan_log("wpckan_api_get_config commandName: " . $commandName . " arguments: " . print_r($arguments,true));
  //
  //   } catch (Exception $e){
  //     return wpckan_api_call_error("wpckan_api_get_config",$e->getMessage());
  //   }
  //
  //   wpckan_log(print_r($response,true));
  //
  // }

  function wpckan_api_ping() {

    try{

      $ckanClient = CkanClient::factory(wpckan_get_ckan_settings());
      $commandName = 'SiteRead';
      $arguments = array();
      $command = $ckanClient->getCommand($commandName,$arguments);
      $response = $command->execute();

      wpckan_log("wpckan_do_ping commandName: " . $commandName . " arguments: " . print_r($arguments,true));

    } catch (Exception $e){
      return false;
    }

    return true;

  }

  function wpckan_api_search_package_with_id($id){

    try{

      $ckanClient = CkanClient::factory(wpckan_get_ckan_settings());
      $commandName = 'PackageSearch';
      $arguments = array('fq' => '+name: ' . $id);
      $command = $ckanClient->getCommand($commandName,$arguments);
      $response = $command->execute();

      wpckan_log("wpckan_api_search_package_with_id commandName: " . $commandName . " arguments: " . print_r($arguments,true));

      if ($response["success"]==false){
        return wpckan_api_call_error("wpckan_api_search_package_with_id",null);
      }

    } catch (Exception $e){
      return wpckan_api_call_error("wpckan_api_search_package_with_id",$e->getMessage());
    }

    return $response["result"]["results"];

  }

  /*
  * Errors
  */

  function wpckan_api_parameter_error($function,$message){
    $error_log = "ERROR Parameters on " . $function . " message: " . $message;
    $error_message = "Something went wrong, check your connection details";
    wpckan_log($error_log);
    return __($error_message,'wpckan_api_parameter_error');
  }

  function wpckan_api_call_error($function,$message){
    $error_log = "ERROR API CALL on " . $function . " message: " . $message;
    $error_message = "Something went wrong, check your connection details";
    wpckan_log($error_log);
    return __($error_message,'wpckan_api_call_error');
  }

  function wpckan_api_settings_error($function,$message){
    $error_log = "ERROR SETTINGS on " . $function . " message: " . $message;
    $error_message = "Please, specify CKAN URL and API Key";
    wpckan_log($error_log);
    return __($error_message,'wpckan_api_settings_error');
  }

  /*
  * Logging
  */

  function wpckan_log($text) {
    if (!is_null(get_option('setting_ckan_log_path')))
      Analog::handler(Handler\File::init (get_option('setting_ckan_log_path')));
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
    if (is_null(wpckan_get_ckan_settings)) return false;
  }

  function wpckan_get_ckan_settings(){

    if ( !get_option('setting_ckan_url') || !get_option('setting_ckan_api') ) return null;

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
