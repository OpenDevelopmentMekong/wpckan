<?php

function wpckan_opendev_get_related_datasets($atts = false)
{
    if (!$atts) {
        $atts = array();
    }

    if (!isset($atts['post_id'])) {
        $atts['post_id'] = get_the_ID();
    }

    $related_datasets_json = get_post_meta($atts['post_id'], 'wpckan_related_datasets', true);
    $related_datasets = array();
    if (!IsNullOrEmptyString($related_datasets_json)) {
        $related_datasets = json_decode($related_datasets_json, true);
    }

    $dataset_array = array();

    foreach ($related_datasets as $dataset) {
        $dataset_atts = array('id' => $dataset['dataset_id']);
        try {
            array_push($dataset_array, wpckan_api_get_dataset($dataset_atts));
        } catch (Exception $e) {
            wpckan_log($e->getMessage());
        }
        if (array_key_exists('limit', $atts) && (count($dataset_array) >= (int) ($atts['limit']))) {
            break;
        }
    }

    return $dataset_array;
}

function wpckan_opendev_api_query_datasets($atts)
{
    if (is_null(wpckan_get_ckan_settings())) {
        wpckan_api_settings_error('wpckan_api_query_datasets');
    }

    if (!isset($atts['query'])) {
        wpckan_api_call_error('wpckan_api_query_datasets', null);
    }

    try {
        $settings = wpckan_get_ckan_settings();
        $ckanClient = CkanClient::factory($settings);
        $commandName = 'PackageSearch';
        $arguments = array('q' => $atts['query']);

        if (isset($atts['limit'])) {
            $arguments['rows'] = (int) $atts['limit'];

            if (isset($atts['page'])) {
                $page = (int) $atts['page'];
                if ($page > 0) {
                    $arguments['start'] = (int) $atts['limit'] * ($page - 1);
                }
            }
        }

        $filter = null;
        if (isset($atts['organization'])) {
            $filter = $filter.'+owner_org:'.$atts['organization'];
        }
        if (isset($atts['organization']) && isset($atts['group'])) {
            $filter = $filter.' ';
        }
        if (isset($atts['group'])) {
            $filter = $filter.'+groups:'.$atts['group'];
        }
        if (!is_null($filter)) {
            $arguments['fq'] = $filter;
        }
        $command = $ckanClient->getCommand($commandName, $arguments);
        $response = $command->execute();

        wpckan_log('wpckan_api_query_datasets commandName: '.$commandName.' arguments: '.print_r($arguments, true).' settings: '.print_r($settings, true));

        if ($response['success'] == false) {
            wpckan_api_call_error('wpckan_api_query_datasets', null);
        }
    } catch (Exception $e) {
        wpckan_api_call_error('wpckan_api_query_datasets', $e->getMessage());
    }

    return $response;
}

function wpckan_get_ckan_domain()
{
    return get_option('setting_ckan_url');
}
