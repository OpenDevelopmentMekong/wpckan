<?php if (is_null($data)) die(); ?>

<?php
  $include_fields_dataset = array();
  $include_fields_resources = array();
  if (array_key_exists("include_fields_resources",$atts))
    $include_fields_dataset = explode(",",$atts["include_fields_dataset"]);
  array_push($include_fields_dataset,"title"); //ensure that this field is present
  array_push($include_fields_dataset,"notes"); //ensure that this field is present
  if (array_key_exists("include_fields_resources",$atts))
    $include_fields_resources = explode(",",$atts["include_fields_resources"]);
  array_push($include_fields_resources,"name"); //ensure that this field is present
  array_push($include_fields_resources,"description"); //ensure that this field is present
?>

<div class="wpckan_dataset_list">
  <ul>
  <?php foreach ($data as $dataset){ ?>
    <li>
      <div class="wpckan_dataset">
        <?php if (array_key_exists("title",$dataset) && !IsNullOrEmptyString($dataset["title"]) && in_array("title",$include_fields_dataset)) {?>
          <div class="wpckan_dataset_title"><?php echo $dataset["title"] ?></div>
        <?php } ?>
        <?php if (array_key_exists("notes",$dataset) && !IsNullOrEmptyString($dataset["notes"]) && in_array("notes",$include_fields_dataset)) {?>
          <div class="wpckan_dataset_notes"><?php echo $dataset["notes"] ?></div>
        <?php } ?>
        <?php if (array_key_exists("license",$dataset) && !IsNullOrEmptyString($dataset["license"]) && in_array("license",$include_fields_dataset)) {?>
          <div class="wpckan_dataset_license"><?php echo $dataset["license"] ?></div>
        <?php } ?>
        <?php if (array_key_exists("license_url",$dataset) && !IsNullOrEmptyString($dataset["license_url"]) && in_array("license_url",$include_fields_dataset)) {?>
          <div class="wpckan_dataset_license_url"><?php echo $dataset["license_url"] ?></div>
        <?php } ?>
        <?php if (array_key_exists("metadata_created",$dataset) && !IsNullOrEmptyString($dataset["metadata_created"]) && in_array("metadata_created",$include_fields_dataset)) {?>
          <div class="wpckan_dataset_metadata_created"><?php echo $dataset["metadata_created"] ?></div>
        <?php } ?>
        <?php if (array_key_exists("metadata_modified",$dataset) && !IsNullOrEmptyString($dataset["metadata_modified"]) && in_array("metadata_modified",$include_fields_dataset)) {?>
          <div class="wpckan_dataset_metadata_modified"><?php echo $dataset["metadata_modified"] ?></div>
        <?php } ?>
        <?php if (array_key_exists("author",$dataset) && !IsNullOrEmptyString($dataset["author"]) && in_array("author",$include_fields_dataset)) {?>
          <div class="wpckan_dataset_author"><?php echo $dataset["author"] ?></div>
        <?php } ?>
        <?php if (array_key_exists("author_email",$dataset) && !IsNullOrEmptyString($dataset["author_email"]) && in_array("author_email",$include_fields_dataset)) {?>
          <div class="wpckan_dataset_author_email"><?php echo $dataset["author_email"] ?></div>
        <?php } ?>
        <?php if (array_key_exists("resources",$dataset)){ ?>
          <div class="wpckan_resources_list">
            <ul>
              <?php foreach ($dataset["resources"] as $resource){ ?>
                <li>
                  <div class="wpckan_resource">
                    <?php if (array_key_exists("name",$resource) && !IsNullOrEmptyString($resource["name"]) && in_array("name",$include_fields_resources)) {?>
                      <div class="wpckan_resource_name"><?php echo $resource["name"] ?></div>
                      <?php } ?>
                      <?php if (array_key_exists("description",$resource) && !IsNullOrEmptyString($resource["description"]) && in_array("description",$include_fields_resources)) {?>
                        <div class="wpckan_resource_description"><?php echo $resource["description"] ?></div>
                      <?php } ?>
                      <?php if (array_key_exists("revision_timestamp",$resource) && !IsNullOrEmptyString($resource["revision_timestamp"]) && in_array("revision_timestamp",$include_fields_resources)) {?>
                        <div class="wpckan_resource_revision_timestamp"><?php echo $resource["revision_timestamp"] ?></div>
                      <?php } ?>
                      <?php if (array_key_exists("format",$resource) && !IsNullOrEmptyString($resource["format"]) && in_array("format",$include_fields_resources)) {?>
                        <div class="wpckan_resource_format"><?php echo $resource["format"] ?></div>
                      <?php } ?>
                      <?php if (array_key_exists("created",$resource) && !IsNullOrEmptyString($resource["created"]) && in_array("created",$include_fields_resources)) {?>
                        <div class="wpckan_resource_created"><?php echo $resource["created"] ?></div>
                      <?php } ?>
                  </div>
                </li>
              <?php } ?>
            </ul>
          </div>
        <?php } ?>
      </div>
    </li>
  <?php } ?>
  </ul>
</div>
