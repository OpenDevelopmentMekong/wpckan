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

			$total_count = $datasets['result']['count'];
			$iteration = 1;
      if ($total_count - ($iteration * 1000) > 0):
				$attrs["limit"] = 1000;
				$attrs["page"] = $iteration;
				$query = '?'.compose_solr_query_from_attrs($attrs);
				$ckanapi_url = $ckan_domain.'/api/3/action/package_search'.$query;

	      $json = wpckan_get_or_cache($ckanapi_url, $query);
				if ($json !== false) {
					$datasets_iteration = json_decode($json, true) ?: [];
	        $datasets['result']['results'] = array_merge($datasets['result']['results'],$datasets_iteration);
					$iteration++;
				}
      endif;

      return $datasets['result'];
  }

  /*
  * Guzzle
  */

  function wpckan_get_guzzle_client($settings)
  {
      $ckanClient = CkanClient::factory($settings);
      $cache_plugin = new CachePlugin(array(
          'storage' => new DefaultCacheStorage(
              new DoctrineCacheAdapter(
                  new FilesystemCache('/cache/')
              )
          ),
      ));
      $ckanClient->addSubscriber($cache_plugin);

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

      return $response['result'];
  }

  function wpckan_api_get_organization_list_for_user()
  {
      wpckan_log('wpckan_api_get_organization_list_for_user');

      try {
          $settings = wpckan_get_ckan_settings();
          $ckanClient = wpckan_get_guzzle_client($settings);
          $commandName = 'GetOrganizationsUserIsMemberOf';
          $arguments = array('permission' => 'create_dataset');
          $command = $ckanClient->getCommand($commandName, $arguments);
          $response = $command->execute();

          wpckan_log('wpckan_api_get_organization_list_for_user commandName: '.$commandName.' arguments: '.print_r($arguments, true).' settings: '.print_r($settings, true));
      } catch (Exception $e) {
          wpckan_api_call_error('wpckan_api_get_organization_list_for_user', $e->getMessage());
      }

      return $response['result'];
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

      wpckan_log($response);

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

      return $response['result'];
  }

  function wpckan_get_ckan_domain()
  {
      return $GLOBALS['wpckan_options']->get_option('wpckan_setting_ckan_url');
  }

   // TODO: parametrize
   function wpckan_get_metadata_info_of_dataset_by_id($ckan_domain, $ckan_dataset_id, $individual_layer = '', $atlernative_links = 0, $showing_fields = '')
   {
     $lang = odm_language_manager()->get_current_language();
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
       ?>
       <div class="layer-toggle-info toggle-info toggle-info-<?php echo $individual_layer->ID; ?>">
          <table border="0" class="toggle-table data-table">
              <tr><td colspan="2"><h4><?php echo $get_info_from_ckan['title_translated'][$lang]!=""?  $get_info_from_ckan['title_translated'][$lang] : $individual_layer->post_title; ?></h4></td></tr>
              <?php
              if($showing_fields == ""){
                if($get_info_from_ckan){
                  foreach ($get_info_from_ckan as $key => $info) {
                    if(!empty($get_info_from_ckan)){
                        if($key == 'license_id' && $get_info_from_ckan['license_id']!=""){ ?>
                          <tr>
                              <td class="row-key"><?php echo $attribute_metadata['license_id']; ?></td>
                              <td><?php echo $info == "unspecified"? ucwords($get_info_from_ckan['license_id'] ) : $get_info_from_ckan['license_id']; ?></td>
                          </tr>
                        <?php
                        }else{
                            if(array_key_exists($key, $attribute_metadata)){ ?>
                              <tr>
                                  <td class="row-key"><?php echo $attribute_metadata[$key]; ?></td>
                                  <td><?php echo is_array($info) ? $info[$lang]: $info; ?></td>
                              </tr>
                            <?php
                            }
                        }//end else
                    } //!empty($get_info_from_ckan)
                  } //end foreach
                }//if get $get_info_from_ckan
              }else { //if show fields are defined
                foreach ($showing_fields as $key => $info) {
                  if(!empty($get_info_from_ckan)){
                      if($key == 'license_id' && $get_info_from_ckan['license_id']!=""){ ?>
                        <tr>
                            <td class="row-key"><?php echo $showing_fields['license_id']; ?></td>
                            <td><?php echo $info == "unspecified"? ucwords($get_info_from_ckan['license_id'] ) : $get_info_from_ckan['license_id']; ?></td>
                        </tr>
                      <?php
                      }else{  ?>
                          <tr>
                              <td class="row-key"><?php echo $showing_fields[$key]; ?></td>
                              <td><?php echo is_array($get_info_from_ckan[$key]) ? $get_info_from_ckan[$key][$lang]: $get_info_from_ckan[$key]; ?></td>
                          </tr>
                      <?php
                      }
                  } //!empty($get_info_from_ckan)
                } //end foreach
              }
              ?>
          </table>
          <?php
          if ($atlernative_links == 1) {
              $get_post_by_id = get_post($individual_layer->ID);
              if ( ($lang != "en") ){
                 $get_download_url = get_post_meta($get_post_by_id->ID, '_layer_download_link_localization', true);
                 $get_profilepage_url = get_post_meta($get_post_by_id->ID, '_layer_profilepage_link_localization', true);
              }else {
                 $get_download_url = get_post_meta($get_post_by_id->ID, '_layer_download_link', true);
                 $get_profilepage_url = get_post_meta($get_post_by_id->ID, '_layer_profilepage_link', true);
              }
              ?>
              <div class="atlernative_links">
              <?php if ($get_download_url != ''){
          				$ckan_dataset_id = wpckan_get_dataset_id_from_dataset_url($get_download_url);
          				$layer_download_link = get_site_url()."/dataset/?id=".$ckan_dataset_id;
              ?>
                      <div class="div-button"><a href="<?php echo $layer_download_link; ?>" target="_blank"><i class="fa fa-arrow-down"></i> <?php _e("Download data", "opendev"); ?></a></div>
                   <?php
                   }
                   if ($get_profilepage_url != ''){ ?>
                        <div class="div-button"><a href="<?php echo $get_profilepage_url; ?>" target="_blank"><i class="fa fa-table"></i> <?php _e("View dataset table", "opendev"); ?></a></div>
                   <?php
                   } ?>
              </div><!-- atlernative_links -->
            <?php
          } //if $atlernative_links ?>
        </div><!--layer-toggle-info-->
        <?php
      }//if ckan_dataset_id !=""
    }//end function

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
