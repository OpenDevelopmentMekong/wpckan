<?php

  define("FREQ_NEVER","0");
  define("FREQ_POST_PUBLISHED","1");
  define("FREQ_POST_SAVED","2");
  define("DEFAULT_LOG","/tmp/wpckan.log");

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
    if (!IsNullOrEmptyString($related_datasets_json))
      $related_datasets = json_decode($related_datasets_json,true);

    $limit = 0;
    if (array_key_exists("limit",$atts)){
      $limit = (int)($atts["limit"]);
    }

    $page = 0;
    if (array_key_exists("limit",$atts) && array_key_exists("page",$atts)){
      $page = (int)($atts["page"]);
    }

    $count = 0;
    $dataset_array = array();
    foreach ($related_datasets as $dataset){
      if (($page == 0) || (($count >= (($page-1) * $limit)) && ($count <= ($page * $limit)))){
        $dataset_atts = array("id" => $dataset["dataset_id"]);
        try{
          array_push($dataset_array,wpckan_api_get_dataset($dataset_atts));
        }catch(Exception $e){
          wpckan_log($e->getMessage());
        }
        if (($limit != 0) && (count($dataset_array) >= $limit)) break;
      }
      $count++;
    }
    return wpckan_output_template( plugin_dir_path( __FILE__ ) . '../templates/dataset_list.php',$dataset_array,$atts);
  }

  function wpckan_show_number_of_related_datasets($atts) {
    wpckan_log("wpckan_show_number_of_related_datasets " . print_r($atts,true));

    $related_datasets_json = get_post_meta( $atts['post_id'], 'wpckan_related_datasets', true );
    $related_datasets = array();
    if (!IsNullOrEmptyString($related_datasets_json))
    $related_datasets = json_decode($related_datasets_json,true);

    if (array_key_exists("group",$atts))
      $filter_group = $atts["group"];
    if (array_key_exists("organization",$atts)){
      $filter_organization = $atts["organization"];
    }

    $dataset_array = array();
    foreach ($related_datasets as $dataset){

      $qualifies = false;

      if (!isset($filter_group) && !isset($filter_organization)){
        $qualifies = true;
      }else{

        // Check if dataset belongs to group
        if (isset($filter_group)){
          $qualifies = false;
          $groups = json_decode($dataset["dataset_groups"], true);
          foreach ($groups as $group){
            if (strtolower($filter_group) == strtolower($group["name"])){
              $qualifies = true;
            }
          }
        }

        // Check if dataset belongs to organization
        if (isset($filter_organization) && isset($dataset["dataset_org"])){
          $qualifies = false;
          try{
            $organization = wpckan_api_get_organization($dataset["dataset_org"]);
            if ( $organization["name"] == $filter_organization){
              $qualifies = true;
            }
          }catch(Exception $e){
            wpckan_log($e->getMessage());
          }
        }
      }

      if ($qualifies){
        $dataset_atts = array("id" => $dataset["dataset_id"]);
        try{
          array_push($dataset_array,wpckan_api_get_dataset($dataset_atts));
        }catch(Exception $e){
          wpckan_log($e->getMessage());
        }
        if (array_key_exists("limit",$atts) && (count($dataset_array) >= (int)($atts["limit"]))) break;
      }
    }
    return wpckan_output_template( plugin_dir_path( __FILE__ ) . '../templates/dataset_number.php',$dataset_array,$atts);
  }

  function wpckan_show_query_datasets($atts) {
    wpckan_log("wpckan_show_query_datasets "  . print_r($atts,true));

    $dataset_array = array();
    try{
      $dataset_array = wpckan_api_query_datasets($atts);
    }catch(Exception $e){
      wpckan_log($e->getMessage());
    }

    return wpckan_output_template( plugin_dir_path( __FILE__ ) . '../templates/dataset_list.php',$dataset_array,$atts);
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

    return get_option('setting_ckan_url') . "/dataset/" . $dataset_name;
  }

  function wpckan_get_link_to_resource($dataset_name,$resource_id){
    wpckan_log("wpckan_get_link_to_resource "  . print_r($dataset_name,true) . " " . print_r($resource_id,true));

    return wpckan_get_link_to_dataset($dataset_name) . "/resource/" . $resource_id;
  }

  /*
  * Logging
  */

  function wpckan_log($text) {
    if (!get_option('setting_log_enabled')) return;
    if (!IsNullOrEmptyString(get_option('setting_log_path')))
      Analog::handler(Handler\File::init (get_option('setting_log_path')));
    else
      Analog::handler(Handler\File::init (DEFAULT_LOG));

    Analog::log ($text);
  }

  /*
  * Utilities
  */

  function wpckan_cleanup_text_for_archiving($post_content){
    $post_content = wpckan_detect_and_remove_shortcodes_in_text($post_content);
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
    return get_option('setting_ckan_url') . "/dataset/" . $dataset["name"];
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
    return !IsNullOrEmptyString(get_option('setting_ckan_api'));
  }

  function wpckan_get_ckan_settings(){

      $settings = array(
        'baseUrl' => get_option('setting_ckan_url') . "/api/",
        'scheme' => 'http',
        'apiKey' => get_option('setting_ckan_api')
      );

      return $settings;
    }

  function wpckan_post_should_be_archived_on_publish($post_ID){
    $archive_freq = get_post_meta( $post_ID, 'wpckan_archive_post_freq', true);
    wpckan_log("wpckan_post_should_be_archived_on_publish freq: " . $archive_freq);
    return ( $archive_freq == FREQ_POST_PUBLISHED);
  }

  function wpckan_post_should_be_archived_on_save($post_ID){
    $archive_freq = get_post_meta( $post_ID, 'wpckan_archive_post_freq', true);
    wpckan_log("wpckan_post_should_be_archived_on_save freq: " . $archive_freq);
    return ( $archive_freq == FREQ_POST_SAVED);
  }

  function wpckan_sanitize_url($input) {
    $clean_url = esc_url($input);
    if(substr($clean_url, -1) == '/') {
      $clean_url = substr($clean_url, 0, -1);
    }
    return $clean_url;
  }

  function IsNullOrEmptyString($question){
    return (!isset($question) || trim($question)==='');
  }


?>
