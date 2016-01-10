<?php if (is_null($data)) die(); ?>
<?php
  // disable error reporting as it breaks json structure
  error_reporting(0);
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
  // field extras
  $include_fields_extra = array();
  if (array_key_exists("include_fields_extra",$atts))
    $include_fields_extra = explode(",",$atts["include_fields_extra"]);
  // insert type
  $dataset_count=count($data);?>
  {
    "wpckan_dataset_list": [
    <?php foreach ($data as $index_data => $dataset): ?>
      <?php $resource_count=count($dataset["resources"]);?>
      <?php $taxonomy_count=count($dataset["taxonomy"]);?>
      <?php
       $title=$dataset["title"];
       $title_url= wpckan_get_link_to_dataset($dataset["name"]);
       $notes=$dataset["notes"];
       $url=$dataset["url"];
       $license=$dataset["license_id"];
       $license_url=$dataset["license_url"];
       $metadata_created=$dataset["metadata_created"];
       $metadata_modified=$dataset["metadata_modified"];
       $author=$dataset["author"];
       $author_email=$dataset["author_email"];
      ?>
      {
      <?php if (array_key_exists("title",$dataset) && !wpckan_is_null_or_empty_string($dataset["title"]) && in_array("title",$include_fields_dataset)):?>
        "wpckan_dataset_title_url":"<?php echo $title_url;?>",
      <?php endif ?>
        "wpckan_dataset_title": "<?php echo $title;?>",
        "wpckan_dataset_notes":<?php echo json_encode($notes);?>,
        "wpckan_dataset_url":"<?php echo $url;?>",
        "wpckan_dataset_license":"<?php echo $license;?>",
        "wpckan_dataset_license_url":"<?php echo $license_url;?>",
        "wpckan_dataset_metadata_created":"<?php echo $metadata_created;?>",
        "wpckan_dataset_metadata_modified":"<?php echo $metadata_modified;?>",
        "wpckan_dataset_author":"<?php echo $author;?>",
        "wpckan_dataset_author_email":"<?php echo $author_email;?>",
        "wpckan_dataset_taxomomy":[
        <?php foreach ($dataset['taxonomy'] as $index => $taxonomy): ?>
          "<?php echo $taxonomy;?>"
          <?php if ($index == $taxonomy_count - 1) { echo "";} else {echo ",";}?>
        <?php endforeach; ?>
        ],
        "wpckan_resources_list":[
          <?php foreach ($dataset["resources"] as $index =>$resource):
            echo json_encode($resource);
          ?>
          <?php if ($index == $resource_count - 1) { echo "";} else {echo ",";}?>
          <?php endforeach; ?>
        ],
        "wpckan_dataset_extras":{
          <?php $extra_count=count($include_fields_extra);?>
          <?php foreach ($include_fields_extra as $index_extra => $extra): ?>
            "wpckan_dataset_extras-<?php echo $extra;?>":<?php echo json_encode($dataset[$extra]);?>
          <?php if ($index_extra == $extra_count - 1) { echo "";} else {echo ",";}?>
          <?php endforeach; ?>
        }
      }
      <?php if ($index_data == $dataset_count - 1) { echo "";} else {echo ",";}?>
    <?php endforeach; ?>
    ]
  }
