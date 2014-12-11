<?php

  include_once plugin_dir_path( __FILE__ ) . 'wpckan_utils.php' ;
  use Silex\ckan\CkanClient;

  /*
  * Api
  */

  function wpckan_api_query_datasets($atts) {

    if (is_null(wpckan_get_ckan_settings()))
      return wpckan_api_settings_error("wpckan_api_query_datasets");

    if (!isset($atts['query']))
      return wpckan_api_call_error("wpckan_api_query_datasets",null);

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

      wpckan_log("wpckan_api_query_datasets commandName: " . $commandName . " arguments: " . print_r($arguments,true));

      if ($response["success"]==false){
        return wpckan_api_call_error("wpckan_api_query_datasets",null);
      }

    } catch (Exception $e){
      return wpckan_api_call_error("wpckan_api_query_datasets",$e->getMessage());
    }

    return $response["result"]["results"];

  }

  function wpckan_api_get_organizations_list() {

    if (is_null(wpckan_get_ckan_settings()))
      return wpckan_api_settings_error("wpckan_get_organizations_list");

    try{
      $ckanClient = CkanClient::factory(wpckan_get_ckan_settings());
      $commandName = 'GetOrganizations';
      $arguments = array('all_fields' => true);
      $command = $ckanClient->getCommand($commandName,$arguments);
      $response = $command->execute();

      wpckan_log("wpckan_get_organizations_list commandName: " . $commandName . " arguments: " . print_r($arguments,true));

      if ($response["success"]==false){
        return wpckan_api_call_error("wpckan_do_get_organizations",null);
      }
    } catch (Exception $e){
      return wpckan_api_call_error("wpckan_get_organizations_list",$e->getMessage());
    }

    return $response["result"];

  }

  function wpckan_api_get_groups_list() {

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

    return $response["result"];

  }

  //TODO improve
  function wpckan_api_archive_post_as_dataset($post){

    if (is_null(wpckan_get_ckan_settings()))
      return wpckan_api_settings_error("wpckan_api_archive_post_as_dataset");

    if (!isset($post))
      return wpckan_api_call_error("wpckan_api_archive_post_as_dataset",null);

    //TODO Add permalink on extras
    //$extras = array(array('published_under' => get_permalink($post->ID)));

    $ckanClient = CkanClient::factory(wpckan_get_ckan_settings());
    $data = array('name' => $post->post_name,
                  'title' => $post->post_title,
                  'notes' => $post->post_content);

    $archive_orga = get_post_meta( $post->ID, 'wpckan_related_dataset_url', true );
    $archive_group = get_post_meta( $post->ID, 'wpckan_archive_post_group', true );

    if ($archive_orga && $archive_orga!=-1)
      $data['owner_org'] = $archive_orga;
    if ($archive_group && $archive_group!=="-1")
      $data['groups'] = array(array('id' => $archive_group));

    if (count(wpckan_api_search_package_with_id($post->post_name))==0){
      $commandName = 'PackageCreate';
    }else{
      $commandName = 'PackageUpdate';
      $data['id'] = $post->post_name;
    }

    try {

      wpckan_log(json_encode($data));

      $ckanClient = CkanClient::factory(wpckan_get_ckan_settings());
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

?>
