<?php

class Wpckan_Query_Resources_Widget extends WP_Widget
{

 public function __construct() {
  parent::__construct(
   'wpckan_query_resources_widget',
   __('WPCKAN Query Datasets', 'wpckan'),
   array('description' => __('Query resources and displays them in a post or page.', 'wpckan'))
  );
 }

 /**
  * Outputs the content of the widget
  *
  * @param array $args
  * @param array $instance
  */
 public function widget( $args, $instance ) {

  global $post;

   $shortcode = '[wpckan_query_datasets query="' . $instance['query'] . '"';
   if (!empty($instance['group']) && $instance['group'] != '-1')
     $shortcode .= ' group="' . $instance['group'] . '"';
   if (!empty($instance['organization']) && $instance['organization'] != '-1')
     $shortcode .= ' organization="' . $instance['organization'] . '"';
   if (!empty($instance['limit']) && $instance['limit'] > 0)
     $shortcode .= ' limit="' . $instance['limit'] . '"';
   $shortcode .= ' include_fields_dataset="title" include_fields_resources="format" blank_on_empty="true"]';

   $output = do_shortcode($shortcode);

   if (!empty($output) && $output != ""){

     echo $args['before_widget'];
     if ( ! empty( $instance['title'] ) ) {
      echo $args['before_title'] . apply_filters( 'widget_title', __( $instance['title'], 'wpckan')). $args['after_title'];
     }

     echo $output;

    if(!empty($instance['more_link']) && $instance['more_link'] != "" )
      echo '<div style="text-align:right"><a href="'.$instance['more_link'].'" target="_blank">'.$instance['more_text'].'</a></div>';

     echo $args['after_widget'];

   }

 }

 /**
  * Outputs the options form on admin
  *
  * @param array $instance The widget options
  */
 public function form( $instance ) {
  // outputs the options form on admin
  $title = ! empty( $instance['title'] ) ? __( $instance['title'], 'wpckan') : __( 'Related datasets', 'wpckan' );
  $query = ! empty( $instance['query'] ) ? $instance['query'] : null;
  $limit = ! empty( $instance['limit'] ) ? $instance['limit'] : 0;
  $more_text = ! empty( $instance['more_text'] ) ? $instance['more_text'] : 'Search for more';
  $more_link = ! empty( $instance['more_link'] ) ? $instance['more_link'] : '';
  $organization = ! empty( $instance['organization'] ) ? $instance['organization'] : -1;
  $organization_list = [];
  if (function_exists('wpckan_api_get_organizations_list')){
    try{
      $organization_list = wpckan_api_get_organizations_list();
    } catch(Exception $e){

    }
  }
  $group = ! empty( $instance['group'] ) ? $instance['group'] : -1;
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
   <label for="<?php echo $this->get_field_id( 'query' ); ?>"><?php _e( 'Query:' ); ?></label>
   <input class="widefat" id="<?php echo $this->get_field_id( 'query' ); ?>" name="<?php echo $this->get_field_name( 'query' ); ?>" type="text" value="<?php echo esc_attr( $query ); ?>">
   <label for="<?php echo $this->get_field_id( 'organization' ); ?>"><?php _e( 'CKAN Organization:' ); ?></label>
   <select class="widefat" id="<?php echo $this->get_field_id( 'organization' ); ?>" name="<?php echo $this->get_field_name( 'organization' ); ?>">
      <option <?php if($organization == -1) echo 'selected="selected"' ?> value="-1"><?php _e('All','wpckan')?></option>
      <?php foreach ($organization_list as $dataset_organization){ ?>
       <option <?php if($dataset_organization['id'] == $organization) echo 'selected="selected"' ?> value="<?php echo $dataset_organization['id']; ?>"><?php echo $dataset_organization['display_name']; ?></option>
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
    <input class="widefat" type="number" id="<?php echo $this->get_field_id( 'limit' ); ?>" name="<?php echo $this->get_field_name( 'limit' ); ?>" value="<?php echo $limit; ?>">
    <label for="<?php echo $this->get_field_id( 'more_text' ); ?>"><?php _e( 'More dataset: Link label' ); ?></label>
    <input class="widefat" type="text" id="<?php echo $this->get_field_id( 'more_text' ); ?>" name="<?php echo $this->get_field_name( 'more_text' ); ?>" placeholder="Search for more" value="<?php echo esc_attr( $more_text ); ?>">
    <label for="<?php echo $this->get_field_id( 'more_link' ); ?>"><?php _e( 'More dataset: Link (URL)' ); ?></label>
    <input class="widefat" type="text" id="<?php echo $this->get_field_id( 'more_link' ); ?>" name="<?php echo $this->get_field_name( 'more_link' ); ?>" value="<?php echo esc_attr( $more_link ); ?>">
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
  $instance['query'] = ( ! empty( $new_instance['query'] ) ) ? strip_tags( $new_instance['query'] ) : '';
  $instance['organization'] = ( ! empty( $new_instance['organization'] ) ) ? strip_tags( $new_instance['organization'] ) : '';
  $instance['group'] = ( ! empty( $new_instance['group'] ) ) ? strip_tags( $new_instance['group'] ) : '';
  $instance['limit'] = ( ! empty( $new_instance['limit'] ) ) ? $new_instance['limit'] : 0;
  $instance['more_text'] = ( ! empty( $new_instance['more_text'] ) ) ? $new_instance['more_text'] : 0;
  $instance['more_link'] = ( ! empty( $new_instance['more_link'] ) ) ? $new_instance['more_link'] : 0;

  return $instance;
 }
}

?>
