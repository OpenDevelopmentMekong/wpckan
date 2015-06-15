<?php if (is_null($data)) die(); ?>

<?php
  $include_fields_dataset = array();
  array_push($include_fields_dataset,"title"); //ensure that this field is present
  array_push($include_fields_dataset,"notes"); //ensure that this field is present
  $include_fields_resources = array();
  array_push($include_fields_resources,"name"); //ensure that this field is present
  array_push($include_fields_resources,"description"); //ensure that this field is present
  if (array_key_exists("include_fields_dataset",$atts))
    $include_fields_dataset = explode(",",$atts["include_fields_dataset"]);
  if (array_key_exists("include_fields_resources",$atts))
    $include_fields_resources = explode(",",$atts["include_fields_resources"]);
  if (array_key_exists("related_dataset",$atts)) $count = count($atts["related_dataset"]);
  if (array_key_exists("count",$atts)) $count = $atts["count"];
?>

<div class="wpckan_dataset_list">
  <ul>
  <?php foreach ($data as $dataset){ ?>
    <li>
      <div class="wpckan_dataset">
        <?php if (array_key_exists("title",$dataset) && !wpckan_is_null_or_empty_string($dataset["title"]) && in_array("title",$include_fields_dataset)) {?>
          <div class="wpckan_dataset_title"><a target="_blank" href="<?php echo wpckan_get_link_to_dataset($dataset["name"]) ?>"><?php echo $dataset["title"] ?></a></div>
        <?php } ?>
        <?php if (array_key_exists("notes",$dataset) && !wpckan_is_null_or_empty_string($dataset["notes"]) && in_array("notes",$include_fields_dataset)) {?>
          <div class="wpckan_dataset_notes"><?php echo $dataset["notes"] ?></div>
        <?php } ?>
        <?php if (array_key_exists("url",$dataset) && !wpckan_is_null_or_empty_string($dataset["url"]) && in_array("url",$include_fields_dataset)) {?>
          <div class="wpckan_dataset_url"><?php echo $dataset["url"] ?></div>
        <?php } ?>
        <?php if (array_key_exists("license",$dataset) && !wpckan_is_null_or_empty_string($dataset["license"]) && in_array("license",$include_fields_dataset)) {?>
          <div class="wpckan_dataset_license"><?php echo $dataset["license"] ?></div>
        <?php } ?>
        <?php if (array_key_exists("license_url",$dataset) && !wpckan_is_null_or_empty_string($dataset["license_url"]) && in_array("license_url",$include_fields_dataset)) {?>
          <div class="wpckan_dataset_license_url"><?php echo $dataset["license_url"] ?></div>
        <?php } ?>
        <?php if (array_key_exists("metadata_created",$dataset) && !wpckan_is_null_or_empty_string($dataset["metadata_created"]) && in_array("metadata_created",$include_fields_dataset)) {?>
          <div class="wpckan_dataset_metadata_created"><?php echo $dataset["metadata_created"] ?></div>
        <?php } ?>
        <?php if (array_key_exists("metadata_modified",$dataset) && !wpckan_is_null_or_empty_string($dataset["metadata_modified"]) && in_array("metadata_modified",$include_fields_dataset)) {?>
          <div class="wpckan_dataset_metadata_modified"><?php echo $dataset["metadata_modified"] ?></div>
        <?php } ?>
        <?php if (array_key_exists("author",$dataset) && !wpckan_is_null_or_empty_string($dataset["author"]) && in_array("author",$include_fields_dataset)) {?>
          <div class="wpckan_dataset_author"><?php echo $dataset["author"] ?></div>
        <?php } ?>
        <?php if (array_key_exists("author_email",$dataset) && !wpckan_is_null_or_empty_string($dataset["author_email"]) && in_array("author_email",$include_fields_dataset)) {?>
          <div class="wpckan_dataset_author_email"><?php echo $dataset["author_email"] ?></div>
        <?php } ?>
        <?php if (array_key_exists("resources",$dataset)){ ?>
          <div class="wpckan_resources_list">
            <ul>
              <?php foreach ($dataset["resources"] as $resource){ ?>
                <li>
                  <div class="wpckan_resource">
                    <?php if (array_key_exists("name",$resource) && !wpckan_is_null_or_empty_string($resource["name"]) && in_array("name",$include_fields_resources)) {?>
                      <div class="wpckan_resource_name"><a target="_blank" href="<?php echo wpckan_get_link_to_resource($dataset["name"],$resource["id"]) ?>"><?php echo $resource["name"] ?></a></div>
                      <?php } ?>
                      <?php if (array_key_exists("description",$resource) && !wpckan_is_null_or_empty_string($resource["description"]) && in_array("description",$include_fields_resources)) {?>
                        <div class="wpckan_resource_description"><?php echo $resource["description"] ?></div>
                      <?php } ?>
                      <?php if (array_key_exists("revision_timestamp",$resource) && !wpckan_is_null_or_empty_string($resource["revision_timestamp"]) && in_array("revision_timestamp",$include_fields_resources)) {?>
                        <div class="wpckan_resource_revision_timestamp"><?php echo $resource["revision_timestamp"] ?></div>
                      <?php } ?>
                      <?php if (array_key_exists("format",$resource) && !wpckan_is_null_or_empty_string($resource["format"]) && in_array("format",$include_fields_resources)) {?>
                        <div class="wpckan_resource_format"><?php echo $resource["format"] ?></div>
                      <?php } ?>
                      <?php if (array_key_exists("created",$resource) && !wpckan_is_null_or_empty_string($resource["created"]) && in_array("created",$include_fields_resources)) {?>
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
<div class="wpckan_dataset_list_pagination">
  <?php
    if (array_key_exists("limit",$atts) && array_key_exists("page",$atts) && array_key_exists("prev_page_link",$atts)){
      $prev_page_title = "Previous";
      if (array_key_exists("prev_page_title",$atts)) $prev_page_title = $atts["prev_page_title"];
      if (!wpckan_pagination_first($atts["page"])){
        echo ("<a href=\"" . $atts["prev_page_link"] . "\">" . $prev_page_title . "</a>");
      }
    }
  ?>
  <?php
  if (array_key_exists("limit",$atts) && array_key_exists("page",$atts) && array_key_exists("next_page_link",$atts)){
    $next_page_title = "Next";
    if (array_key_exists("next_page_title",$atts)) $next_page_title = $atts["next_page_title"];
    if (!wpckan_pagination_last($count,$atts["limit"],$atts["page"])){
      echo ("<a href=\"" . $atts["next_page_link"] . "\">" . $next_page_title . "</a>");
    }
  }
  ?>
</div>
