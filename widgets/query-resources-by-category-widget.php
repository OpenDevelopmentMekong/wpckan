<?php

class Wpckan_Query_Resources_By_Topic_Widget extends WP_Widget {

	/**
	 * Sets up the widgets name etc
	 */
	public function __construct() {
		// widget actual processes
		parent::__construct(
			'wpckan_query_resources_by_topic_widget',
			__('WPCKAN Query resources by post\'s category', 'wpckan'),
			array('description' => __('Queries CKAN for datasets with the post\'s category as value for the field specified.', 'wpckan'))
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

		$search_field = isset($instance['search_field']) ? $instance['search_field'] : 'title';
		$limit = isset($instance['limit']) ? $instance['limit'] : -1;
		$categories_names = wp_get_post_categories($post->ID,array(
			"fields" => "slugs")
		);
    $output_fields = isset($instance['output_fields']) ? $instance['output_fields'] : 'title';
    $output_fields_resources = isset($instance['output_fields_resources']) ? $instance['output_fields_resources'] : '';

		$filter_value = "(" . implode(" OR ", $categories_names) . ")";

		if (!empty($categories_names) && !(empty($search_field))):

			$shortcode = '[wpckan_query_datasets filter_fields=\'{"'. $search_field .'":"' . $filter_value . '"}\'';

			if (!empty($instance['organization']) && $instance['organization'] != '-1'):
        $shortcode .= ' organization="'.$instance['organization'].'"';
      endif;

			if (!empty($instance['type'])):
				$shortcode .= ' type="'.$instance['type'].'"';
			endif;

			if (!empty($instance['limit']) && $instance['limit'] > 0)
	      $shortcode .= ' limit="' . $instance['limit'] . '"';

			$shortcode .= ' include_fields_dataset="' . $output_fields . '" include_fields_resources="'. $output_fields_resources.'" blank_on_empty="true"]';

			$output = do_shortcode($shortcode);

			if (!empty($output) && $output != ""):

				echo $args['before_widget'];

					if (!empty($instance['title'])):
						 echo $args['before_title'].apply_filters('widget_title', __($instance['title'], 'wpckan')).$args['after_title'];
					endif;

				echo $output;

				echo $args['after_widget'];

			endif;

		endif;

	}

	/**
	 * Outputs the options form on admin
	 *
	 * @param array $instance The widget options
	 */
	public function form( $instance ) {

		$title = !empty($instance['title']) ? __($instance['title'], 'wpckan') : __('Custom posts', 'wpckan');
		$type = !empty($instance['type']) ? $instance['type'] : 'dataset';
		$organization = isset($instance['organization']) ? $instance['organization'] : -1;
		$organization_list = [];
		if (function_exists('wpckan_api_get_organizations_list')) {
				try {
						$organization_list = wpckan_api_get_organizations_list();
				} catch (Exception $e) {
				}
		} ?>
		<p>
			<label for="<?php echo $this->get_field_id('title');?>"><?php _e('Title:');?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('title');?>" name="<?php echo $this->get_field_name('title');?>" type="text" value="<?php echo esc_attr($title);?>">
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('organization');?>"><?php _e('CKAN Organization:');?></label>
	    <select class="widefat" id="<?php echo $this->get_field_id('organization');?>" name="<?php echo $this->get_field_name('organization');?>">
	      <option <?php if ($organization == -1) { echo 'selected="selected"'; }?> value="-1"><?php _e('All', 'wpckan')?></option>
	      <?php foreach ($organization_list as $dataset_organization) { ?>
	        <option <?php if ($dataset_organization['id'] == $organization) { echo 'selected="selected"';}?> value="<?php echo $dataset_organization['id'];?>"><?php echo $dataset_organization['display_name'];?></option>
	      <?php } ?>
	    </select>
		</p>
		<?php
		$search_field = !empty($instance['search_field']) ? __($instance['search_field'], 'wpckan') : 'title' ?>
		<p>
			<label for="<?php echo $this->get_field_id('search_field');?>"><?php _e('Search field:');?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('search_field');?>" name="<?php echo $this->get_field_name('search_field');?>" type="text" value="<?php echo esc_attr($search_field);?>">
		</p>

		<p>
			<label for="<?php echo $this->get_field_id('type');?>"><?php _e('Dataset type:');?></label>
	    <input class="widefat" id="<?php echo $this->get_field_id('type');?>" name="<?php echo $this->get_field_name('type');?>" type="text" value="<?php echo esc_attr($type);?>" placeholder="<?php _e('dataset, library_record, etc..');?>">
		</p>

		<?php $limit = !empty($instance['limit']) ? $instance['limit'] : -1 ?>
		<p>
			<label for="<?php echo $this->get_field_id( 'limit' ); ?>"><?php _e( 'Select max number of posts to list (-1 to show all):' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('limit');?>" name="<?php echo $this->get_field_name('limit');?>" type="number" value="<?php echo $limit;?>">
		</p>
		<h3>Output</h3>
    <?php
    $output_fields = !empty($instance['output_fields']) ? __($instance['output_fields'], 'wpckan') : 'title';
    $output_fields_resources = !empty($instance['output_fields_resources']) ? __($instance['output_fields_resources'], 'wpckan') : '' ?>
		<p>
			<label for="<?php echo $this->get_field_id('output_fields');?>"><?php _e('Output fields for dataset:');?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('output_fields');?>" name="<?php echo $this->get_field_name('output_fields');?>" type="text" value="<?php echo esc_attr($output_fields);?>">
		</p>
    <p>
			<label for="<?php echo $this->get_field_id('output_fields_resources');?>"><?php _e('Output fields for resources:');?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('output_fields_resources');?>" name="<?php echo $this->get_field_name('output_fields_resources');?>" type="text" value="<?php echo esc_attr($output_fields_resources);?>">
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

		$instance = array();
		$instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
		$instance['organization'] = (!empty($new_instance['organization'])) ? strip_tags($new_instance['organization']) : '';
		$instance['search_field'] = (!empty($new_instance['search_field'])) ? strip_tags($new_instance['search_field']) : 'title';
		$instance['limit'] = (!empty($new_instance['limit'])) ? strip_tags($new_instance['limit']) : -1;
		$instance['type'] = (! empty( $new_instance['type'])) ? strip_tags( $new_instance['type'] ) : 'dataset';
	  $instance['output_fields'] = (!empty($new_instance['output_fields'])) ? strip_tags($new_instance['output_fields']) : 'title';
    $instance['output_fields'] = wpckan_remove_whitespaces($instance['output_fields']);
    $instance['output_fields_resources'] = (!empty($new_instance['output_fields_resources'])) ? strip_tags($new_instance['output_fields_resources']) : '';
    $instance['output_fields_resources'] = wpckan_remove_whitespaces($instance['output_fields_resources']);
		return $instance;
	}



}

add_action( 'widgets_init', create_function('', 'register_widget("Wpckan_Query_Resources_By_Topic_Widget");'));

?>
