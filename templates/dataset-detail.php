<?php if (is_null($data)) {
    die();
} ?>

<?php
  $supported_fields_csv = $GLOBALS['wpckan_options']->get_option('wpckan_setting_supported_fields');
  $supported_fields = explode(',', $supported_fields_csv);
	$supported_fields_additional_csv = $GLOBALS['wpckan_options']->get_option('wpckan_setting_supported_fields_additional');
  $supported_fields_additional = explode(',', $supported_fields_additional_csv);
	$current_language = wpckan_get_current_language(); ?>

<div class="wpckan_dataset_detail">

	<!-- Title or title_translated in case of multilingual dataset-->
	<?php
    $title = wpckan_get_multilingual_value('title',$data);?>
	<h1 class="wpckan_dataset_title"><?php echo $title ?></h1>

	<!-- Organization -->
  <?php if (isset($data['organization']['title']) && function_exists("odm_country_manager") && (odm_country_manager()->get_current_country()=="mekong")): ?>
    <h3 class="wpckan_dataset_organization"><?php _e($data['organization']['title'], 'wpckan') ?></h3>
  <?php endif; ?>$linked_fields

	<!-- Tags -->
  <ul class="wpckan_dataset_tags">
    <?php foreach ($data['tags'] as $tag): ?>
      <li class="wpckan_dataset_tag"><?php echo apply_filters('translate_term', $tag['display_name'], $current_language); ?></li>
    <?php endforeach; ?>
  </ul>

	<!-- Notes or notes_translated in case of multilingual dataset -->
	<?php
    $notes = wpckan_get_multilingual_value('notes',$data);?>
	  <p class="wpckan_dataset_notes expandible"><?php echo $notes ?></p>

	<!-- License -->
  <?php if (isset($data['license_title'])): ?>
    <a href="<?php echo $data['license_url'] ?>" class="wpckan_dataset_license"><?php echo $data['license_title'] ?></a>
  <?php endif; ?>

  <!-- Resources -->
	<h2><?php _e('Resources', 'wpckan') ?></h2>
	<table class="wpckan_dataset_resources">
    <?php foreach ($data['resources'] as $resource): ?>
  		<tr class="wpckan_dataset_resource">
  			<td class="wpckan_dataset_resource_format" format="<?php echo $resource['format']; ?>"><?php if (isset($resource['format'])): ?>
          <p format="<?php echo $resource['format']; ?>"><?php echo $resource['format']; ?></p>
        <?php endif; ?></td>
        <td class="wpckan_dataset_resource_name">
					<?php
					$resource_title = $resource['name'];
					if (array_key_exists('name_translated', $resource)):
	            if (array_key_exists($current_language, $resource['name_translated'])):
	                $resource_title = !empty($resource['name_translated'][$current_language]) ? $resource['name_translated'][$current_language] : $resource['name_translated']['en'];
	            endif;
	        endif; ?>
          <h3><?php echo $resource_title; ?></h3>
					<?php
					$resource_description = $resource['description'];
					if (array_key_exists('description_translated', $resource)):
	            if (array_key_exists($current_language, $resource['description_translated'])):
	                $resource_description = !empty($resource['description_translated'][$current_language]) ? $resource['description_translated'][$current_language] : $resource['description_translated']['en'];
	            endif;
	        endif; ?>
          <p class="expandible"><?php echo $resource_description; ?></p>
        </td>
        <td class="wpckan_dataset_resource_url"><?php if (isset($resource['url'])): ?>
          <a class="wpckan_dataset_resource_url button download" href="<?php echo $resource['url']; ?>"><?php _e('Download', 'wpckan') ?></a>
        <?php endif; ?></td>
  		</tr>
    <?php endforeach; ?>
  </table>

	<!-- Metadata -->
	<h2><?php _e('Metadata', 'wpckan') ?></h2>
	<?php
		if (!empty($supported_fields)): ?>
	    <?php
				render_metadata_table($supported_fields,$data); ?>
	<?php
		endif; ?>

	<?php
		if (!empty($supported_fields_additional)): ?>
			<div class="slideable">
				<h5><?php _e('View additional metadata', 'wpckan') ?></h5>
				<div class="slideable-content">
			    <?php
						render_metadata_table($supported_fields_additional,$data); ?>
				</div>
			</div>
	<?php
		endif; ?>

