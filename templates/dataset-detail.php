<?php if (is_null($data)) {
    die();
} ?>

<?php
    $supported_fields_csv = $GLOBALS['wpckan_options']->get_option('wpckan_setting_supported_fields');
    $supported_fields = explode(',', $supported_fields_csv);

    $multilingual_fields = array();
    $uses_ckanext_fluent = $GLOBALS['wpckan_options']->get_option('wpckan_setting_uses_ckanext_fluent');
    $current_language = 'en';
    if ($uses_ckanext_fluent && wpckan_is_qtranslate_available()):
        $multilingual_fields_csv = $GLOBALS['wpckan_options']->get_option('wpckan_setting_multilingual_fields');
        $multilingual_fields = explode(',', $multilingual_fields_csv);
        $current_language = qtranxf_getLanguage();
    endif;

    $field_mappings = wpckan_parse_field_mappings();

?>

<div class="wpckan_dataset_detail">

	<!-- Title or title_translated in case of multilingual dataset-->
	<?php
        $title = $data['title'];
        if ($uses_ckanext_fluent && array_key_exists('title_translated', $data)):
            if (array_key_exists($current_language, $data['title_translated'])):
                $title = !empty($data['title_translated'][$current_language]) ? $data['title_translated'][$current_language] : $data['title_translated']['en'];
            endif;
        endif;
    ?>
	<h1 class="wpckan_dataset_title"><?php echo $title ?></h1>

	<!-- Organization -->
  <?php if (isset($data['organization']['title']) && (odm_country_manager()->get_current_country()=="mekong")): ?>
    <h3 class="wpckan_dataset_organization"><?php echo $data['organization']['title'] ?></h3>
  <?php endif; ?>

	<!-- Tags -->
  <ul class="wpckan_dataset_tags">
    <?php foreach ($data['tags'] as $tag): ?>
      <li class="wpckan_dataset_tag"><?php echo $tag['display_name'] ?></li>
    <?php endforeach; ?>
  </ul>

	<!-- Notes or notes_translated in case of multilingual dataset -->
	<?php
        $notes = $data['notes'];
        if (array_key_exists('notes_translated', $data)):
            if (array_key_exists($current_language, $data['notes_translated'])):
              $notes = !empty($data['notes_translated'][$current_language]) ? $data['notes_translated'][$current_language] : $data['notes_translated']['en'];
            endif;
        endif;
    ?>
	<p class="wpckan_dataset_notes"><?php echo $notes ?></p>

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
          <?php if (isset($resource['name'])): ?>
            <h3><?php echo $resource['name']; ?></h3>
          <?php endif; ?>
          <?php if (isset($resource['description'])): ?>
            <p><?php echo $resource['description']; ?></p>
          <?php endif; ?>
        </td>
        <td class="wpckan_dataset_resource_url"><?php if (isset($resource['url'])): ?>
          <a class="wpckan_dataset_resource_url button download" href="<?php echo $resource['url']; ?>"><?php _e('Download', 'wpckan') ?></a>
        <?php endif; ?></td>
  		</tr>
    <?php endforeach; ?>
  </table>

	<!-- Metadata -->
	<h2><?php _e('Additional info', 'wpckan') ?></h2>
  <table class="wpckan_dataset_metadata_fields">
    <?php
        $metadata_available = false;
        if (!empty($supported_fields)): ?>
        <?php
          foreach ($supported_fields as $key): ?>
            <tr class="wpckan_dataset_metadata_field">
            <?php $mapped_key = isset($field_mappings[$key]) ? $field_mappings[$key] : $key;
            if (array_key_exists($key,$data) && isset($data[$key])):
              $value = $data[$key];
              if (is_array($value) && (!empty($value[$current_language]) || (!empty($value["en"]))) && !empty($value)):
                $value = !empty($value[$current_language]) ? $value[$current_language] : $value["en"];
                if (!empty($value)):
                  $metadata_available = true;
                  echo '<td><p>'.__($mapped_key).'</p></td>';
                  echo '<td><p>'.$value.'</p></td>';
                endif;
              else:
                $value = $data[$key];
                if (is_array($value)):
                  $value = implode(', ', $value);
                endif;
                if (!empty($value)):
                  $metadata_available = true;
                  echo '<td><p>'.__($mapped_key).'</p></td>';
                  echo '<td><p>'.$value.'</p></td>';
                endif;
              endif;
            endif; ?>
            </tr>
          <?php endforeach;
          if ($metadata_available == false):
            echo '<p>'.__('No metadata available for current dataset','wpckan').'</p>';
          endif;
        ?>

    <?php endif; ?>
  </table>

</div>
