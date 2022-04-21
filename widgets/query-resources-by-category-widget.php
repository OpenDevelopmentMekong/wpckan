<?php

class Wpckan_Query_Resources_By_Topic_Widget extends WP_Widget
{

    /**
     * Set up the widget
     */
    public function __construct()
    {
        parent::__construct(
            'wpckan_query_resources_by_topic_widget',
            __('WPCKAN Query resources by post\'s category', 'wpckan'),
            array('description' => __('Queries CKAN for datasets with the post\'s category as value for the field specified.', 'wpckan'))
        );

        $this->sort_options = array(
            'metadata_modified+desc'        => 'Metadata modified',
            'metadata_created+desc'         => 'Metadata created',
            'relevance+asc'                 => 'Relevance',
            'views_recent+desc'             => 'Views recent',
            'odm_date_created+desc'         => 'Creation date (Datasets)',
            'marc21_260c+desc'              => 'Publication date (Library)',
            'odm_promulgation_date+desc'    => 'Promulgation date (Laws)'
        );

        $this->templates = array(
            'dataset-list' => 'dataset-list',
            'dataset-grid' => 'dataset-grid'
        );
    }

    private function get_categories($post)
    {
        $categories_names = array();

        if (isset($post)) :
            global $wpdb;

            $get_post_cat_ids = wp_get_post_categories($post->ID, array('fields' => 'ids'));
            $table_name = $wpdb->prefix . "terms";

            $cat_results = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT name
                    FROM $table_name
                    WHERE `term_id` IN (" . implode(', ', array_fill(0, count($get_post_cat_ids), '%d')) . ")
                    ",
                    $get_post_cat_ids
                )
            );

            if ($cat_results) :
                foreach ($cat_results as $key => $cat) {
                    $categories_names[] = $cat->name;
                }
            endif;
        endif;

        if (isset($_GET['id'])) :
            try {
                $dataset = wpckan_api_package_show(wpckan_get_ckan_domain(), $_GET['id']);
                $categories_names = $dataset['taxonomy'];
            } catch (Exception $e) { #
                wpckan_log($e->getMessage());
            }
        endif;

        return $categories_names;
    }

    /**
     * Outputs the content of the widget
     *
     * @param array $args
     * @param array $instance
     */
    public function widget($args, $instance)
    {
        global $post;

        $search_field               = isset($instance['search_field']) ? sanitize_text_field($instance['search_field']) : 'title now';
        $limit                      = isset($instance['limit']) ? sanitize_text_field($instance['limit']) : -1;
        $categories_names           = $this->get_categories($post);
        $template                   = isset($instance['template']) ? $instance['template'] : 'dataset-list';
        $output_fields              = isset($instance['output_fields']) ? sanitize_text_field($instance['output_fields']) : 'title';
        $output_fields_resources    = isset($instance['output_fields_resources']) ? sanitize_text_field($instance['output_fields_resources']) : '';

        $filter_value = "(\"" . implode("\" OR \"", $categories_names) . "\")";

        if (!empty($categories_names) && !(empty($search_field))) :
            $shortcode = '[wpckan_query_datasets filter_fields=\'{"' . $search_field . '":"' . urlencode($filter_value) . '"}\'';
            
            if ($template !== "dataset-list") :
                $shortcode .= ' template="' . $template . '"';
            endif;

            if (!empty($instance['organization']) && $instance['organization'] != '-1') :
                $shortcode .= ' organization="' . $instance['organization'] . '"';
            endif;

            if (!empty($instance['type'])) :
                $shortcode .= ' type="' . $instance['type'] . '"';
            endif;

            if (!empty($instance['limit']) && $instance['limit'] > 0) :
                $shortcode .= ' limit="' . $instance['limit'] . '"';
            endif;

            if (!empty($instance['sort'])) :
                $shortcode .= ' sort="' . $instance['sort'] . '"';
            endif;

            $shortcode .= ' include_fields_dataset="' . $output_fields . '" include_fields_resources="' . $output_fields_resources . '" blank_on_empty="true"]';

            $output = do_shortcode($shortcode);

            if (!empty($output) && $output != "") :
                echo $args['before_widget'];

                if (!empty($instance['title'])) :
                    echo $args['before_title'] . apply_filters('widget_title', __($instance['title'], 'wpckan')) . $args['after_title'];
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
    public function form($instance)
    {
        $title                      = !empty($instance['title']) ? __(sanitize_text_field($instance['title']), 'wpckan') : __('Custom posts', 'wpckan');
        $organization               = isset($instance['organization']) ? sanitize_text_field($instance['organization']) : -1;
        $search_field               = !empty($instance['search_field']) ? sanitize_text_field($instance['search_field']) : 'title';
        $type                       = !empty($instance['type']) ? sanitize_text_field($instance['type']) : 'dataset';
        $limit                      = !empty($instance['limit']) ? sanitize_text_field($instance['limit']) : -1;

        $template                   = isset($instance['template']) ? sanitize_text_field($instance['template']) : 'dataset-list';
        $output_fields              = !empty($instance['output_fields']) ? sanitize_text_field($instance['output_fields']) : 'title';
        $output_fields_resources    = !empty($instance['output_fields_resources']) ? sanitize_text_field($instance['output_fields_resources']) : '';
        $sort                       = isset($instance['sort']) ? sanitize_text_field($instance['sort']) : 'metadata_modified+desc';

        if (function_exists('wpckan_api_get_organizations_list')) {
            try {
                $organization_list = wpckan_api_get_organizations_list();
            } catch (Exception $e) {
            }
        }
        ?>

        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'wpckan'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>">
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('organization'); ?>"><?php _e('CKAN Organization:', 'wpckan'); ?></label>
            <select class="widefat" id="<?php echo $this->get_field_id('organization'); ?>" name="<?php echo $this->get_field_name('organization'); ?>">
                <option <?php echo ($organization == -1) ? 'selected' : ''; ?> value="-1"><?php _e('All', 'wpckan') ?></option>
                <?php foreach ($organization_list as $dataset_organization) : ?>
                    <option <?php echo ($dataset_organization['id'] == $organization) ? 'selected' : ''; ?> value="<?php echo $dataset_organization['id']; ?>"><?php echo $dataset_organization['display_name']; ?></option>
                <?php endforeach; ?>
            </select>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('search_field'); ?>"><?php _e('Search field:', 'wpckan'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('search_field'); ?>" name="<?php echo $this->get_field_name('search_field'); ?>" type="text" value="<?php echo esc_attr($search_field); ?>">
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('type'); ?>"><?php _e('Dataset type:', 'wpckan'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('type'); ?>" name="<?php echo $this->get_field_name('type'); ?>" type="text" value="<?php echo esc_attr($type); ?>" placeholder="<?php _e('dataset, library_record, etc..', 'wpckan'); ?>">
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('limit'); ?>"><?php _e('Select max number of posts to list (-1 to show all):', 'wpckan'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('limit'); ?>" name="<?php echo $this->get_field_name('limit'); ?>" type="number" min="-1" value="<?php echo $limit; ?>">
        </p>

        <h3>Output</h3>
        <p>
            <label for="<?php echo $this->get_field_id('template'); ?>"><?php _e('Select layout:', 'wpckan'); ?></label>
            <select class='widefat template template-selector' id="<?php echo $this->get_field_id('template'); ?>" name="<?php echo $this->get_field_name('template'); ?>" type="text">
                <?php foreach ($this->templates as $key => $value) : ?>
                    <option <?php echo ($template == $value) ? 'selected' : ''; ?> value="<?php echo $value ?>"><?php echo $key ?></option>
                <?php endforeach; ?>
            </select>
        </p>
        
        <div class="template-dependent-options">
            <p>
                <label for="<?php echo $this->get_field_id('output_fields'); ?>"><?php _e('Output fields for dataset:'); ?></label>
                <input class="widefat" id="<?php echo $this->get_field_id('output_fields'); ?>" name="<?php echo $this->get_field_name('output_fields'); ?>" type="text" value="<?php echo esc_attr($output_fields); ?>">
            </p>

            <p>
                <label for="<?php echo $this->get_field_id('output_fields_resources'); ?>"><?php _e('Output fields for resources:'); ?></label>
                <input class="widefat" id="<?php echo $this->get_field_id('output_fields_resources'); ?>" name="<?php echo $this->get_field_name('output_fields_resources'); ?>" type="text" value="<?php echo esc_attr($output_fields_resources); ?>">
            </p>

            <p>
                <label for="<?php echo $this->get_field_id('sort'); ?>"><?php _e('Order by:'); ?></label>
                <select class='widefat' id="<?php echo $this->get_field_id('sort'); ?>" name="<?php echo $this->get_field_name('sort'); ?>" type="text">
                    <?php foreach ($this->sort_options  as $key => $value) : ?>
                        <option <?php echo ($sort == $key) ? 'selected' : ''; ?> value="<?php echo $key ?>"><?php echo $value ?></option>
                    <?php endforeach; ?>
                </select>
            </p>
        </div>
    <?php
    }

    /**
     * Processing widget options on save
     *
     * @param array $new_instance The new options
     * @param array $old_instance The previous options
     */
    public function update($new_instance, $old_instance)
    {
        $instance = [];

        $instance['title']                      = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
        $instance['organization']               = (!empty($new_instance['organization'])) ? strip_tags($new_instance['organization']) : '';
        $instance['search_field']               = (!empty($new_instance['search_field'])) ? strip_tags($new_instance['search_field']) : 'title';
        $instance['type']                       = (!empty($new_instance['type'])) ? strip_tags($new_instance['type']) : 'dataset';
        $instance['limit']                      = (!empty($new_instance['limit'])) ? strip_tags($new_instance['limit']) : -1;
        $instance['template']                   = (!empty($new_instance['template'])) ? $new_instance['template'] : 'dataset-list';
        
        $instance['output_fields']              = (!empty($new_instance['output_fields'])) ? strip_tags($new_instance['output_fields']) : 'title';
        $instance['output_fields']              = wpckan_remove_whitespaces($instance['output_fields']);
        
        $instance['output_fields_resources']    = (!empty($new_instance['output_fields_resources'])) ? strip_tags($new_instance['output_fields_resources']) : '';
        $instance['output_fields_resources']    = wpckan_remove_whitespaces($instance['output_fields_resources']);
        
        $instance['sort']                       = (!empty($new_instance['sort'])) ? strip_tags($new_instance['sort']) : 'metadata_modified+desc';

        return $instance;
    }
}

add_action('widgets_init', function () {
    register_widget("Wpckan_Query_Resources_By_Topic_Widget");
});