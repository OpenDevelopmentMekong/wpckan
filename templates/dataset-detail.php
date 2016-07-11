<?php if (is_null($data)) die(); ?>

<?php
	$supported_fields_csv = $GLOBALS['wpckan_options']->get_option('wpckan_setting_supported_fields');
	$supported_fields = explode(",", $supported_fields_csv);

	$multilingual_fields = array();
	$current_language = 'en';
	if (wpckan_is_qtranslate_available()):
		$multilingual_fields_csv = $GLOBALS['wpckan_options']->get_option('wpckan_setting_multilingual_fields');
		$multilingual_fields = explode(",", $multilingual_fields_csv);
		$current_language = qtranxf_getLanguage();
	endif;

	$field_mappings = wpckan_parse_field_mappings();
?>

<div class="wpckan_dataset_detail">

	<!-- Title or title_translated in case of multilingual dataset-->
	<?php
		$title = $data['title'];
		if (array_key_exists('title_translated',$data)):
			if (array_key_exists($current_language,$data['title_translated'])):
				$title = $data['title_translated'][$current_language];
			endif;
		endif;
	?>
	<h1 class="wpckan_dataset_title"><?php echo $title ?></h1>

	<!-- Organization -->
  <?php if (isset($data['organization']['title'])): ?>
    <h3 class="wpckan_dataset_organization"><?php echo $data['organization']['title'] ?></h3>
  <?php endif; ?>

	<!-- Tags -->
  <?php foreach($data['tags'] as $tag): ?>
    <ul class="wpckan_dataset_tags">
      <li class="wpckan_dataset_tag"><?php echo $tag['display_name'] ?></li>
    </ul>
  <?php endforeach; ?>

	<!-- Notes or notes_translated in case of multilingual dataset -->
	<?php
		$notes = $data['notes'];
		if (array_key_exists('notes_translated',$data)):
			if (array_key_exists($current_language,$data['notes_translated'])):
				$notes = $data['notes_translated'][$current_language];
			endif;
		endif;
	?>
	<p class="wpckan_dataset_notes"><?php echo $notes ?></p>

	<!-- License -->
  <?php if (isset($data['license_title'])): ?>
    <a href="<?php echo $data['license_url'] ?>" class="wpckan_dataset_license"><?php echo $data['license_title'] ?></a>
  <?php endif; ?>

  <!-- Resources -->
	<h2><?php _e('Resources','wpckan') ?></h2>
	<table class="wpckan_dataset_resources">
    <?php foreach($data['resources'] as $resource): ?>
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
          <a class="wpckan_dataset_resource_url button" href="<?php echo $resource['url']; ?>"><?php _e('Download','wpckan') ?></a>
        <?php endif; ?></td>
  		</tr>
    <?php endforeach; ?>
  </table>

	<!-- Metadata -->
	<h2><?php _e('Additional info','wpckan') ?></h2>
	<table class="wpckan_dataset_metadata_fields">
		<?php foreach($data as $key => $value): ?>
			<?php if (!empty($supported_fields) && in_array($key,$supported_fields)): ?>
				<tr class="wpckan_dataset_metadata_field">
					<?php
						$key = isset($field_mappings[$key]) ? $field_mappings[$key] : $key;
						if (in_array($key,$multilingual_fields)):
							if (is_array($value) &&  array_key_exists($current_language,$value)):
								$value = $value[$current_language];
								echo "<td><p>" . __($key) . "</p></td>";
								echo "<td><p>" . $value . "</p></td>";
							endif;
						else:
							if (is_array($value)):
								$value = implode(", ",$value);
							endif;
							echo "<td><p>" . __($key) . "</p></td>";
							echo "<td><p>" . $value . "</p></td>";
						endif; ?>
				</tr>
			<?php endif; ?>
	  <?php endforeach; ?>
  </table>

</div>
