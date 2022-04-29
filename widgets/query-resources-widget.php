<?php
class Wpckan_Query_Resources_Widget extends WP_Widget
{
    public function __construct()
    {
        parent::__construct(
            'wpckan_query_resources_widget',
            __('WPCKAN Query Datasets', 'wpckan'),
            array('description' => __('Query resources and displays them in a post or page.', 'wpckan'))
        );

        $this->sort_options = array(
            "metadata_modified+desc"        => "Metadata modified",
            "metadata_created+desc"         => "Metadata created",
            "relevance+asc"                 => "Relevance",
            "views_recent+desc"             => "Views recent",
            "odm_date_created+desc"         => "Creation date (Datasets)",
            "marc21_260c+desc"              => "Publication date (Library)",
            "odm_promulgation_date+desc"    => "Promulgation date (Laws)"
        );

        $this->templates = array(
            "dataset-list" => "dataset-list",
            "dataset-grid" => "dataset-grid"
        );
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

        $template = isset($instance['template']) ? $instance['template'] : 'dataset-list';

        $shortcode = '[wpckan_query_datasets query="' . $instance['query'] . '"';

        if ($template !== "dataset-list") :
            $shortcode .= ' template="' . $template . '"';
        endif;

        if (!empty($instance['group']) && $instance['group'] != '-1') :
            $shortcode .= ' group="' . $instance['group'] . '"';
        endif;

        if (!empty($instance['organization']) && $instance['organization'] != '-1') :
            $shortcode .= ' organization="' . $instance['organization'] . '"';
        endif;

        if (!empty($instance['filter_fields']) && json_decode($instance['filter_fields'])) :
            $shortcode .= ' filter_fields=\'' . $instance['filter_fields'] . '\'';
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

        $shortcode .= ' include_fields_dataset="' . $instance['output_fields'] . '" include_fields_resources="' . $instance['output_fields_resources'] . '" blank_on_empty="true"]';

        $output = do_shortcode($shortcode);

        if (!empty($output) && $output != "") :
            echo $args['before_widget'];

            if (!empty($instance['title'])) :
                echo $args['before_title'] . apply_filters('widget_title', __($instance['title'], 'wpckan')) . $args['after_title'];
            endif;

            echo $output;

            if (!empty($instance['more_link']) && $instance['more_link'] != "") :
                echo '<div style="text-align:right"><a href="' . $instance['more_link'] . '" target="_blank">' . $instance['more_text'] . '</a></div>';
            endif;

            echo $args['after_widget'];
        endif;
    }

