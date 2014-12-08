<?php if (is_null($data)) die(); ?>
<ul>
<?php foreach ($data as $value){ ?>
  <li>
    <div class="wpckan_dataset">
      <!-- Title -->
      <h1><?php echo $value["title"] ?></h1>
      <!-- Notes -->
      <?php if (!IsNullOrEmptyString($value["notes"])) {?>
        <p><?php echo $value["notes"] ?></p>
        <?php } ?>
      <!-- License -->
      <?php if (!IsNullOrEmptyString($value["license_title"])) {?>
        <a href="<?php echo $value["license_url"] ?>"><?php echo $value["license_title"] ?></a>
      <?php } ?>
      <!-- Url -->
      <?php if (!IsNullOrEmptyString($value["url"])) {?>
        <a href="<?php echo $value["url"] ?>"><?php _e( 'Link to source', 'dataset_list_link_to_source' ) ?></a>
      <?php } ?>
      <!-- Author -->
      <?php if (!IsNullOrEmptyString($value["author"])) {?>
        <a href="<?php echo 'mailto://' . $value["author_email"] ?>"><?php echo $value["author"] ?></a>
      <?php } ?>
    </div>
  </li>
<?php } ?>
</ul>
