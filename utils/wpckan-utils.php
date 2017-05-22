<?php

  define("WPCKAN_FREQ_NEVER","0");
  define("WPCKAN_FREQ_POST_SAVED","1");
  define("WPCKAN_FILTER_ALL","0");
  define("WPCKAN_FILTER_ONLY_WITH_RESOURCES","1");
  define("WPCKAN_DEFAULT_LOG_PATH","/tmp/wpckan.log");
  define("WPCKAN_DEFAULT_CACHE_PATH","/cache/");
  define("WPCKAN_DEFAULT_CACHE_TIME",3600);

  use Analog\Analog;
  use Analog\Handler;

  function wpckan_edit_post_logic_dataset_metabox($post_ID){
    wpckan_log("wpckan_edit_post_logic_datasets_metabox: " . $post_ID);

    if ( ! isset( $_POST['wpckan_add_related_datasets_nonce'] ) )
    return $post_ID;

    $nonce = $_POST['wpckan_add_related_datasets_nonce'];

    if ( ! wp_verify_nonce( $nonce, 'wpckan_add_related_datasets' ) )
    return $post_ID;

    $datasets_json = $_POST['wpckan_add_related_datasets_datasets'];

    // Update the meta field.
    update_post_meta( $post_ID, 'wpckan_related_datasets', $datasets_json );

  }

  function wpckan_edit_post_logic_archive_post_metabox($post_ID){
    wpckan_log("wpckan_edit_post_logic_archive_post_metabox: " . $post_ID);

    if ( ! isset( $_POST['wpckan_archive_post_nonce'] ) )
    return $post_ID;

    $nonce = $_POST['wpckan_archive_post_nonce'];

    if ( ! wp_verify_nonce( $nonce, 'wpckan_archive_post' ) )
    return $post_ID;

    $archive_orga = $_POST['wpckan_archive_post_orga'];
    $archive_group = $_POST['wpckan_archive_post_group'];
    $archive_freq = $_POST['wpckan_archive_post_freq'];

    update_post_meta( $post_ID, 'wpckan_archive_post_orga', $archive_orga );
    update_post_meta( $post_ID, 'wpckan_archive_post_group', $archive_group );
    update_post_meta( $post_ID, 'wpckan_archive_post_freq', $archive_freq );

    if (wpckan_post_should_be_archived_on_save( $post_ID )){
      $post = get_post($post_ID);
      try{
        wpckan_api_archive_post_as_dataset($post);
      }catch(Exception $e){
        wpckan_log($e->getMessage());
      }
    }

  }

  /*
  * Shortcodes
  */

  function wpckan_show_related_datasets($atts) {
    wpckan_log("wpckan_show_related_datasets " . print_r($atts,true));

    $related_datasets_json = get_post_meta( $atts['post_id'], 'wpckan_related_datasets', true );
    $related_datasets = array();
    if (!wpckan_is_null_or_empty_string($related_datasets_json)):
      $related_datasets = json_decode($related_datasets_json,true);
    endif;

    $blank_on_empty = false;
    if (array_key_exists("blank_on_empty",$atts)){
      $blank_on_empty = filter_var( $atts['blank_on_empty'], FILTER_VALIDATE_BOOLEAN );
    }

    // Add ids attribute to constraint search
    $atts['ids'] = array_map(function($item){
      return $item['dataset_id'];
    }, $related_datasets);

    $atts['ids'] = array_filter($atts['ids'], "wpckan_valid_id");

    if (empty($atts['ids'])):
      return "";
    endif;

    $result = wpckan_api_package_search(wpckan_get_ckan_domain(),$atts);
    $dataset_array = $result["results"];

    if ((count($dataset_array) == 0) && $blank_on_empty):
      return "";
    endif;

    return wpckan_output_template( plugin_dir_path( __FILE__ ) . '../templates/dataset-list.php',$dataset_array,$atts);
  }

  function wpckan_show_number_of_related_datasets($atts) {
    wpckan_log("wpckan_show_number_of_related_datasets " . print_r($atts,true));

    $related_datasets_json = get_post_meta( $atts['post_id'], 'wpckan_related_datasets', true );
    $related_datasets = array();
    if (!wpckan_is_null_or_empty_string($related_datasets_json)):
      $related_datasets = json_decode($related_datasets_json,true);
    endif;

    $blank_on_empty = false;
    if (array_key_exists("blank_on_empty",$atts)){
      $blank_on_empty = filter_var( $atts['blank_on_empty'], FILTER_VALIDATE_BOOLEAN );
    }

    // Add ids attribute to constraint search
    $atts['ids'] = array_map(function($item){
      return $item['dataset_id'];
    }, $related_datasets);

    $atts['ids'] = array_filter($atts['ids'], "wpckan_valid_id");

    $result = wpckan_api_package_search(wpckan_get_ckan_domain(),$atts);
    $dataset_array = $result["results"];
    $atts["count"] = $result["count"];

    return wpckan_output_template( plugin_dir_path( __FILE__ ) . '../templates/dataset-number.php',$dataset_array,$atts);
  }

  function wpckan_show_query_datasets($atts) {
    wpckan_log("wpckan_show_query_datasets "  . print_r($atts,true));

    $dataset_array = array();
    try{
      $result = wpckan_api_package_search(wpckan_get_ckan_domain(),$atts);
      $dataset_array = $result["results"];
      $atts["count"] = $result["count"];
    }catch(Exception $e){
      wpckan_log($e->getMessage());
    }

    $blank_on_empty = false;
    if (array_key_exists("blank_on_empty",$atts)){
      $blank_on_empty = filter_var( $atts['blank_on_empty'], FILTER_VALIDATE_BOOLEAN );
    }

    if ((count($dataset_array) == 0) && $blank_on_empty)
      return "";

    return wpckan_output_template( plugin_dir_path( __FILE__ ) . '../templates/dataset-list.php',$dataset_array,$atts);
  }

  function wpckan_show_number_of_query_datasets($atts) {
    wpckan_log("wpckan_show_number_of_query_datasets "  . print_r($atts,true));

    $dataset_array = array();
    try{
      $result = wpckan_api_package_search(wpckan_get_ckan_domain(),$atts);
      $dataset_array = $result["results"];
      $atts["count"] = $result["count"];
    }catch(Exception $e){
      wpckan_log($e->getMessage());
    }

    return wpckan_output_template( plugin_dir_path( __FILE__ ) . '../templates/dataset-number.php',$dataset_array,$atts);
  }

  function wpckan_show_dataset_detail($atts) {

    wpckan_log("wpckan_show_dataset_detail "  . print_r($atts,true));

    $dataset;
    try{
      $dataset = wpckan_api_package_show(wpckan_get_ckan_domain(),$atts['id']);
    }catch(Exception $e){
      wpckan_log($e->getMessage());
    }

    if (!(isset($dataset))):
      return "";
    endif;

    return wpckan_output_template( plugin_dir_path( __FILE__ ) . '../templates/dataset-detail.php',$dataset,$atts);

  }

  /*
  * Templates
  */

  function wpckan_output_template($template_url,$data,$atts){
    ob_start();
    require $template_url;
    $output = ob_get_contents();
    ob_end_clean();
    return $output;
  }

  function wpckan_get_link_to_dataset($dataset_name){
    wpckan_log("wpckan_get_link_to_dataset "  . print_r($dataset_name,true));

    if ($GLOBALS['wpckan_options']->get_option('wpckan_setting_redirect_enabled')):
      return "/dataset/?id=" . $dataset_name;
    endif;

    return $GLOBALS['wpckan_options']->get_option('wpckan_setting_ckan_url') . "/dataset/" . $dataset_name;
  }

  function wpckan_get_dataset_id_from_dataset_url($dataset_url){
    $parsed_url = parse_url($dataset_url, PHP_URL_PATH);
    $exploded_by = array("dataset", "library_record", "laws_record");
    foreach($exploded_by as $exploded_val){
      $explode_dataset_url = explode("/".$exploded_val."/", $parsed_url);
      if(isset($explode_dataset_url[1])){
        $ckan_dataset_id = $explode_dataset_url[1];
    		return $ckan_dataset_id;
      }
    }
    return;
  }

  function wpckan_get_link_to_resource($dataset_name,$resource_id){
    wpckan_log("wpckan_get_link_to_resource "  . print_r($dataset_name,true) . " " . print_r($resource_id,true));

    return wpckan_get_link_to_dataset($dataset_name) . "/resource/" . $resource_id;
  }

  /*
  * Logging
  */

  function wpckan_log($text) {

    if (!$GLOBALS['wpckan_options']->get_option('wpckan_setting_log_enabled')):
      return;
    endif;

    $log_file_path = !wpckan_is_null_or_empty_string($GLOBALS['wpckan_options']->get_option('wpckan_setting_log_path')) ? $GLOBALS['wpckan_options']->get_option('wpckan_setting_log_path') : WPCKAN_DEFAULT_LOG_PATH;
    if (!file_exists($log_file_path) || !is_file ($log_file_path)):
      return;
    endif;

    $bt = debug_backtrace();
    $caller = array_shift($bt);

    Analog::handler(Handler\File::init($log_file_path));

    Analog::log ( "[ " . $caller['file'] . " | " . $caller['line'] . " ] " . $text );
  }

  /*
  * Utilities
  */

  function compose_solr_query_from_attrs($attrs){

    $arguments = '';

    if (!isset($attrs["limit"])):
      $attrs["limit"] = "1000";
    endif;

    // query
    if (isset($attrs['query'])):
      $arguments .= 'q="'. urlencode($attrs['query']) . '"';
    endif;

    $fq = "";

    // Ids
    if (isset($attrs['ids']) && !empty($attrs['ids'])):
      $joined_ids =  $attrs['ids'];
      if (is_array($attrs['ids'])):
        $joined_ids = implode(" OR ", $attrs['ids']);
      endif;
      $fq = $fq . '+id:(' . $joined_ids . ')';
    endif;

    // group
    if (isset($attrs['group'])):
      $fq = $fq . '+groups:' . $attrs['group'];
    endif;

    // organization
    if (isset($attrs['organization'])):
      $fq = $fq . '+owner_org:' . $attrs['organization'];
    endif;

    // type
    if (isset($attrs['type'])):
      $fq = $fq . '+type:' . $attrs['type'];
    endif;

    // filter_fields
    if (isset($attrs['filter_fields'])):
      $filter_fields_json = json_decode($attrs['filter_fields'],true);
      if (isset($filter_fields_json)):
        foreach ($filter_fields_json as $field => $value):

          if ($field == "extras_taxonomy"):
            $taxonomy_top_tier = odm_taxonomy_manager()->get_taxonomy_top_tier();
            if (array_key_exists($value,$taxonomy_top_tier)):
              $value = "(\"" . implode("\" OR \"", $taxonomy_top_tier[$value]) . "\")";
              $value = urlencode($value);
            endif;
          endif;
          $fq = $fq . '+' . $field . ':' . $value;
        endforeach;
      endif;
    endif;

    // filter
    if (isset($attrs['filter'])):
      if ((int)$attrs['filter'] == 1):
        $fq = $fq . '+num_resources:[1 TO *]';
      endif;
    endif;

    if (!empty($fq)):
      $arguments .= '&fq='.$fq;
    endif;

    // limit
    if (isset($attrs['limit'])):
      $limit = $attrs['limit'];
      $arguments .= '&rows='.$limit;
      if (isset($attrs['page'])):
        $page = (int)$attrs['page'];
        if ($page > 0):
          $arguments .= '&start='. (string)($limit * ($page - 1));
        endif;
      endif;
    endif;

		// sort
		$sort = isset($attrs['sort']) ? $attrs['sort'] : 'metadata_modified+desc';
    $arguments .= '&sort=' . $sort;

    return $arguments;
  }

  function wpckan_do_curl($url)
  {
    if(function_exists("curl_init")){
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
      curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
      $content = curl_exec($ch);
      curl_close($ch);
      return $content;
    } else {
      return @file_get_contents($url);
    }
  }

  function wpckan_is_supported_post_type($post_type){
   $settings_name =  "setting_supported_post_types_" . $post_type;
   return $GLOBALS['wpckan_options']->get_option($settings_name);
  }

  function wpckan_dataset_has_resources($dataset){
    if (array_key_exists("dataset_num_resources",$dataset)){
     return ($dataset["dataset_num_resources"] >= 1);
    }

    if (array_key_exists("num_resources",$dataset)){
     return ($dataset["num_resources"] >= 1);
    }
    return false;
  }

  function wpckan_dataset_has_matching_extras($dataset,$filter_fields_json){
    wpckan_log("wpckan_dataset_has_matching_extras " . print_r($dataset,true) . print_r($filter_fields_json,true));

    if (array_key_exists("dataset_extras",$dataset)){
     $extras = json_decode($dataset["dataset_extras"], true);
    }else if (array_key_exists("extras",$dataset)){
     $extras = $dataset["extras"];
    }else{
     return false;
    }

    foreach ($extras as $extra){
     $field_value = $filter_fields_json[$extra['key']];
     if (!wpckan_is_null_or_empty_string($field_value) && strpos(strtolower($extra['value']),strtolower($field_value)) !== false){
      return true;
     }

    }

   return false;
  }

  function wpckan_cleanup_text_for_archiving($post_content){
    $post_content = wpckan_detect_and_remove_shortcodes_in_text($post_content);
    $post_content = wpckan_strip_qtranslate_tags($post_content);
    return $post_content;
  }

  function wpckan_detect_and_remove_shortcodes_in_text($text)
  {
    global $post;
    $pattern = get_shortcode_regex();
    $shortcodes = array("wpckan_related_datasets","wpckan_number_of_related_datasets","wpckan_query_datasets");

    foreach($shortcodes as $shortcode){
      if ( preg_match_all( '/'. $pattern .'/s', $post->post_content, $matches )
      && array_key_exists( 2, $matches )
      && in_array( $shortcode, $matches[2] ) )
      {
        foreach($matches as $match){
          $text = str_replace($match,"",$text);
        }
      }
    }
    return $text;
  }

  function wpckan_detect_and_echo_shortcodes_in_text($text)
  {
    global $post;
    $pattern = get_shortcode_regex();
    $shortcodes = array("wpckan_related_datasets","wpckan_number_of_related_datasets","wpckan_query_datasets");

    foreach($shortcodes as $shortcode){
      if (   preg_match_all( '/'. $pattern .'/s', $post->post_content, $matches )
      && array_key_exists( 2, $matches )
      && in_array( $shortcode, $matches[2] ) )
      {
        foreach($matches as $match){
          $text = str_replace($match,do_shortcode($match),$text);
        }
      }
    }
    return $text;
  }

  function wpckan_get_group_names_for_user(){
    $groups = array();
    $group_names = array();
    try{
      $groups = wpckan_api_get_group_list_for_user();
    }catch(Exception $e){
      wpckan_log($e->getMessage());
    }
    foreach ($groups as $group){
      array_push($group_names,$group["display_name"]);
    }
    return $group_names;
  }

  function wpckan_get_organization_names_for_user(){
    $organizations = array();
    $organization_names = array();
    try{
      $organizations = wpckan_api_get_organization_list_for_user();
    }catch(Exception $e){
      wpckan_log($e->getMessage());
    }
    foreach ($organizations as $organization){
      array_push($organization_names,$organization["display_name"]);
    }
    return $organization_names;
  }

  function wpckan_validate_settings_log(){

    $log_enabled = $GLOBALS['wpckan_options']->get_option('wpckan_setting_log_enabled');
    if ($log_enabled):
      $log_file_path = !wpckan_is_null_or_empty_string($GLOBALS['wpckan_options']->get_option('wpckan_setting_log_path')) ? $GLOBALS['wpckan_options']->get_option('wpckan_setting_log_path') : WPCKAN_DEFAULT_LOG_PATH;
      if (!file_exists($log_file_path) || !is_file($log_file_path)):
        return false;
      endif;
    endif;

    return true;
  }


  function wpckan_validate_settings_read(){
    try{
      wpckan_api_ping();
    }catch(Exception $e){
      wpckan_log($e->getMessage());
      return false;
    }

    return true;
  }

  function wpckan_validate_settings_write(){
    return !wpckan_is_null_or_empty_string($GLOBALS['wpckan_options']->get_option('wpckan_setting_ckan_api'));
  }

  function wpckan_get_ckan_settings(){

    $settings = array(
      'baseUrl' => $GLOBALS['wpckan_options']->get_option('wpckan_setting_ckan_url') . "/api/",
      'scheme' => 'http',
      'apiKey' => $GLOBALS['wpckan_options']->get_option('wpckan_setting_ckan_api')
    );

    return $settings;
  }

  function wpckan_post_should_be_archived_on_save($post_ID){
    $archive_freq = get_post_meta( $post_ID, 'wpckan_archive_post_freq', true);
    wpckan_log("wpckan_post_should_be_archived_on_save freq: " . $archive_freq);
    return ( $archive_freq == WPCKAN_FREQ_POST_SAVED);
  }

  function wpckan_sanitize_url($input) {
    $clean_url = esc_url($input);
    if(substr($clean_url, -1) == '/') {
      $clean_url = substr($clean_url, 0, -1);
    }
    return $clean_url;
  }

  function wpckan_remove_whitespaces($input) {
    $clean_input =  str_replace(" ", "", $input);
    return $clean_input;
  }

  function wpckan_strip_qtranslate_tags($input) {
    $clean_url = str_replace("<!--:-->", " ", $input);
    $clean_url = strip_tags($clean_url);
    return $clean_url;
  }

  function wpckan_pagination_last($count,$limit,$page) {
    wpckan_log("wpckan_pagination_last");
    return (($count >= ($limit * ($page -1 ))) && ($count <= ($limit * $page)));
  }

  function wpckan_pagination_first($page) {
    wpckan_log("wpckan_pagination_first");
    return ($page == 1);
  }

  function wpckan_is_null_or_empty_string($question){
    return (!isset($question) || empty($question));
  }

  function wpckan_is_null($question){
    return !isset($question);
  }

  function wpckan_is_valid_url($url){
    if (filter_var($url, FILTER_VALIDATE_URL) != false){
     return true;
    }
    return false;
  }

  function wpckan_get_url_extension($url){
    $path = parse_url($url, PHP_URL_PATH);
    return pathinfo($path, PATHINFO_EXTENSION);
  }

  function wpckan_get_url_extension_or_html($url){
    $ext = wpckan_get_url_extension($url);
    if ($ext)
     return $ext;
    return 'html';
  }

  function wpckan_is_qtranslate_available(){
    return function_exists('qtranxf_getLanguage');
  }

  function wpckan_get_current_language(){
    $current_language = 'en';
    if (wpckan_is_qtranslate_available() && function_exists("qtranxf_getLanguage")):
      $current_language = qtranxf_getLanguage();
    endif;
    if (function_exists("odm_language_manager")):
      $current_language = odm_language_manager()->get_current_language();
    endif;
    return $current_language;
  }

  function wpckan_parse_field_mappings($option){
    $mappings_raw = $GLOBALS['wpckan_options']->get_option($option);
    $mappings_clean = array();
    if (empty($mappings_raw)):
      return $mappings_clean;
    endif;

    $mappings = explode("\r\n", $mappings_raw);
    foreach ($mappings as $value) {
        $array_value = explode('=>', trim($value));
        $mappings_clean[wpckan_remove_whitespaces($array_value[0])] = trim($array_value[1]);
    }
    return $mappings_clean;
  }

  function wpckan_valid_id($id){
    return isset($id) && !empty($id) && $id !== "" && $id !== " ";
  }

  function wpckan_get_license_list(){
    $path_to_license_file = wpckan_get_ckan_domain() . '/licenses.json';
		$json_file = wpckan_do_curl($path_to_license_file);
		return json_decode($json_file);
  }

  function wpckan_is_date($value){
    if (!$value):
      return false;
    endif;
    if (strpos($value,"-") === false && strpos($value,".") === false && strpos($value,"/") === false):
      return false;
    endif;
    try {
        new \DateTime($value);
        return true;
    } catch (\Exception $e) {
        return false;
    }
  }

  function wpckan_print_date($date_string){
    try {
        $date = new \DateTime($date_string);
        return $date->format("Y-m-d");
    } catch (\Exception $e) {
        return null;
    }
  }

?>