    /**
     * Outputs the options form on admin
     *
     * @param array $instance The widget options
     */
    public function form($instance)
    {
        // outputs the options form on admin
        $title              = !empty($instance['title']) ? __($instance['title'], 'wpckan') : __('Related datasets', 'wpckan');
        $query              = !empty($instance['query']) ? $instance['query'] : null;
        $limit              = !empty($instance['limit']) ? $instance['limit'] : 0;
        $filter_fields      = !empty($instance['filter_fields']) && json_decode($instance['filter_fields']) ? $instance['filter_fields'] : null;
        $type               = !empty($instance['type']) ? $instance['type'] : 'dataset';
        $more_text          = !empty($instance['more_text']) ? $instance['more_text'] : 'Search for more';
        $more_link          = !empty($instance['more_link']) ? $instance['more_link'] : '';
        $organization       = !empty($instance['organization']) ? $instance['organization'] : -1;
        $organization_list  = [];
        $template           = isset($instance['template']) ? $instance['template'] : 'dataset-list';

        if (function_exists('wpckan_api_get_organizations_list')) {
            try {
                $organization_list = wpckan_api_get_organizations_list();
            } catch (Exception $e) {
            }
        }

        $group = !empty($instance['group']) ? $instance['group'] : -1;
        $group_list = [];

        if (function_exists('wpckan_api_get_groups_list')) {
            try {
                $group_list = wpckan_api_get_groups_list();
            } catch (Exception $e) {
            }
        }

        $output_fields = !empty($instance['output_fields']) ? $instance['output_fields'] : 'title';
        $output_fields_resources = !empty($instance['output_fields_resources']) ? $instance['output_fields_resources'] : '';
        $sort = isset($instance['sort']) ? $instance['sort'] : 'metadata_modified+desc';
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'wpckan'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('query'); ?>"><?php _e('Query:', 'wpckan'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('query'); ?>" name="<?php echo $this->get_field_name('query'); ?>" type="text" value="<?php echo esc_attr($query); ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('organization'); ?>"><?php _e('CKAN Organization:', 'wpckan'); ?></label>
            <select class="widefat" id="<?php echo $this->get_field_id('organization'); ?>" name="<?php echo $this->get_field_name('organization'); ?>">
                <option <?php if ($organization == -1) echo 'selected="selected"' ?> value="-1"><?php _e('All', 'wpckan') ?></option>
                <?php foreach ($organization_list as $dataset_organization) { ?>
                    <option <?php if ($dataset_organization['id'] == $organization) echo 'selected="selected"' ?> value="<?php echo $dataset_organization['id']; ?>"><?php echo $dataset_organization['display_name']; ?></option>
                <?php } ?>
            </select>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('group'); ?>"><?php _e('CKAN Group:', 'wpckan'); ?></label>
            <select class="widefat" id="<?php echo $this->get_field_id('group'); ?>" name="<?php echo $this->get_field_name('group'); ?>">
                <option <?php if ($group == -1) echo 'selected="selected"' ?> value="-1"><?php _e('All', 'wpckan') ?></option>
                <?php foreach ($group_list as $dataset_group) { ?>
                    <option <?php if ($dataset_group['name'] == $group) echo 'selected="selected"' ?> value="<?php echo $dataset_group['name']; ?>"><?php echo $dataset_group['display_name']; ?></option>
                <?php } ?>
            </select>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('filter_fields'); ?>"><?php _e('Additional filtering:', 'wpckan'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('filter_fields'); ?>" name="<?php echo $this->get_field_name('filter_fields'); ?>" type="text" value="<?php echo esc_attr($filter_fields); ?>" placeholder="<?php _e('Specify valid JSON, otherwise not saved'); ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('type'); ?>"><?php _e('Dataset type:', 'wpckan'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('type'); ?>" name="<?php echo $this->get_field_name('type'); ?>" type="text" value="<?php echo esc_attr($type); ?>" placeholder="<?php _e('dataset, library_record, etc..'); ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('limit'); ?>"><?php _e('Limit:', 'wpckan'); ?></label>
            <input class="widefat" type="number" id="<?php echo $this->get_field_id('limit'); ?>" name="<?php echo $this->get_field_name('limit'); ?>" value="<?php echo $limit; ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('more_text'); ?>"><?php _e('More dataset: Link label', 'wpckan'); ?></label>
            <input class="widefat" type="text" id="<?php echo $this->get_field_id('more_text'); ?>" name="<?php echo $this->get_field_name('more_text'); ?>" placeholder="Search for more" value="<?php echo esc_attr($more_text); ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('more_link'); ?>"><?php _e('More dataset: Link (URL)', 'wpckan'); ?></label>
            <input class="widefat" type="text" id="<?php echo $this->get_field_id('more_link'); ?>" name="<?php echo $this->get_field_name('more_link'); ?>" value="<?php echo esc_attr($more_link); ?>">
        </p>
        <h3>Output</h3>
        <p>
            <label for="<?php echo $this->get_field_id('template'); ?>"><?php _e('Select layout:', 'wpckan'); ?></label>
            <select class='widefat template template-selector' id="<?php echo $this->get_field_id('template'); ?>" name="<?php echo $this->get_field_name('template'); ?>" type="text">
                <?php foreach ($this->templates  as $key => $value) : ?>
                    <option <?php if ($template == $value) {
                                echo " selected";
                            } ?> value="<?php echo $value ?>"><?php echo $key ?></option>
                <?php endforeach; ?>
            </select>
        </p>
        <div class="template-dependent-options">
            <p>
                <label for="<?php echo $this->get_field_id('output_fields'); ?>"><?php _e('Output fields for dataset:', 'wpckan'); ?></label>
                <input class="widefat" id="<?php echo $this->get_field_id('output_fields'); ?>" name="<?php echo $this->get_field_name('output_fields'); ?>" type="text" value="<?php echo esc_attr($output_fields); ?>">
            </p>
            <p>
                <label for="<?php echo $this->get_field_id('output_fields_resources'); ?>"><?php _e('Output fields for resources:', 'wpckan'); ?></label>
                <input class="widefat" id="<?php echo $this->get_field_id('output_fields_resources'); ?>" name="<?php echo $this->get_field_name('output_fields_resources'); ?>" type="text" value="<?php echo esc_attr($output_fields_resources); ?>">
            </p>
            <p>
                <label for="<?php echo $this->get_field_id('sort'); ?>"><?php _e('Order by:', 'wpckan'); ?></label>
                <select class='widefat' id="<?php echo $this->get_field_id('sort'); ?>" name="<?php echo $this->get_field_name('sort'); ?>" type="text">
                    <?php foreach ($this->sort_options  as $key => $value) : ?>
                        <option <?php if ($sort == $key) {
                                    echo " selected";
                                } ?> value="<?php echo $key ?>"><?php echo $value ?></option>
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
        $instance                               = [];
        $instance['title']                      = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
        $instance['query']                      = (!empty($new_instance['query'])) ? strip_tags($new_instance['query']) : '';
        $instance['organization']               = (!empty($new_instance['organization'])) ? strip_tags($new_instance['organization']) : '';
        $instance['group']                      = (!empty($new_instance['group'])) ? strip_tags($new_instance['group']) : '';
        $instance['filter_fields']              = (!empty($new_instance['filter_fields'])) ? strip_tags($new_instance['filter_fields']) : '';
        $instance['type']                       = (!empty($new_instance['type'])) ? strip_tags($new_instance['type']) : 'dataset';
        $instance['limit']                      = (!empty($new_instance['limit'])) ? $new_instance['limit'] : 0;
        $instance['more_text']                  = (!empty($new_instance['more_text'])) ? $new_instance['more_text'] : 0;
        $instance['more_link']                  = (!empty($new_instance['more_link'])) ? $new_instance['more_link'] : 0;
        
        $instance['output_fields']              = (!empty($new_instance['output_fields'])) ? strip_tags($new_instance['output_fields']) : 'title';
        $instance['output_fields']              = wpckan_remove_whitespaces($instance['output_fields']);
        
        $instance['output_fields_resources']    = (!empty($new_instance['output_fields_resources'])) ? strip_tags($new_instance['output_fields_resources']) : '';
        $instance['output_fields_resources']    = wpckan_remove_whitespaces($instance['output_fields_resources']);
        
        $instance['sort']                       = (!empty($new_instance['sort'])) ? strip_tags($new_instance['sort']) : 'metadata_modified+desc';
        $instance['template']                   = (!empty($new_instance['template'])) ? $new_instance['template'] : 'dataset-list';

        return $instance;
    }
}

add_action('widgets_init', function () {
    register_widget("Wpckan_Query_Resources_Widget");
});