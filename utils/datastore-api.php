<?php

 function wpckan_get_datastore_resources_filter($ckan_domain, $resource_id, $key, $value)
 {
     $datastore_url = $ckan_domain.'/api/3/action/datastore_search?resource_id='.$resource_id.'&limit=1000&filters={"'.$key.'":"'.$value.'"}';
     $json = wpckan_get_or_cache($datastore_url,$resource_id);
     if ($json === false) {
         return [];
     }
     $profiles = json_decode($json, true) ?: [];

     return $profiles['result']['records'];
 }

 function wpckan_get_datastore_resource($ckan_domain, $resource_id)
 {
     $datastore_url = $ckan_domain.'/api/3/action/datastore_search?resource_id='.$resource_id.'&limit=1000';
     $json = wpckan_get_or_cache($datastore_url,$resource_id);
     if ($json === false) {
         return [];
     }
     $profiles = json_decode($json, true) ?: [];

     return $profiles['result']['records'];
 }

?>
