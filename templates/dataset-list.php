<?php if (is_null($data)) die(); ?>

<?php
  $include_fields_dataset = array();
  $include_fields_resources = array();
  if (array_key_exists("include_fields_dataset",$atts) && !empty($atts["include_fields_dataset"])):
    $include_fields_dataset = explode(",",$atts["include_fields_dataset"]);
  endif;
  if (array_key_exists("include_fields_resources",$atts) && !empty($atts["include_fields_resources"])):
    $include_fields_resources = explode(",",$atts["include_fields_resources"]);
  endif;
  if (array_key_exists("related_dataset",$atts)):
    $count = count($atts["related_dataset"]);
  endif;
  if (array_key_exists("count",$atts)):
    $count = $atts["count"];
  endif;

  $include_fields_extra = array();
  if (array_key_exists("include_fields_extra",$atts)):
    $include_fields_extra = explode(",",$atts["include_fields_extra"]);
  endif;

  $target_blank_enabled = $GLOBALS['wpckan_options']->get_option('wpckan_setting_target_blank_enabled');
  $uses_ckanext_fluent = $GLOBALS['wpckan_options']->get_option('wpckan_setting_uses_ckanext_fluent');
  $current_language = 'en';
  if ($uses_ckanext_fluent && wpckan_is_qtranslate_available()):
    $current_language = qtranxf_getLanguage();
  endif;

?>

<div class="wpckan_dataset_list">
  <ul>
  <?php foreach ($data as $dataset){ ?>
    <li>
      <div class="wpckan_dataset">
        <?php if ($uses_ckanext_fluent && array_key_exists("title_translated",$dataset) && !wpckan_is_null_or_empty_string($dataset["title_translated"]) && in_array("title_translated",$include_fields_dataset)) {
          $title = !empty($dataset["title_translated"][$current_language]) ? $dataset["title_translated"][$current_language] : $dataset["title_translated"]["en"]; ?>
          <div class="wpckan_dataset_title"><a <?php if ($target_blank_enabled){ echo 'target="_blank"';} ?>  href="<?php echo wpckan_get_link_to_dataset($dataset["name"]) ?>"><?php echo $title; ?></a></div>
        <?php } elseif (array_key_exists("title",$dataset) && !wpckan_is_null_or_empty_string($dataset["title"]) && in_array("title",$include_fields_dataset)) {?>
          <div class="wpckan_dataset_title"><a <?php if ($target_blank_enabled){ echo 'target="_blank"';} ?>  href="<?php echo wpckan_get_link_to_dataset($dataset["name"]) ?>"><?php echo $dataset["title"] ?></a></div>
        <?php } ?>
        <?php if ($uses_ckanext_fluent && array_key_exists("notes_translated",$dataset) && !wpckan_is_null_or_empty_string($dataset["notes_translated"]) && in_array("notes_translated",$include_fields_dataset)) {
          $notes = !empty($dataset["notes_translated"][$current_language]) ? $dataset["notes_translated"][$current_language] : $dataset["notes_translated"]["en"]; ?>
          <div class="wpckan_dataset_notes"><?php echo $notes; ?></div>
        <?php } ?>
        <?php if (array_key_exists("notes",$dataset) && !wpckan_is_null_or_empty_string($dataset["notes"]) && in_array("notes",$include_fields_dataset)) { ?>
          <div class="wpckan_dataset_notes"><?php echo $dataset["notes"] ?></div>
        <?php } ?>
        <?php if (array_key_exists("url",$dataset) && !wpckan_is_null_or_empty_string($dataset["url"]) && in_array("url",$include_fields_dataset)) {?>
          <div class="wpckan_dataset_url"><?php echo $dataset["url"] ?></div>
        <?php } ?>
        <?php if (array_key_exists("license_id",$dataset) && !wpckan_is_null_or_empty_string($dataset["license_id"]) && in_array("license_id",$include_fields_dataset)) {?>
          <div class="wpckan_dataset_license"><?php echo $dataset["license_id"] ?></div>
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
        <?php if (array_key_exists("resources",$dataset) && !empty($include_fields_resources)){ ?>
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
            <?php if (array_key_exists("include_fields_extra",$atts))?>
              <div class="wpckan_dataset_extras">
                <ul>
                  <?php foreach ($include_fields_extra as $extra) {
                    if (array_key_exists($extra,$dataset) && !wpckan_is_null_or_empty_string($dataset[$extra]) && in_array($extra,$include_fields_extra)) {?>
                      <li class="wpckan_dataset_extras-<?php echo $extra;?>"><?php echo $dataset[$extra];?></li>
                    <?php } ?>
                <?php } ?>
                </ul>
              </div>
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
