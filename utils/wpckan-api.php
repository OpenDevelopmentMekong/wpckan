<?php

  use Silex\ckan\CkanClient;

  /*
  * Api
  */

  function wpckan_api_query_datasets($atts) {

    if (is_null(wpckan_get_ckan_settings())):
      wpckan_api_settings_error("wpckan_api_query_datasets");
    endif;

    try{

      $settings = wpckan_get_ckan_settings();
      $ckanClient = CkanClient::factory($settings);
      $commandName = 'PackageSearch';
      $arguments = compose_solr_query_from_attrs($atts);

      $command = $ckanClient->getCommand($commandName,$arguments);
      $response = $command->execute();

      wpckan_log("wpckan_api_query_datasets commandName:" . $commandName . " arguments: " . print_r($arguments,true) . " settings: " . print_r($settings,true));

      if ($response['success']==false){
        wpckan_api_call_error("wpckan_api_query_datasets",null);
      }

    } catch (Exception $e){
        wpckan_api_call_error("wpckan_api_query_datasets",$e->getMessage());
    }

    return $response['result'];

  }

  function wpckan_api_query_dataset_detail($atts) {

    if (is_null(wpckan_get_ckan_settings())):
      wpckan_api_settings_error("wpckan_api_query_dataset_detail");
    endif;

    if (!isset($atts['id'])):
      wpckan_api_call_error("wpckan_api_query_dataset_detail",null);
    endif;

    try{
      $settings = wpckan_get_ckan_settings();
      $ckanClient = CkanClient::factory($settings);
      $commandName = 'GetDataset';
      $arguments = array('id' => $atts['id']);

      $command = $ckanClient->getCommand($commandName,$arguments);
      $response = $command->execute();

      wpckan_log("wpckan_api_query_dataset_detail commandName:" . $commandName . " arguments: " . print_r($arguments,true) . " settings: " . print_r($settings,true));
      if ($response['success']==false):
        wpckan_api_call_error("wpckan_api_query_dataset_detail",null);
      endif;

    } catch (Exception $e){
        wpckan_api_call_error("wpckan_api_query_dataset_detail",$e->getMessage());
    }

    return $response['result'];
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

      if ($response['success']==false){
        wpckan_api_call_error("wpckan_do_get_organizations",null);
      }
    } catch (Exception $e){
        wpckan_api_call_error("wpckan_get_organizations_list",$e->getMessage());
    }

    return $response['result'];

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

      if ($response['success']==false){
        wpckan_api_call_error("wpckan_do_get_groups",null);
      }
    } catch (Exception $e){
        wpckan_api_call_error("wpckan_do_get_groups_list",$e->getMessage());
    }

    return $response['result'];

  }

  function wpckan_api_archive_post_as_dataset($post){

    if (is_null(wpckan_get_ckan_settings()))
      wpckan_api_settings_error("wpckan_api_archive_post_as_dataset");

    if (!isset($post))
      wpckan_api_call_error("wpckan_api_archive_post_as_dataset",null);

    $resources = array();
    $extras = array();
    $custom_fields = get_post_custom($post->ID);
    foreach ( $custom_fields as $key => $value ) {
     if ((substr($key,0,1) == "_") || (substr($key,0,7) == "wpckan_") || (wpckan_is_null_or_empty_string($key)) || (wpckan_is_null_or_empty_string($value)))
       continue;

     $imploded_value = implode(", ", $value);

     if (wpckan_is_valid_url($imploded_value)){
       array_push($resources,array('name' => wpckan_strip_mqtranslate_tags($post->post_title), 'description' => $key, 'url' => $imploded_value, 'format' => wpckan_get_url_extension_or_html($imploded_value)));
     }else{
       array_push($extras,array('key' => $key, 'value' => $imploded_value));
     }

    }

    $ckanClient = CkanClient::factory(wpckan_get_ckan_settings());
    $data = array('name' => $post->post_name,
                  'title' => wpckan_strip_mqtranslate_tags($post->post_title),
                  'notes' => wpckan_cleanup_text_for_archiving($post->post_content),
                  'extras' => $extras);

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

      if ($response['success']==false){
        wpckan_api_call_error("wpckan_api_archive_post_as_dataset",null);
      }
    } catch (Exception $e){
        wpckan_api_call_error("wpckan_api_archive_post_as_dataset",$e->getMessage());
    }

    if ($response['success']==false){
      wpckan_api_call_error("wpckan_api_archive_post_as_dataset",null);
    }

    foreach ($resources as $resource){
      $resource['package_id'] = $response['result']['id'];
      wpckan_api_create_resource($resource);
    }

    return $response['result'];

  }

  function wpckan_api_create_resource($data) {
    wpckan_log("wpckan_api_create_resource");

    try{

      $settings = wpckan_get_ckan_settings();
      $ckanClient = CkanClient::factory($settings);
      $commandName = 'ResourceCreate';
      $arguments = array('data' => json_encode($data));
      $command = $ckanClient->getCommand($commandName,$arguments);
      $response = $command->execute();

      wpckan_log("wpckan_api_create_resource commandName: " . $commandName . " arguments: " . print_r($arguments,true) . " settings: " . print_r($settings,true));

    } catch (Exception $e){
        wpckan_api_call_error("wpckan_api_create_resource",$e->getMessage());
    }

    if ($response['success']==false){
      wpckan_api_call_error("wpckan_do_get_organizations",null);
    }

    return $response['result'];

  }

  function wpckan_api_search_package_with_id($id){

    // Pass OR-separated list of ids if parameter is array
    if (is_array($id)){
      $id = '('. implode("OR ", $id).')';
    }

    try{

      $settings = wpckan_get_ckan_settings();
      $ckanClient = CkanClient::factory($settings);
      $commandName = 'PackageSearch';
      $arguments = array('fq' => '+name: ' . $id);
      $command = $ckanClient->getCommand($commandName,$arguments);
      $response = $command->execute();

      wpckan_log("wpckan_api_search_package_with_id commandName: " . $commandName . " arguments: " . print_r($arguments,true) . " settings: " . print_r($settings,true));

      if ($response['success']==false){
        wpckan_api_call_error("wpckan_api_search_package_with_id",null);
      }

    } catch (Exception $e){
        wpckan_api_call_error("wpckan_api_search_package_with_id",$e->getMessage());
    }

    return $response['result']["results"];

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

    return $response['result'];

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

    return $response['result'];

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

    return $response['result'];

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
    return $response['result'];

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

    return $response['result'];

  }

  function wpckan_get_ckan_domain()
  {
      return $GLOBALS['options']->get_option('wpckan_setting_ckan_url');
  }


  /*
   * Non-guzzle calls
   */

   function wpckan_get_or_cache($url,$id){

     $json = "{}";

     if (!$GLOBALS['options']->get_option('wpckan_setting_cache_enabled')):
       $json = @file_get_contents($url);
     else:
       $json = $GLOBALS['cache']->get_data($id,$url);
     endif;

     return $json;
   }

   function wpckan_get_dataset_by_id($ckan_domain, $id)
   {
       $ckanapi_url = $ckan_domain.'/api/3/action/package_show?id='.$id;

       $json = wpckan_get_or_cache($ckanapi_url,$id);

       if ($json === false) {
           return [];
       }
       $datasets = json_decode($json, true) ?: [];

       return $datasets['result'];
   }

   function wpckan_get_datasets_filter($ckan_domain, $key, $value)
   {
       $ckanapi_url = $ckan_domain.'/api/3/action/package_search?rows=1000&fq='.$key.':'.$value;

       $json = wpckan_get_or_cache($ckanapi_url,$key . $value);

       if ($json === false) {
           return [];
       }
       $datasets = json_decode($json, true) ?: [];

       return $datasets['result']['results'];
   }

   function wpckan_get_datasets_filters($ckan_domain, $filter_array)
   {

        if (empty($filter_array)):
          return [];
        endif;

       $ckanapi_url = $ckan_domain.'/api/3/action/package_search?rows=1000&fq=';
       foreach ($filter_array as $key => $value):
         $ckanapi_url .= "+". $key . ":" . $value;
       endforeach;

       $json = wpckan_get_or_cache($ckanapi_url,$key . $value);

       if ($json === false):
           return [];
       endif;
       $datasets = json_decode($json, true) ?: [];

       return $datasets['result']['results'];
   }

   // TODO: parametrize
   function wpckan_get_metadata_info_of_dataset_by_id($ckan_domain, $ckan_dataset_id, $individual_layer = '', $atlernative_links = 0, $showing_fields = '')
   {
       $lang = CURRENT_LANGUAGE;

       $attribute_metadata = array(
                           //  "title_translated" => "Title",
                             'notes_translated' => 'Description',
                             'odm_source' => 'Source(s)',
                             'odm_completeness' => 'Completeness',
                             'odm_metadata_reference_information' => 'Metadata Reference Information',
                             'odm_process' => 'Process(es)',
                             'odm_attributes' => 'Attributes',
                             'odm_logical_consistency' => 'Logical Consistency',
                             'odm_copyright' => 'Copyright',
                             'version' => 'Version',
                             'odm_date_created' => 'Date created',
                             'odm_date_uploaded' => 'Date uploaded',
                             'odm_temporal_range' => 'Temporal range',
                             'odm_accuracy-en' => 'Accuracy',
                             'odm_logical_consistency' => 'Logical Consistency',
                             'odm_contact' => 'Contact',
                             'odm_access_and_use_constraints' => 'Access and use constraints',
                             'license_id' => 'License',
                         );

     // get ckan record by id
     $get_info_from_ckan = wpckan_get_dataset_by_id($ckan_domain, $ckan_dataset_id);
       ?>
       <div class="layer-toggle-info toggle-info toggle-info-<?php echo $individual_layer['ID'];
       ?>">
           <table border="0" class="toggle-talbe">
             <tr><td colspan="2"><h5><?php echo $get_info_from_ckan['title_translated'][$lang] ?></h5></td></tr>
             <?php
             if ($showing_fields == '') {
                 if ($get_info_from_ckan) {
                     foreach ($get_info_from_ckan as $key => $info) {
                         if ($key == 'license_id') {
                             ?>
                     <tr>
                         <td><?php echo $attribute_metadata['license_id'];
                             ?></td>
                         <td><?php echo $info == 'unspecified' ? ucwords($get_info_from_ckan['license_id']) : $get_info_from_ckan['license_id'];
                             ?></td>
                     </tr>
                 <?php

                         } else {
                             if (array_key_exists($key, $attribute_metadata)) {
                                 ?>    <tr>
                           <td><?php echo $attribute_metadata[$key];
                                 ?></td><td><?php echo is_array($info) ? $info[$lang] : $info;
                                 ?></td>
                       </tr>
             <?php

                             }
                         }
                     }
                 }
             } else {
                 foreach ($showing_fields as $key => $info) {
                     if ($key == 'license_id') {
                         ?>
                   <tr>
                       <td><?php echo $showing_fields['license_id'];
                         ?></td>
                       <td><?php echo $info == 'unspecified' ? ucwords($get_info_from_ckan['license_id']) : $get_info_from_ckan['license_id'];
                         ?></td>
                   </tr>
               <?php

                     } else {
                         if ($get_info_from_ckan) {
                             ?>    <tr>
                           <td><?php echo $showing_fields[$key];
                             ?></td>
                           <td><?php echo is_array($get_info_from_ckan[$key]) ? $get_info_from_ckan[$key][$lang] : $get_info_from_ckan[$key];
                             ?></td>
                       </tr>
           <?php

                         }
                     }
                 }
             }
       ?>
           </table>
         <?php if ($atlernative_links == 1) {
       ?>
           <div class="atlernative_links">
           <?php if ($lang != 'en') {
       ?>
                   <div class="div-button"><a href="<?php echo $individual_layer['download_url_localization'];
       ?>" target="_blank"><i class="fa fa-arrow-down"></i> <?php _e('Download data', 'opendev');
       ?></a></div>

                   <?php if ($individual_layer['profilepage_url_localization']) {
       ?>
                     <div class="div-button"><a href="<?php echo $individual_layer['profilepage_url_localization'];
       ?>" target="_blank"><i class="fa fa-table"></i> <?php _e('View dataset table', 'opendev');
       ?></a></div>
                   <?php

   }
       ?>
           <?php

   } else {
       ?>
                   <div class="div-button"><a href="<?php echo $individual_layer['download_url'];
       ?>" target="_blank"><i class="fa fa-arrow-down"></i> <?php _e('Download data', 'opendev');
       ?></a></div>

                   <?php if ($individual_layer['profilepage_url']) {
       ?>
                     <div class="div-button"><a href="<?php echo $individual_layer['profilepage_url'];
       ?>" target="_blank"><i class="fa fa-table"></i> <?php _e('View dataset table', 'opendev');
       ?></a></div>
                   <?php

   }
       ?>
           <?php

   }
       ?>
           </div>
         <?php

   }
       ?>
       </div>

   <?php

   }

  /*
  * Errors
  */

  function wpckan_api_parameter_error($function,$message){
    $error_log = "ERROR Parameters on " . $function . " message: " . $message;
    $error_message = "Something went wrong, check your connection details";
    wpckan_log($error_log);
    throw new WpckanApiParametersException($error_message);
  }

  function wpckan_api_call_error($function,$message){
    $error_log = "ERROR API CALL on " . $function . " message: " . $message;
    $error_message = "Something went wrong, check your connection details";
    wpckan_log($error_log);
    throw new WpckanApiCallException($error_message);
  }

  function wpckan_api_settings_error($function,$message){
    $error_log = "ERROR SETTINGS on " . $function . " message: " . $message;
    $error_message = "Please, specify CKAN URL and API Key";
    wpckan_log($error_log);
    throw new WpckanApiSettingsException($error_message);
  }

?>
