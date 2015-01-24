<?php

  include_once plugin_dir_path( __FILE__ ) . 'wpckan_utils.php' ;
  use Silex\ckan\CkanClient;

  /*
  * Api
  */

  function wpckan_api_get_dataset($atts) {

    if (is_null(wpckan_get_ckan_settings()))
      wpckan_api_settings_error("wpckan_api_get_dataset");

    if (!isset($atts['id']))
      wpckan_api_call_error("wpckan_api_get_dataset",null);

    try{

      $settings = wpckan_get_ckan_settings();
      $ckanClient = CkanClient::factory($settings);
      $commandName = 'GetDataset';
      $arguments = array('id' => $atts['id'], 'use_default_schema' => true);
      $command = $ckanClient->getCommand($commandName,$arguments);
      $response = $command->execute();

      wpckan_log("wpckan_api_get_dataset commandName: " . $commandName . " arguments: " . print_r($arguments,true) . " settings: " . print_r($settings,true));

      if ($response["success"]==false){
        wpckan_api_call_error("wpckan_api_get_dataset",null);
      }

    } catch (Exception $e){
        wpckan_api_call_error("wpckan_api_get_dataset",$e->getMessage());
    }

    wpckan_log("wpckan_api_get_dataset: " . print_r($response["result"],true));
    return $response["result"];

  }

  function wpckan_api_query_datasets($atts) {

    if (is_null(wpckan_get_ckan_settings()))
      wpckan_api_settings_error("wpckan_api_query_datasets");

    if (!isset($atts['query']))
      wpckan_api_call_error("wpckan_api_query_datasets",null);

    try{

      $settings = wpckan_get_ckan_settings();
      $ckanClient = CkanClient::factory($settings);
      $commandName = 'PackageSearch';
      $arguments = array('q' => $atts['query']);

      if (isset($atts['limit'])){
        $arguments['rows'] = (int)$atts['limit'];

        if (isset($atts['page'])){
          $page = (int)$atts['page'];
          if ($page > 0) $arguments['start'] = (int)$atts['limit'] * ($page - 1);
        }
      }

      $filter = null;
      if (isset($atts['organization'])) $filter = $filter . "+owner_org:" . $atts['organization'];
      if (isset($atts['organization']) && isset($atts['group'])) $filter = $filter . " ";
      if (isset($atts['group'])) $filter = $filter . "+groups:" . $atts['group'];
      if (!is_null($filter)){
        $arguments["fq"] = $filter;
      }
      $command = $ckanClient->getCommand($commandName,$arguments);
      $response = $command->execute();

      wpckan_log("wpckan_api_query_datasets commandName: " . $commandName . " arguments: " . print_r($arguments,true) . " settings: " . print_r($settings,true));

      if ($response["success"]==false){
        wpckan_api_call_error("wpckan_api_query_datasets",null);
      }

    } catch (Exception $e){
        wpckan_api_call_error("wpckan_api_query_datasets",$e->getMessage());
    }

    return $response["result"];

  }

  function wpckan_api_get_organizations_list() {

    if (is_null(wpckan_get_ckan_settings()))
      wpckan_api_settings_error("wpckan_get_organizations_list");

    try{

      $settings = wpckan_get_ckan_settings();
      $ckanClient = CkanClient::factory($settings);
      $commandName = 'GetOrganizations';
      $arguments = array('all_fields' => true);
      $command = $ckanClient->getCommand($commandName,$arguments);
      $response = $command->execute();

      wpckan_log("wpckan_get_organizations_list commandName: " . $commandName . " arguments: " . print_r($arguments,true) . " settings: " . print_r($settings,true));

      if ($response["success"]==false){
        wpckan_api_call_error("wpckan_do_get_organizations",null);
      }
    } catch (Exception $e){
        wpckan_api_call_error("wpckan_get_organizations_list",$e->getMessage());
    }

    return $response["result"];

  }

  function wpckan_api_get_groups_list() {

    if (is_null(wpckan_get_ckan_settings()))
      wpckan_api_settings_error("wpckan_do_get_groups");

    try{

      $settings = wpckan_get_ckan_settings();
      $ckanClient = CkanClient::factory($settings);
      $commandName = 'GetGroups';
      $arguments = array('all_fields' => true);
      $command = $ckanClient->getCommand($commandName,$arguments);
      $response = $command->execute();

      wpckan_log("wpckan_do_get_groups_list commandName: " . $commandName . " arguments: " . print_r($arguments,true) . " settings: " . print_r($settings,true));

      if ($response["success"]==false){
        wpckan_api_call_error("wpckan_do_get_groups",null);
      }
    } catch (Exception $e){
        wpckan_api_call_error("wpckan_do_get_groups_list",$e->getMessage());
    }

    return $response["result"];

  }

  function wpckan_api_archive_post_as_dataset($post){

    if (is_null(wpckan_get_ckan_settings()))
      wpckan_api_settings_error("wpckan_api_archive_post_as_dataset");

    if (!isset($post))
      wpckan_api_call_error("wpckan_api_archive_post_as_dataset",null);

    // $extras = array(array('published_under' => get_permalink($post->ID)));

    $ckanClient = CkanClient::factory(wpckan_get_ckan_settings());
    $data = array('name' => $post->post_name,
                  'title' => $post->post_title,
                  'notes' => wpckan_cleanup_text_for_archiving($post->post_content));

    $archive_orga = get_post_meta( $post->ID, 'wpckan_archive_post_orga', true );
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

      $settings = wpckan_get_ckan_settings();
      $ckanClient = CkanClient::factory($settings);
      $arguments = array('data' => json_encode($data));
      $command = $ckanClient->getCommand($commandName,$arguments);
      $response = $command->execute();

      wpckan_log("wpckan_api_archive_post_as_dataset commandName: " . $commandName . " arguments: " . print_r($arguments,true) . " settings: " . print_r($settings,true));

      if ($response["success"]==false){
        wpckan_api_call_error("wpckan_api_archive_post_as_dataset",null);
      }
    } catch (Exception $e){
        wpckan_api_call_error("wpckan_api_archive_post_as_dataset",$e->getMessage());
    }

  }

  function wpckan_api_search_package_with_id($id){

    try{

      $settings = wpckan_get_ckan_settings();
      $ckanClient = CkanClient::factory($settings);
      $commandName = 'PackageSearch';
      $arguments = array('fq' => '+name: ' . $id);
      $command = $ckanClient->getCommand($commandName,$arguments);
      $response = $command->execute();

      wpckan_log("wpckan_api_search_package_with_id commandName: " . $commandName . " arguments: " . print_r($arguments,true) . " settings: " . print_r($settings,true));

      if ($response["success"]==false){
        wpckan_api_call_error("wpckan_api_search_package_with_id",null);
      }

    } catch (Exception $e){
        wpckan_api_call_error("wpckan_api_search_package_with_id",$e->getMessage());
    }

    return $response["result"]["results"];

  }

  function wpckan_api_get_organization_list_for_user() {
    wpckan_log("wpckan_api_get_organization_list_for_user");

    try{

      $settings = wpckan_get_ckan_settings();
      $ckanClient = CkanClient::factory($settings);
      $commandName = 'GetOrganizationsUserIsMemberOf';
      $arguments = array('permission' => 'create_dataset');
      $command = $ckanClient->getCommand($commandName,$arguments);
      $response = $command->execute();

      wpckan_log("wpckan_api_get_organization_list_for_user commandName: " . $commandName . " arguments: " . print_r($arguments,true) . " settings: " . print_r($settings,true));

    } catch (Exception $e){
        wpckan_api_call_error("wpckan_api_get_organization_list_for_user",$e->getMessage());
    }

    return $response["result"];

  }

  function wpckan_api_get_organization($id) {
    wpckan_log("wpckan_api_get_organization");

    try{

      $settings = wpckan_get_ckan_settings();
      $ckanClient = CkanClient::factory($settings);
      $commandName = 'GetGroup';
      $arguments = array('id' => $id, 'include_datasets'=> false);
      $command = $ckanClient->getCommand($commandName,$arguments);
      $response = $command->execute();

      wpckan_log("wpckan_api_get_organization commandName: " . $commandName . " arguments: " . print_r($arguments,true) . " settings: " . print_r($settings,true));

    } catch (Exception $e){
        wpckan_api_call_error("wpckan_api_get_organization",$e->getMessage());
    }

    return $response["result"];

  }

  function wpckan_api_get_group_list_for_user() {
    wpckan_log("wpckan_api_get_group_list_for_user");

    try{

      $settings = wpckan_get_ckan_settings();
      $ckanClient = CkanClient::factory($settings);
      $commandName = 'GetGroupsUserCanEdit';
      $arguments = array();
      $command = $ckanClient->getCommand($commandName,$arguments);
      $response = $command->execute();

      wpckan_log("wpckan_api_get_group_list_for_user commandName: " . $commandName . " arguments: " . print_r($arguments,true) . " settings: " . print_r($settings,true));

    } catch (Exception $e){
        wpckan_api_call_error("wpckan_api_get_group_list_for_user",$e->getMessage());
    }

    return $response["result"];

  }

  function wpckan_api_user_show($id) {
    wpckan_log("wpckan_api_user_show");

    try{

      $settings = wpckan_get_ckan_settings();
      $ckanClient = CkanClient::factory($settings);
      $commandName = 'GetUser';
      $arguments = array('id' => $id);
      $command = $ckanClient->getCommand($commandName,$arguments);
      $response = $command->execute();

      wpckan_log("wpckan_api_user_show commandName: " . $commandName . " arguments: " . print_r($arguments,true) . " settings: " . print_r($settings,true));

    } catch (Exception $e){
        wpckan_api_call_error("wpckan_api_user_show",$e->getMessage());
    }

    wpckan_log($response);
    return $response["result"];

  }

  function wpckan_api_ping() {
    wpckan_log("wpckan_api_ping");
    try{

      $settings = wpckan_get_ckan_settings();
      $ckanClient = CkanClient::factory($settings);
      $commandName = 'SiteRead';
      $arguments = array();
      $command = $ckanClient->getCommand($commandName,$arguments);
      $response = $command->execute();

      wpckan_log("wpckan_do_ping commandName: " . $commandName . " arguments: " . print_r($arguments,true) . " settings: " . print_r($settings,true));

    } catch (Exception $e){
      wpckan_api_call_error("wpckan_api_ping",$e->getMessage());
    }

    return true;

  }

  function wpckan_api_status_show() {
    wpckan_log("wpckan_api_status_show");

    try{

      $settings = wpckan_get_ckan_settings();
      $ckanClient = CkanClient::factory($settings);
      $commandName = 'StatusShow';
      $arguments = array();
      $command = $ckanClient->getCommand($commandName,$arguments);
      $response = $command->execute();

      wpckan_log("wpckan_api_status_show commandName: " . $commandName . " arguments: " . print_r($arguments,true) . " settings: " . print_r($settings,true));

    } catch (Exception $e){
        wpckan_api_call_error("wpckan_api_status_show",$e->getMessage());
    }

    return $response["result"];

  }

  /*
  * Errors
  */

  function wpckan_api_parameter_error($function,$message){
    $error_log = "ERROR Parameters on " . $function . " message: " . $message;
    $error_message = "Something went wrong, check your connection details";
    //wpckan_log($error_log);
    throw new ApiParametersException($error_message);
    // return __($error_message,'wpckan_api_parameter_error');
  }

  function wpckan_api_call_error($function,$message){
    $error_log = "ERROR API CALL on " . $function . " message: " . $message;
    $error_message = "Something went wrong, check your connection details";
    //wpckan_log($error_log);
    throw new ApiCallException($error_message);
    // return __($error_message,'wpckan_api_call_error');
  }

  function wpckan_api_settings_error($function,$message){
    $error_log = "ERROR SETTINGS on " . $function . " message: " . $message;
    $error_message = "Please, specify CKAN URL and API Key";
    // wpckan_log($error_log);
    throw new ApiSettingsException($error_message);
    // return __($error_message,'wpckan_api_settings_error');
  }

?>
