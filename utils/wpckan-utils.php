<?php

  define("WPCKAN_FREQ_NEVER","0");
  define("WPCKAN_FREQ_POST_SAVED","1");
  define("WPCKAN_FILTER_ALL","0");
  define("WPCKAN_FILTER_ONLY_WITH_RESOURCES","1");
  define("WPCKAN_DEFAULT_LOG","/tmp/wpckan.log");

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
    $result = wpckan_api_query_datasets($atts);
    $dataset_array = $result["results"];

    if ((count($dataset_array) == 0) && $blank_on_empty)
      return "";

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
    $result = wpckan_api_query_datasets($atts);
    $dataset_array = $result["results"];

    if ((count($dataset_array) == 0) && $blank_on_empty)
      return "";

    return wpckan_output_template( plugin_dir_path( __FILE__ ) . '../templates/dataset-number.php',$dataset_array,$atts);
  }

  function wpckan_show_query_datasets($atts) {
    wpckan_log("wpckan_show_query_datasets "  . print_r($atts,true));

    $dataset_array = array();
    try{
      $result = wpckan_api_query_datasets($atts);
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

  function wpckan_show_dataset_detail($atts) {

    wpckan_log("wpckan_show_dataset_detail "  . print_r($atts,true));

    $dataset;
    try{
      $dataset = wpckan_api_query_dataset_detail($atts);
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

    return get_option('wpckan_setting_ckan_url') . "/dataset/" . $dataset_name;
  }

  function wpckan_get_link_to_resource($dataset_name,$resource_id){
    wpckan_log("wpckan_get_link_to_resource "  . print_r($dataset_name,true) . " " . print_r($resource_id,true));

    return wpckan_get_link_to_dataset($dataset_name) . "/resource/" . $resource_id;
  }

  /*
  * Logging
  */

  function wpckan_log($text) {
    if (!get_option('wpckan_setting_log_enabled')) return;

    $bt = debug_backtrace();
    $caller = array_shift($bt);

    if (!wpckan_is_null_or_empty_string(get_option('wpckan_setting_log_path')))
      Analog::handler(Handler\File::init (get_option('wpckan_setting_log_path')));
    else
      Analog::handler(Handler\File::init (WPCKAN_DEFAULT_LOG));

    Analog::log ( "[ " . $caller['file'] . " | " . $caller['line'] . " ] " . $text );
  }

  /*
  * Utilities
  */

  function compose_solr_query_from_attrs($attrs){

    $arguments = array();

    // query
    if (isset($attrs['query'])):
      $arguments['q'] = $attrs['query'];
    endif;

    $fq = "";

    // Ids
    if (isset($attrs['ids'])):
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
      foreach ($filter_fields_json as $field => $value):
        $fq = $fq . '+' . $field . ':' . $value;
      endforeach;
    endif;

    // filter
    if (isset($attrs['filter'])):
      if ((int)$attrs['filter'] == 1):
        $fq = $fq . '+num_resources:[1 TO *]';
      endif;
    endif;

    if (!empty($fq)):
      $arguments['fq'] = urldecode($fq);
    endif;

    // limit
    if (isset($attrs['limit'])):
      $limit = (int)$attrs['limit'];
      $arguments['rows'] = $limit;
      if (isset($attrs['page'])):
        $page = (int)$attrs['page'];
        if ($page > 0):
          $arguments['start'] = $limit * ($page - 1);
        endif;
      endif;
    endif;

    return $arguments;
  }

  function wpckan_is_supported_post_type($post_type){
   $settings_name =  "setting_supported_post_types_" . $post_type;
   return get_option($settings_name);
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
    $post_content = wpckan_strip_mqtranslate_tags($post_content);
    return $post_content;
  }

  function wpckan_detect_and_remove_shortcodes_in_text($text)
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

  function wpckan_get_complete_url_for_dataset($dataset){
    return get_option('wpckan_setting_ckan_url') . "/dataset/" . $dataset["name"];
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
    return !wpckan_is_null_or_empty_string(get_option('wpckan_setting_ckan_api'));
  }

  function wpckan_get_ckan_settings(){

    $settings = array(
      'baseUrl' => get_option('wpckan_setting_ckan_url') . "/api/",
      'scheme' => 'http',
      'apiKey' => get_option('wpckan_setting_ckan_api')
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

  function wpckan_sanitize_csv($input) {
    $clean_url =  str_replace(" ", "", $input);
    return $clean_url;
  }

  function wpckan_strip_mqtranslate_tags($input) {
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
    return (!isset($question) || trim($question)==='');
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

  function wpckan_replace_domains_on_link($url){
    $redirection = get_option('wpckan_setting_ckan_url_redirection');
    if (empty($redirection)):
      return $url;
    endif;

    $ckan_domain = get_option('wpckan_setting_ckan_url');
    return $clean_url =  str_replace($ckan_domain,$redirection, $url);
  }

?>
