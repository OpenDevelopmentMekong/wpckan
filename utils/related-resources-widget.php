<?php

class Wpckan_Related_Resources_Widget extends WP_Widget {

 /**
  * Sets up the widgets name etc
  */
 public function __construct() {
  // widget actual processes
  parent::__construct(
   'wpckan_related_resources_widget',
   __('WPCKAN Related Resources', 'wpckan'),
   array('description' => __('Display post related resources.', 'wpckan'))
  );
 }

 /**
  * Outputs the content of the widget
  *
  * @param array $args
  * @param array $instance
  */
 public function widget( $args, $instance ) {
  // outputs the content of the widget


  global $post;

  $shortcode = '[wpckan_related_datasets';
  if (!empty($instance['group']) && $instance['group'] != '-1'){
    $shortcode .= ' group="' . $instance['group'] . '"';
    $group =  $instance['group'];
  }if (!empty($instance['organization']) && $instance['organization'] != '-1'){
    $shortcode .= ' organization="' . $instance['organization'] . '"';
    $organization =  ucwords(str_replace("-organization", "", $instance['organization']));
  }if (!empty($instance['limit']) && $instance['limit'] > 0){
    $shortcode .= ' limit="' . $instance['limit'] . '"';
  }
  $shortcode .= ' include_fields_dataset="title" include_fields_resources="format" blank_on_empty="true"]';
  $output = do_shortcode($shortcode);

      //get the taxonomy
      $get_page_title = get_the_title();
      $term = get_term_by('name', $get_page_title, 'category');
      if($term->parent==0);
        $taxonomy = $get_page_title;
      $more_link = "https://data.opendevelopmentmekong.net/dataset?odm_spatial_range=".$organization."&groups=".$group."&vocab_taxonomy=".$taxonomy;
      if (!empty($output) && $output != ""){

    echo $args['before_widget'];
    if ( ! empty( $instance['title'] ) ) {
     echo $args['before_title'] . apply_filters( 'widget_title', __( $instance['title'], 'wpckan') ). $args['after_title'];
    }

    echo $output;
    echo '<div style="text-align:right"><a href="'.$more_link.'" target="_blank">'.__('More...', 'wpckan').'</a></div>';
    echo $args['after_widget'];

  }

  //else
  //  echo "<p>" . __('No results returned.','wpckan') . "</p>";


 }

 /**
  * Outputs the options form on admin
  *
  * @param array $instance The widget options
  */
 public function form( $instance ) {
  // outputs the options form on admin
  $title = ! empty( $instance['title'] ) ? __( $instance['title'], 'wpckan') : __( 'Related Resources', 'wpckan' );
  $limit = ! empty( $instance['limit'] ) ? $instance['limit'] : 0;
  $organization = $instance['organization'];
  $organization_list = [];
  if (function_exists('wpckan_api_get_organizations_list')){
    try{
      $organization_list = wpckan_api_get_organizations_list();
    } catch(Exception $e){

    }
  }
  $group = $instance['group'];
  $group_list = [];
  if (function_exists('wpckan_api_get_groups_list')){
    try{
      $group_list = wpckan_api_get_groups_list();
    } catch(Exception $e){

    }
  }

  ?>
  <p>
   <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
   <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
   <label for="<?php echo $this->get_field_id( 'organization' ); ?>"><?php _e( 'CKAN Organization:' ); ?></label>
   <select class="widefat" id="<?php echo $this->get_field_id( 'organization' ); ?>" name="<?php echo $this->get_field_name( 'organization' ); ?>">
      <option <?php if($organization == -1) echo 'selected="selected"' ?> value="-1"><?php _e('All','wpckan')?></option>
      <?php foreach ($organization_list as $dataset_organization){ ?>
       <option <?php if($dataset_organization['name'] == $organization) echo 'selected="selected"' ?> value="<?php echo $dataset_organization['name']; ?>"><?php echo $dataset_organization['display_name']; ?></option>
      <?php } ?>
    </select>
   <label for="<?php echo $this->get_field_id( 'group' ); ?>"><?php _e( 'CKAN Group:' ); ?></label>
   <select class="widefat" id="<?php echo $this->get_field_id( 'group' ); ?>" name="<?php echo $this->get_field_name( 'group' ); ?>">
      <option <?php if($group == -1) echo 'selected="selected"' ?> value="-1"><?php _e('All','wpckan')?></option>
      <?php foreach ($group_list as $dataset_group){ ?>
       <option <?php if($dataset_group['name'] == $group) echo 'selected="selected"' ?> value="<?php echo $dataset_group['name']; ?>"><?php echo $dataset_group['display_name']; ?></option>
      <?php } ?>
    </select>
    <label for="<?php echo $this->get_field_id( 'limit' ); ?>"><?php _e( 'Limit:' ); ?></label>
    <input class="widefat" type="number" id="<?php echo $this->get_field_id( 'limit' ); ?>" name="<?php echo $this->get_field_name( 'limit' ); ?>" value="<?php echo esc_attr( $limit ); ?>">
  </p>
  <?php
 }

 /**
  * Processing widget options on save
  *
  * @param array $new_instance The new options
  * @param array $old_instance The previous options
  */
 public function update( $new_instance, $old_instance ) {
  // processes widget options to be saved
  $instance = array();
  $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
  $instance['organization'] = ( ! empty( $new_instance['organization'] ) ) ? strip_tags( $new_instance['organization'] ) : '';
  $instance['group'] = ( ! empty( $new_instance['group'] ) ) ? strip_tags( $new_instance['group'] ) : '';
  $instance['limit'] = ( ! empty( $new_instance['limit'] ) ) ? strip_tags( $new_instance['limit'] ) : '';

  return $instance;
 }
}
