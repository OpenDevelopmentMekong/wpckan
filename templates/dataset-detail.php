<?php if (is_null($data)) die(); ?>

<?php
  //print_r($data);
	$supported_fields_csv = get_option('wpckan_setting_supported_fields');
	$supported_fields = explode(",", $supported_fields_csv);
?>


<!--
  TODO: - multilingual support (both gettext and fluent)
        - complete layout
        - figure out way of linking (specify redirection on config)
-->

<div class="wpckan_dataset_detail">

	<!-- Title -->
	<h1 class="wpckan_dataset_title"><?php echo $data['title'] ?></h1>

	<!-- Organization -->
  <?php if (isset($data['organization']['title'])): ?>
    <a href="#" class="wpckan_dataset_organization"><?php echo $data['organization']['title'] ?></a>
  <?php endif; ?>

	<!-- Tags -->
  <?php foreach($data['tags'] as $tag): ?>
    <ul class="wpckan_dataset_tags">
      <li class="wpckan_dataset_tag"><a href="#"><?php echo $tag['display_name'] ?></a></li>
    </ul>
  <?php endforeach; ?>

	<!-- Description -->
	<p class="wpckan_dataset_notes"><?php echo $data['notes'] ?></p>

	<!-- License -->
  <?php if (isset($data['license_title'])): ?>
    <a href="<?php echo $data['license_url'] ?>" class="wpckan_dataset_license"><?php echo $data['license_title'] ?></a>
  <?php endif; ?>

	<!-- Resources -->
	<?php if (isset($data['resources'])): ?>
		<h2>Resources</h2>
		<?php foreach($data['resources'] as $resource): ?>
	    <ul class="wpckan_dataset_resources">
	      <li class="wpckan_dataset_resource">
					<?php if (isset($resource['format'])): ?>
						<p class="wpckan_dataset_resource_format"><?php echo $resource['format']; ?></p>
					<?php endif; ?>
					<?php if (isset($resource['name'])): ?>
						<h3 class="wpckan_dataset_resource_name"><?php echo $resource['name']; ?></h3>
					<?php endif; ?>
					<?php if (isset($resource['description'])): ?>
						<p class="wpckan_dataset_resource_description"><?php echo $resource['description']; ?></p>
					<?php endif; ?>
					<?php if (isset($resource['url'])): ?>
						<a class="wpckan_dataset_resource_url" href="<?php echo $resource['url']; ?>"><?php _e('Download','wpckan') ?></a>
					<?php endif; ?>
				</li>
	    </ul>
	  <?php endforeach; ?>
	<?php endif; ?>

	<!-- Metadata -->
	<h2>Additional info</h2>
	<table class="wpckan_dataset_metadata_fields">
		<?php foreach($data as $key => $value): ?>
			<?php if (!empty($supported_fields) && in_array($key,$supported_fields)): ?>
				<tr class="wpckan_dataset_metadata_field">
					<td><?php echo $key ?></td>
					<td><?php
							if (is_array($value)):
								$value = implode(", ",$value);
							endif;
							echo $value ?>
					</td>
				</tr>
			<?php endif; ?>
	  <?php endforeach; ?>
  </table>

</div>
