<?php

function wpckan_get_dataset_by_id($ckan_domain, $id)
{
    $ckanapi_url = $ckan_domain.'/api/3/action/package_show?id='.$id;
    $json = @file_get_contents($ckanapi_url);
    if ($json === false) {
        return [];
    }
    $datasets = json_decode($json, true) ?: [];

    return $datasets['result'];
}

function wpckan_get_datasets_filter($ckan_domain, $key, $value)
{
    $ckanapi_url = $ckan_domain.'/api/3/action/package_search?fq='.$key.':'.$value;
    $json = @file_get_contents($ckanapi_url);
    if ($json === false) {
        return [];
    }
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

 function wpckan_get_datastore_resources_filter($ckan_domain, $resource_id, $key, $value)
 {
     $datastore_url = $ckan_domain.'/api/3/action/datastore_search?resource_id='.$resource_id.'&limit=1000&filters={"'.$key.'":"'.$value.'"}';
     $json = @file_get_contents($datastore_url);
     if ($json === false) {
         return [];
     }
     $profiles = json_decode($json, true) ?: [];

     return $profiles['result']['records'];
 }

 function wpckan_get_datastore_resource($ckan_domain, $resource_id)
 {
     $datastore_url = $ckan_domain.'/api/3/action/datastore_search?resource_id='.$resource_id.'&limit=1000';
     $json = @file_get_contents($datastore_url);
     if ($json === false) {
         return [];
     }
     $profiles = json_decode($json, true) ?: [];

     return $profiles['result']['records'];
 }

?>
