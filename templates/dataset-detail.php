<?php if (is_null($data)) die(); ?>

<?php
  print_r($data);
?>


<!--
  TODO: - multilingual support (both gettext and fluent)
        - complete layout
        - figure out way of linking
        -
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
	<h2>Resources</h2>
	<ul class="wpckan_dataset_resources">
    <li class="wpckan_dataset_resource">
			<img src="#"></img><a href="#">Resource1</a>
		</li>
		<li class="wpckan_dataset_resource">
			<img src="#"></img><a href="#">Resource2</a>
		</li>
  </ul>
  <!-- Metadata -->
	<h2>Additional info</h2>
	<table class="wpckan_dataset_metadata_fields">
    <tr class="wpckan_dataset_metadata_field">
			<td>field name</td>
			<td>field value</td>
		</tr>
  </table>
</div>
<div class="wpckan_dataset_list_pagination">
<a href="#">Previous</a>
<a href="#">Next</a>
</div>
