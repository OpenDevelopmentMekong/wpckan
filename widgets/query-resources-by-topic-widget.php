<?php

class Wpckan_Query_Resources_By_Topic_Widget extends WP_Widget {

	/**
	 * Sets up the widgets name etc
	 */
	public function __construct() {
		// widget actual processes
		parent::__construct(
			'wpckan_query_resources_by_topic_widget',
			__('WPCKAN Resources by post\'s topic', 'opendev'),
			array('description' => __('Queries CKAN for datasets tagged with the same tags as the post/page the widget is added to.', 'opendev'))
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

		$limit = isset($instance['limit']) ? $instance['limit'] : -1;
		$categories = wp_get_post_categories($post->ID);
		$related_posts = array();

		if (!empty($categories)):
			foreach ($this->available_post_types() as $post_type):
				if (isset($instance[$post_type->name]) && $instance[$post_type->name]):

					$taxonomy_terms = get_terms( 'category', array(
					    'hide_empty' => 0,
					    'fields' => 'ids'
						)
					);

					print_r($taxonomy_terms);

					$query = array(
			      'post_type' => $post_type->name,
			      'tax_query' => array(
			         array(
			            'taxonomy' => 'category',
			            'field' => 'id',
			            'terms' => array(1)
			         )
						 )
			   );

				 $slider_posts = new WP_Query($query);

				 if($slider_posts->have_posts()) : ?>

					<div class='slider'>
					   <?php while($slider_posts->have_posts()) : $slider_posts->the_post() ?>
					      <div class='slide'>
					         <h1><?php the_title() ?></h1>
					      </div>
					   <?php endwhile ?>
					</div>

				<?php endif;



				endif;
			endforeach;
		endif;

		echo $args['before_widget']; ?>

		<?php
			if (!empty($instance['title'])):
				 echo $args['before_title'].apply_filters('widget_title', __($instance['title'], 'opendev')).$args['after_title'];
			endif;

				//print_r($related_posts);
				?>

		<ul>

			<?php foreach($related_posts as $post):?>
				<li>
					<a href="<?php echo get_permalink($post->ID);?>"><?php echo $post->post_title . " " . $post->post_type;?></a>
				</li>
			<?php endforeach; ?>

		</ul>

		<?php echo $args['after_widget'];

	}

	/**
	 * Outputs the options form on admin
	 *
	 * @param array $instance The widget options
	 */
	public function form( $instance ) {

	  $post_types = $this->available_post_types();

		$title = !empty($instance['title']) ? __($instance['title'], 'opendev') : __('Custom posts', 'opendev'); ?>
		<p>
			<label for="<?php echo $this->get_field_id('title');?>"><?php _e('Title:');?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('title');?>" name="<?php echo $this->get_field_name('title');?>" type="text" value="<?php echo esc_attr($title);?>">
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'post_type' ); ?>"><?php _e( 'Select custom post type:' ); ?></label>
			<?php foreach ( $post_types  as $post_type ): ?>
				<p>
					<input type="checkbox" <?php if (isset($instance[$post_type->name]) && $instance[$post_type->name]) { echo " checked"; } ?> name="<?php echo $this->get_field_name($post_type->name)?>" value="<?php echo $post_type->name ?>"><?php echo $post_type->labels->name ?></input>
				</p>
			<?php endforeach; ?>
		</p>

		<?php $limit = !empty($instance['limit']) ? $instance['limit'] : -1 ?>
		<p>
			<label for="<?php echo $this->get_field_id( 'limit' ); ?>"><?php _e( 'Select max number of posts to list (-1 to show all):' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('limit');?>" name="<?php echo $this->get_field_name('limit');?>" type="number" value="<?php echo $limit;?>">
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
		$instance['limit'] = (!empty($new_instance['limit'])) ? strip_tags($new_instance['limit']) : -1;

		$post_types = $this->available_post_types();
		foreach ($post_types as $post_type):
			$instance[$post_type->name] = (!empty($new_instance[$post_type->name])) ? true : false;
		endforeach;
		return $instance;
	}

	private function available_post_types(){
		$args = array(
		   'public'   => true,
		   '_builtin' => false
		);

		$output = 'objects';
		$operator = 'and';
		$post_types = get_post_types( $args, $output, $operator );

		return $post_types;
	}

}

add_action( 'widgets_init', create_function('', 'register_widget("Wpckan_Query_Resources_By_Topic_Widget");'));

?>
