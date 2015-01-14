<?php if (is_null($data)) die(); ?>

<?php
  $prefix = "";
  $suffix = "";
  if (array_key_exists("prefix",$atts))
    $prefix = $atts["prefix"];
  if (array_key_exists("suffix",$atts))
    $suffix = $atts["suffix"];
?>

<div class="wpckan_dataset_number">
  <p><a <?php if (array_key_exists("link_url",$atts)){ ?>target="_blank" href=<?php echo $atts["link_url"] ?><?php } ?>><?php echo $prefix ?><?php echo count($data) ?><?php echo $suffix ?></a></p>
</div>