</div>

<?php
function render_metadata_table($supported_fields,$data){

	$field_mappings = wpckan_parse_field_mappings('wpckan_setting_field_mappings');
  $field_mappings_values = wpckan_parse_field_mappings('wpckan_setting_field_mappings_values');
	$supported_datatables = wpckan_parse_field_mappings('wpckan_setting_supported_datatables');
	$linked_fields_csv = $GLOBALS['wpckan_options']->get_option('wpckan_setting_linked_fields');
  $linked_fields = explode(',', $linked_fields_csv);
	$current_language = wpckan_get_current_language(); ?>

	<table class="wpckan_dataset_metadata_fields">
    <?php
        $metadata_available = false;
        if (!empty($supported_fields)): ?>
        <?php
          foreach ($supported_fields as $key): ?>
            <tr class="wpckan_dataset_metadata_field">
            <?php
						$mapped_key = isset($field_mappings[$key]) ? trim($field_mappings[$key]," ") : $key;
            $mapped_value = "";
            if (array_key_exists($key,$data) && isset($data[$key])):
              $value = $data[$key];

							if (array_key_exists($key, $supported_datatables) && !empty($supported_datatables[$key])):
								$resource_id = $supported_datatables[$key];
								$ids = is_array($value) ? $value : explode(',', $value);
								if (count($ids) > 0):
                  $metadata_available = true;
									foreach($ids as $id):
										$results = wpckan_get_datastore_resources_filter(wpckan_get_ckan_domain(),$resource_id,"id",$id);
										$result = $results[0];
                    $mapped_value = $mapped_value . $result["name"];
										if ($id !== end($ids)):
							        $mapped_value = $mapped_value . ", ";
										endif;
									endforeach;
								endif;
							elseif (is_array($value) && array_key_exists($current_language, $value) && !empty($value)):
                $value = !empty($value[$current_language]) ? $value[$current_language] : $value["en"];
                $mapped_value = isset($field_mappings_values[$value]) ? $field_mappings_values[$value] : $value;
                if (!empty($mapped_value)):
                  $metadata_available = true;
                endif;
              else:
                $value = $data[$key];
                if (is_array($value)):
                  $value = implode(', ', $value);
                endif;
                if (!empty($value)):
                  $mapped_value = isset($field_mappings_values[$value]) ? $field_mappings_values[$value] : $value;
                  $metadata_available = true;
                endif;
              endif;
            endif; ?>

						<?php
						 	if (wpckan_is_date($mapped_value)):
								$parsed_date = date_parse($mapped_value);
                $monthName = date('M', mktime(0, 0, 0, $parsed_date["month"], 10));
								$mapped_value =  $parsed_date["day"] . " " . $monthName . " " . $parsed_date["year"];
							endif;
						?>

            <?php
            if (!empty($mapped_value)):
              if (in_array($key,$linked_fields)):
                echo '<td><p>'.__($mapped_key, 'wpckan').'</p></td>';
                echo '<td><p class="expandible"><a target="_blank" href="' . wpckan_get_link_to_dataset($mapped_value) . '"</a>' . $mapped_value .'</p></td>';
              else:
                echo '<td><p>'.__($mapped_key, 'wpckan').'</p></td>';
                echo '<td><p class="expandible">'.__($mapped_value, 'wpckan').'</p></td>';
              endif;
            endif;
            ?>

            </tr>
          <?php endforeach;

          if ($metadata_available == false):
            echo '<p>'.__('No metadata available for current dataset','wpckan').'</p>';
          endif;
        ?>

    <?php endif; ?>
  </table>
<?php
}

?>
