<?php

function wpckan_get_or_cache($url,$id){

  if (!$id) {
    return  wpckan_do_curl($url);
  }
  
  $json = "{}";
  $hashed_id = "wpckan.".md5($id);

   wpckan_log("wpckan_get_or_cache url:" . $url . " id: " . $hashed_id);

   if (defined(WP_REDIS_CACHE_HOST)) {
     $json = $GLOBALS['cache']->fetch($hashed_id);
   }
   if ($json && $json != '{}') {
     $json = wpckan_do_curl($url);
   }
   if (!(strpos($json, '"success": false') !== false && !empty($hashed_id))) {
     $GLOBALS['cache']->save($hashed_id, $json, $GLOBALS['cache_time']);
   }

   return $json;
 }

 function wpckan_get_datastore_resources_filter($ckan_domain, $resource_id, $key, $value)
 {
     $datastore_url = $ckan_domain.'/api/3/action/datastore_search?resource_id='.$resource_id.'&limit=9999&filters={"'.$key.'":"'.$value.'"}';
     $json = wpckan_get_or_cache($datastore_url,$resource_id. $key . $value);

     if ($json === false) {
         return [];
     }

     $profiles = json_decode($json, true) ?: [];
     if ($profiles['success']==false){
       return [];
     }

     return $profiles['result']['records'];
 }

 function wpckan_get_datastore_resource($ckan_domain, $resource_id)
 {
     $datastore_url = $ckan_domain.'/api/3/action/datastore_search?resource_id='.$resource_id.'&limit=9999';
     $json = wpckan_get_or_cache($datastore_url,$resource_id);

     if ($json === false) {
         return [];
     }

     $profiles = json_decode($json, true) ?: [];
     if ($profiles['success']==false){
       return [];
     }

     return $profiles['result']['records'];
 }

?>
