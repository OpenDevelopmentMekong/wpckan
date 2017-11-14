<?php

  use Silex\ckan\CkanClient;
  use Guzzle\Cache\DoctrineCacheAdapter;
  use Guzzle\Plugin\Cache\CachePlugin;
  use Guzzle\Plugin\Cache\DefaultCacheStorage;
  use Doctrine\Common\Cache\FilesystemCache;

  /*
  * Custom api methods
  */

  function wpckan_api_package_show($ckan_domain, $id)
  {
      $ckanapi_url = $ckan_domain.'/api/3/action/package_show?id='.$id;

      $json = wpckan_get_or_cache($ckanapi_url, $id);

      if ($json === false) {
          return [];
      }
      $datasets = json_decode($json, true) ?: [];

			if (!isset($datasets['result'])):
				return [];
			endif;

      wpckan_log('wpckan_api_package_show result: '. print_r($datasets['result'], true));

      return $datasets['result'];
  }

  function wpckan_api_package_search($ckan_domain, $attrs)
  {
      $query = '?'.compose_solr_query_from_attrs($attrs);
      $ckanapi_url = $ckan_domain.'/api/3/action/package_search'.$query;
      $json = wpckan_get_or_cache($ckanapi_url, $query);

      if ($json === false) {
          return [];
      }
      $datasets = json_decode($json, true) ?: [];

			if (!isset($datasets['result'])):
				return [];
			endif;

			$total_count = $datasets['result']['count'];
			$iteration = 1;
      $limit_set = array_key_exists("limit",$attrs) && $attrs["limit"] > 0;
      while ( !$limit_set && $total_count - ($iteration * 1000) > 0):
				$attrs["limit"] = 1000;
				$attrs["page"] = $iteration + 1;
				$query = '?'.compose_solr_query_from_attrs($attrs);
				$ckanapi_url = $ckan_domain.'/api/3/action/package_search'.$query;

	      $json = wpckan_get_or_cache($ckanapi_url, $query);
				if ($json !== false) {
					$datasets_iteration = json_decode($json, true) ?: [];
	        $datasets['result']['results'] = array_merge($datasets['result']['results'],$datasets_iteration['result']['results']);
					$iteration++;
				}
      endwhile;

      wpckan_log('wpckan_api_package_search result: '. print_r($datasets['result'], true));

      return $datasets['result'];
  }

  /*
  * Guzzle
  */

  function wpckan_get_guzzle_client($settings)
  {
      $ckanClient = CkanClient::factory($settings);

      if ((bool)($GLOBALS['wpckan_options']->get_option('wpckan_setting_cache_enabled'))):
        $cache_plugin = new CachePlugin(array(
            'storage' => new DefaultCacheStorage(
                new DoctrineCacheAdapter(
                    new FilesystemCache('/cache/')
                )
            ),
        ));
        $ckanClient->addSubscriber($cache_plugin);
      endif;

      return $ckanClient;
  }

  function wpckan_api_get_organizations_list()
  {
      if (is_null(wpckan_get_ckan_settings())) {
          wpckan_api_settings_error('wpckan_get_organizations_list');
      }

      try {
          $settings = wpckan_get_ckan_settings();
          $ckanClient = wpckan_get_guzzle_client($settings);
          $commandName = 'GetOrganizations';
          $arguments = array('all_fields' => true);
          $command = $ckanClient->getCommand($commandName, $arguments);
          $response = $command->execute();

          wpckan_log('wpckan_get_organizations_list commandName: '.$commandName.' arguments: '.print_r($arguments, true).' settings: '.print_r($settings, true));

          if ($response['success'] == false) {
              wpckan_api_call_error('wpckan_do_get_organizations', null);
          }
      } catch (Exception $e) {
          wpckan_api_call_error('wpckan_get_organizations_list', $e->getMessage());
      }

      wpckan_log('wpckan_api_get_organizations_list result: '. print_r($response['result'], true));

      return $response['result'];
  }

  function wpckan_api_get_groups_list()
  {
      if (is_null(wpckan_get_ckan_settings())) {
          wpckan_api_settings_error('wpckan_do_get_groups');
      }

      try {
          $settings = wpckan_get_ckan_settings();
          $ckanClient = wpckan_get_guzzle_client($settings);
          $commandName = 'GetGroups';
          $arguments = array('all_fields' => true);
          $command = $ckanClient->getCommand($commandName, $arguments);
          $response = $command->execute();

          wpckan_log('wpckan_do_get_groups_list commandName: '.$commandName.' arguments: '.print_r($arguments, true).' settings: '.print_r($settings, true));

          if ($response['success'] == false) {
              wpckan_api_call_error('wpckan_do_get_groups', null);
          }
      } catch (Exception $e) {
          wpckan_api_call_error('wpckan_do_get_groups_list', $e->getMessage());
      }

      wpckan_log('wpckan_api_get_groups_list result: '. print_r($response['result'], true));

      return $response['result'];
  }

  function wpckan_api_archive_post_as_dataset($post)
  {
      if (is_null(wpckan_get_ckan_settings())) {
          wpckan_api_settings_error('wpckan_api_archive_post_as_dataset');
      }

      if (!isset($post)) {
          wpckan_api_call_error('wpckan_api_archive_post_as_dataset', null);
      }

      $resources = array();
      $extras = array();
      $custom_fields = get_post_custom($post->ID);
      foreach ($custom_fields as $key => $value) {
          if ((substr($key, 0, 1) == '_') || (substr($key, 0, 7) == 'wpckan_') || (wpckan_is_null_or_empty_string($key)) || (wpckan_is_null_or_empty_string($value))) {
              continue;
          }

          $imploded_value = implode(', ', $value);

          if (wpckan_is_valid_url($imploded_value)) {
              array_push($resources, array('name' => wpckan_strip_qtranslate_tags($post->post_title), 'description' => $key, 'url' => $imploded_value, 'format' => wpckan_get_url_extension_or_html($imploded_value)));
          } else {
              array_push($extras, array('key' => $key, 'value' => $imploded_value));
          }
      }

      $settings = wpckan_get_ckan_settings();
      $ckanClient = wpckan_get_guzzle_client($settings);
      $data = array('name' => $post->post_name,
                  'title' => wpckan_strip_qtranslate_tags($post->post_title),
                  'notes' => wpckan_cleanup_text_for_archiving($post->post_content),
                  'extras' => $extras, );

      $archive_orga = get_post_meta($post->ID, 'wpckan_archive_post_orga', true);
      $archive_group = get_post_meta($post->ID, 'wpckan_archive_post_group', true);

      if ($archive_orga && $archive_orga != -1) {
          $data['owner_org'] = $archive_orga;
      }
      if ($archive_group && $archive_group !== '-1') {
          $data['groups'] = array(array('id' => $archive_group));
      }

      $result = wpckan_api_package_search(wpckan_get_ckan_domain(), array(
        'ids' => array($post->post_name),
      ));
      if ((int) $result['count'] == 0) {
          $commandName = 'PackageCreate';
      } else {
          $commandName = 'PackageUpdate';
          $data['id'] = $post->post_name;
      }

      try {
          wpckan_log(json_encode($data));

          $settings = wpckan_get_ckan_settings();
          $ckanClient = wpckan_get_guzzle_client($settings);
          $arguments = array('data' => json_encode($data));
          $command = $ckanClient->getCommand($commandName, $arguments);
          $response = $command->execute();

          wpckan_log('wpckan_api_archive_post_as_dataset commandName: '.$commandName.' arguments: '.print_r($arguments, true).' settings: '.print_r($settings, true));

          if ($response['success'] == false) {
              wpckan_api_call_error('wpckan_api_archive_post_as_dataset', null);
          }
      } catch (Exception $e) {
          wpckan_api_call_error('wpckan_api_archive_post_as_dataset', $e->getMessage());
      }

      if ($response['success'] == false) {
          wpckan_api_call_error('wpckan_api_archive_post_as_dataset', null);
      }

      foreach ($resources as $resource) {
          $resource['package_id'] = $response['result']['id'];
          wpckan_api_create_resource($resource);
      }

      wpckan_log('wpckan_api_archive_post_as_dataset result: '. print_r($response['result'], true));

      return $response['result'];
  }

  function wpckan_api_create_resource($data)
  {
      wpckan_log('wpckan_api_create_resource');

      try {
          $settings = wpckan_get_ckan_settings();
          $ckanClient = wpckan_get_guzzle_client($settings);
          $commandName = 'ResourceCreate';
          $arguments = array('data' => json_encode($data));
          $command = $ckanClient->getCommand($commandName, $arguments);
          $response = $command->execute();

          wpckan_log('wpckan_api_create_resource commandName: '.$commandName.' arguments: '.print_r($arguments, true).' settings: '.print_r($settings, true));
      } catch (Exception $e) {
          wpckan_api_call_error('wpckan_api_create_resource', $e->getMessage());
      }

      if ($response['success'] == false) {
          wpckan_api_call_error('wpckan_do_get_organizations', null);
      }

      wpckan_log('wpckan_api_create_resource result: '. print_r($response['result'], true));

      return $response['result'];
  }

  // function wpckan_api_get_organization_list_for_user()
  // {
  //     wpckan_log('wpckan_api_get_organization_list_for_user');
	//
  //     try {
  //         $settings = wpckan_get_ckan_settings();
  //         $ckanClient = wpckan_get_guzzle_client($settings);
  //         $commandName = 'GetOrganizationsUserIsMemberOf';
  //         $arguments = array('permission' => 'create_dataset');
  //         $command = $ckanClient->getCommand($commandName, $arguments);
  //         $response = $command->execute();
	//
  //         wpckan_log('wpckan_api_get_organization_list_for_user commandName: '.$commandName.' arguments: '.print_r($arguments, true).' settings: '.print_r($settings, true));
	//
  //     } catch (Exception $e) {
  //         wpckan_api_call_error('wpckan_api_get_organization_list_for_user', $e->getMessage());
  //     }
	//
  //     wpckan_log('wpckan_api_get_organization_list_for_user result: '. print_r($response['result'], true));
	//
  //     return $response['result'];
  // }

	function wpckan_get_organization_list($ckan_domain)
  {

    $ckanapi_url = $ckan_domain.'/api/3/action/organization_list?all_fields=true';
    $json = wpckan_get_or_cache($ckanapi_url, $query);

    if ($json === false) {
        return [];
    }
    $datasets = json_decode($json, true) ?: [];

		if (!isset($datasets['result'])):
			return [];
		endif;

    return $datasets['result'];
  }

  function wpckan_api_get_organization($id)
  {
      wpckan_log('wpckan_api_get_organization');

      try {
          $settings = wpckan_get_ckan_settings();
          $ckanClient = wpckan_get_guzzle_client($settings);
          $commandName = 'GetGroup';
          $arguments = array('id' => $id, 'include_datasets' => false);
          $command = $ckanClient->getCommand($commandName, $arguments);
          $response = $command->execute();

          wpckan_log('wpckan_api_get_organization commandName: '.$commandName.' arguments: '.print_r($arguments, true).' settings: '.print_r($settings, true));
      } catch (Exception $e) {
          wpckan_api_call_error('wpckan_api_get_organization', $e->getMessage());
      }

      wpckan_log('wpckan_api_get_organization result: '. print_r($response['result'], true));

      return $response['result'];
  }

  function wpckan_api_get_group_list_for_user()
  {
      wpckan_log('wpckan_api_get_group_list_for_user');

      try {
          $settings = wpckan_get_ckan_settings();
          $ckanClient = wpckan_get_guzzle_client($settings);
          $commandName = 'GetGroupsUserCanEdit';
          $arguments = array();
          $command = $ckanClient->getCommand($commandName, $arguments);
          $response = $command->execute();

          wpckan_log('wpckan_api_get_group_list_for_user commandName: '.$commandName.' arguments: '.print_r($arguments, true).' settings: '.print_r($settings, true));
      } catch (Exception $e) {
          wpckan_api_call_error('wpckan_api_get_group_list_for_user', $e->getMessage());
      }

      wpckan_log('wpckan_api_get_group_list_for_user result: '. print_r($response['result'], true));

      return $response['result'];
  }

  function wpckan_api_user_show($id)
  {
      wpckan_log('wpckan_api_user_show');

      try {
          $settings = wpckan_get_ckan_settings();
          $ckanClient = wpckan_get_guzzle_client($settings);
          $commandName = 'GetUser';
          $arguments = array('id' => $id);
          $command = $ckanClient->getCommand($commandName, $arguments);
          $response = $command->execute();

          wpckan_log('wpckan_api_user_show commandName: '.$commandName.' arguments: '.print_r($arguments, true).' settings: '.print_r($settings, true));
      } catch (Exception $e) {
          wpckan_api_call_error('wpckan_api_user_show', $e->getMessage());
      }

      wpckan_log('wpckan_api_user_show result: '. print_r($response['result'], true));

      return $response['result'];
  }

  function wpckan_api_ping()
  {
      wpckan_log('wpckan_api_ping');
      try {
          $settings = wpckan_get_ckan_settings();
          $ckanClient = CkanClient::factory($settings);
          $commandName = 'SiteRead';
          $arguments = array();
          $command = $ckanClient->getCommand($commandName, $arguments);
          $response = $command->execute();

          wpckan_log('wpckan_do_ping commandName: '.$commandName.' arguments: '.print_r($arguments, true).' settings: '.print_r($settings, true));
      } catch (Exception $e) {
          wpckan_api_call_error('wpckan_api_ping', $e->getMessage());
      }

      return true;
  }

  function wpckan_api_status_show()
  {
      wpckan_log('wpckan_api_status_show');

      try {
          $settings = wpckan_get_ckan_settings();
          $ckanClient = CkanClient::factory($settings);
          $commandName = 'StatusShow';
          $arguments = array();
          $command = $ckanClient->getCommand($commandName, $arguments);
          $response = $command->execute();

          wpckan_log('wpckan_api_status_show commandName: '.$commandName.' arguments: '.print_r($arguments, true).' settings: '.print_r($settings, true));
      } catch (Exception $e) {
          wpckan_api_call_error('wpckan_api_status_show', $e->getMessage());
      }

      wpckan_log('wpckan_api_status_show result: '. print_r($response['result'], true));

      return $response['result'];
  }

  function wpckan_get_ckan_domain()
  {
      return $GLOBALS['wpckan_options']->get_option('wpckan_setting_ckan_url');
  }


   function wpckan_get_metadata_info_of_dataset_by_id($params_arr)
   {
     $ckan_domain = isset($params_arr["ckan_domain"]) ? $params_arr["ckan_domain"] : wpckan_get_ckan_domain();
     $ckan_dataset_id = isset($params_arr["ckan_dataset_id"]) ? $params_arr["ckan_dataset_id"] : null;
     $get_layer_by_id = isset($params_arr["get_layer_post"]) ? $params_arr["get_layer_post"] : null;
     $get_download_url = isset($params_arr["download_url"]) ? $params_arr["download_url"] : null;
     $get_profilepage_url = isset($params_arr["profile_url"]) ? $params_arr["profile_url"] : null;
     $showing_fields = isset($params_arr["showing_fields"]) ? $params_arr["showing_fields"] : null;
     $echo = isset($params_arr["echo"]) ? $params_arr["echo"] : true;
     $lang = wpckan_get_current_language();
     $get_info = null;
     $attribute_metadata = array(
        //  "title_translated" => "Title",
        "notes_translated" => "Description",
        "odm_source" => "Source(s)",
        "odm_completeness" => "Completeness",
        "odm_metadata_reference_information" => "Metadata Reference Information",
        "odm_process" => "Process(es)",
        "odm_attributes" => "Attributes",
        "odm_logical_consistency" => "Logical Consistency",
        "odm_copyright" => "Copyright",
        "version" => "Version",
        "odm_date_created" => "Date created",
        "odm_date_uploaded" => "Date uploaded",
        "odm_temporal_range" => "Temporal range",
        "odm_accuracy-en" => "Accuracy",
        "odm_logical_consistency" => "Logical Consistency",
        "odm_contact" => "Contact",
        "odm_access_and_use_constraints" => "Access and use constraints",
        "license_id" => "License"
      );

     // get ckan record by id
     $get_info_from_ckan = wpckan_api_package_show($ckan_domain, $ckan_dataset_id);
     if(!empty($get_info_from_ckan)){
       $get_info .= '<div class="layer-toggle-info toggle-info toggle-info-' . $get_layer_by_id->ID .'">';
          $get_info .= '<h4 class="'. odm_country_manager()->get_current_country() . '-bgcolor">';
            $get_info .= $get_info_from_ckan['title_translated'][$lang]!=""?  $get_info_from_ckan['title_translated'][$lang] : $get_layer_by_id->post_title;
          $get_info .= '</h4>';
              if(!empty($showing_fields)){
                if($get_info_from_ckan):
                  foreach ($get_info_from_ckan as $key => $info) :
                    if(array_key_exists($key, $showing_fields)):
                        if(($key == 'notes_translated') && !empty($get_info_from_ckan['notes_translated'])):
                              $info = is_array($info) ? $info[$lang]: $info;
                              $get_info .= '<p>' . $info . '</p>';
                        elseif($key == 'odm_date_created' && !empty($get_info_from_ckan['odm_date_created'])):
                              $get_info .= '<p><strong>' . $attribute_metadata['odm_date_created'] .':</strong> ';
                              $get_info .= ($info == "unspecified")? ucwords($get_info_from_ckan['odm_date_created'] ) : $get_info_from_ckan['odm_date_created']. '</p>';
                        elseif($key == 'license_id' && $get_info_from_ckan['license_id']!=""):
                              $get_info .= '<p><strong>' .$attribute_metadata['license_id'] .':</strong> ';
                              $get_info .= ($info == "unspecified")? ucwords($get_info_from_ckan['license_id'] ) : $get_info_from_ckan['license_id']. '</p>';
                        elseif($key == 'version' && $get_info_from_ckan['version']!=""):
                              $get_info .= '<p><strong>' .$attribute_metadata['version'] .':</strong> ';
                              $get_info .= ($info == "unspecified")? ucwords($get_info_from_ckan['version'] ) : $get_info_from_ckan['version']. '</p>';
                        else:
                              $get_info .= '<p><strong>'. $attribute_metadata[$key].'</strong><br/>';
                              $get_info .= is_array($info) ? $info[$lang]: $info. '</p>';
                        endif;
                     endif;
                  endforeach;
                endif;
              }else {
                foreach ($showing_fields as $key => $info):
                  if(!empty($get_info_from_ckan)):
                      if($key == 'notes_translated' && !empty($get_info_from_ckan['notes_translated'])):
                            $get_info .= '<p>' . is_array($info) ? $info[$lang]: $info . '</p>';
                      elseif($key == 'odm_date_created' && !empty($get_info_from_ckan['odm_date_created'])):
                            $get_info .= '<p>' . $attribute_metadata['odm_date_created'] .': ';
                            $get_info .= ($info == "unspecified")? ucwords($get_info_from_ckan['odm_date_created'] ) : $get_info_from_ckan['odm_date_created']. '</p>';
                      elseif($key == 'license_id' && $get_info_from_ckan['license_id']!=""):
                            $get_info .= '<p>' .$attribute_metadata['license_id'] .': ';
                            $get_info .= ($info == "unspecified")? ucwords($get_info_from_ckan['license_id'] ) : $get_info_from_ckan['license_id']. '</p>';
                      elseif($key == 'version' && $get_info_from_ckan['version']!=""):
                            $get_info .= '<p>' .$attribute_metadata['version'] .': ';
                            $get_info .= ($info == "unspecified")? ucwords($get_info_from_ckan['version'] ) : $get_info_from_ckan['version']. '</p>';
                      else:
                          if(array_key_exists($key, $attribute_metadata)):
                              $get_info .= '<p><strong>'. $attribute_metadata[$key].'</strong><br/>';
                              $get_info .= is_array($info) ? $info[$lang]: $info. '</p>';
                          endif;
                      endif;
                  endif;
                endforeach;
              }

              if ($get_layer_by_id) {
                  if ( ($lang != "en") ){
                     $get_download_url = get_post_meta($get_layer_by_id->ID, '_layer_download_link_localization', true);
                     $get_profilepage_url = get_post_meta($get_layer_by_id->ID, '_layer_profilepage_link_localization', true);
                  }else {
                     $get_download_url = get_post_meta($get_layer_by_id->ID, '_layer_download_link', true);
                     $get_profilepage_url = get_post_meta($get_layer_by_id->ID, '_layer_profilepage_link', true);
                  }
                  $get_info .= '<div class="atlernative_links">';
                  if ($get_download_url != ''){
              				$ckan_dataset_id = wpckan_get_dataset_id_from_dataset_url($get_download_url);
              				$layer_download_link = get_site_url()."/dataset/?id=".$ckan_dataset_id;

                      $get_info .= '<div class="div-button"><a href="'. $layer_download_link.'" target="_blank"><i class="fa fa-arrow-down"></i> '. __("Download data", "opendev"). '</a></div>';

                   }
                   if ($get_profilepage_url != ''){
                        $get_info .= '<div class="div-button"><a href="'. $get_profilepage_url. '" target="_blank"><i class="fa fa-table"></i> '. __("View dataset table", "opendev"). '</a></div>';
                   }
                  $get_info .= '</div>';
              }
        $get_info .= '</div>';
      }//if ckan_dataset_id
      if($echo){
        echo $get_info;
      }else{
        return $get_info;
      }
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
