<?php

 function wpckan_get_or_cache($url,$id){

   $json = "{}";

   wpckan_log("wpckan_get_or_cache url:" . $url . " id: " . $id);

   if (!$GLOBALS['wpckan_options']->get_option('wpckan_setting_cache_enabled')):
     $json = @file_get_contents($url);
   else:
     $valid_id = substr($id,0,249);
     $json = $GLOBALS['cache']->get_data($valid_id,$url);

     if (strpos($json, '"success": false') !== false) {
        $file_path = $GLOBALS['wpckan_options']->get_option('wpckan_setting_cache_path')  . $valid_id;
        wpckan_log("wpckan_get_or_cache deleting cached file:" . $file_path);
        if (file_exists($file_path)) {
          unlink($file_path);
        }
     }

   endif;

   return $json;
 }

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
