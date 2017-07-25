<?php if (is_null($data)) die(); ?>

<?php
  if (array_key_exists("related_dataset",$atts)):
    $count = count($atts["related_dataset"]);
  endif;
  if (array_key_exists("count",$atts)):
    $count = $atts["count"];
  endif;

  $target_blank_enabled = $GLOBALS['wpckan_options']->get_option('wpckan_setting_target_blank_enabled');
  $current_language = wpckan_get_current_language();

?>

<div class="wpckan_dataset_list">
  <ul>
  <?php foreach ($data as $dataset): ?>
    <li>
      <div class="wpckan_dataset four columns post-grid-item">
				<div class="grid-content-wrapper">
					<div class="meta">
						<?php
							$localized_title = wpckan_get_multilingual_value("title",$dataset);
							$date = $dataset["metadata_created"];
							$link = wpckan_get_link_to_dataset($dataset["id"]); ?>
							<a class="item-title" href="<?php echo $link; ?>" title="<?php echo $localized_title; ?>" data-ga-event="Dataset|link_click|<?php echo $dataset["id"]; ?>">
								<?php echo $localized_title; ?>
							</a>
	      	</div>
					<?php
						$image_urls = wpckan_get_image_urls_from_dataset($dataset);
						if (!empty($image_urls)):
							$first_url = $image_urls[0]; ?>
							<img class="attachment-post-thumbnail size-post-thumbnail wp-post-image" src="<?php echo $first_url; ?>" />';
					<?php
						endif;
					 ?>
				</div>
      </div>
    </li>
  <?php
	endforeach; ?>
  </ul>
</div>
