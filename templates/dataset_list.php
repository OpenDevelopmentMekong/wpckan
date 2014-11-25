<?php if (!$dataset_array) die(); ?>

<ul>
<?php foreach ($dataset_array as $value){ ?>
  <li><?php echo $value["title"] ?></li>
<?php } ?>
</ul>
