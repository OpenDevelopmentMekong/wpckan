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
  <p><?php echo $prefix ?><a href="#"><?php echo count($data) ?></a><?php echo $suffix ?></p>
</div>
