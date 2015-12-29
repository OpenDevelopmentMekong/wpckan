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

// field extras
  $include_fields_extra = array();
  if (array_key_exists("include_fields_extra",$atts))
    $include_fields_extra = explode(",",$atts["include_fields_extra"]);
  // insert type
  $dataset_count=count($data);?>
{
    "wpckan_dataset_list":{
      <?php foreach ($data as $index_data => $dataset){ ?>
        <?php $resource_count=count($dataset["resources"]);?>
        <!-- set variables -->
        <?php
          // wpckan_dataset_list
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
          <!-- set json -->
          "<?php echo $title;?>":{
            <?php if (array_key_exists("title",$dataset) && !wpckan_is_null_or_empty_string($dataset["title"]) && in_array("title",$include_fields_dataset)) {?>
              "wpckan_dataset_title_url":"<?php echo $title_url;?>",
            <?php }?>
            "wpckan_dataset_notes":"<?php #echo $notes;?>",
            "wpckan_dataset_url":"<?php echo $url;?>",
            "wpckan_dataset_license":"<?php #echo $license;?>",
            "wpckan_dataset_license_url":"<?php echo $license_url;?>",
            "wpckan_dataset_metadata_created":"<?php echo $metadata_created;?>",
            "wpckan_dataset_metadata_modified":"<?php echo $metadata_modified;?>",
            "wpckan_dataset_author":"<?php echo $author;?>",
            "wpckan_dataset_author_email":"<?php echo $author_email;?>",
            "wpckan_resources_list":{
              <?php foreach ($dataset["resources"] as $index =>$resource){
                //  wpckan_resources_list
                $resource_name=$resource["name"];
                $resource_name_link=wpckan_get_link_to_resource($dataset["name"],$resource["id"]);
                $resource_description=$resource["description"];
                if (array_key_exists("revision_timestamp",$resource) && !wpckan_is_null_or_empty_string($resource["revision_timestamp"]) && in_array("revision_timestamp",$include_fields_resources)) {
                $resource_revision_timestamp=$resource["revision_timestamp"];
                }
                $resource_format=$resource["format"];
                $resource_created=$resource["created"];

                $resource_language=$resource["odm_language"][0];

                ?>
                "<?php echo $index;?>":{
                    "wpckan_resource_name":"<?php echo $resource_name;?>",
                    "wpckan_resource_name_link":"<?php echo $resource_name_link;?>",
                    "wpckan_resource_description":"",
                    <?php if (array_key_exists("revision_timestamp",$resource) && !wpckan_is_null_or_empty_string($resource["revision_timestamp"]) && in_array("revision_timestamp",$include_fields_resources)) {?>
                      "wpckan_resource_revision_timestamp":"<?php echo $resource_revision_timestamp;?>",
                    <?php } ?>
                    "wpckan_resource_format":"<?php echo $resource_format;?>",
                    "wpckan_resource_created":"<?php echo $resource_created;?>",

                    "wpckan_resource_language":"<?php echo $resource_language;?>"

                  }<?php if ($index == $resource_count - 1) { echo "";} else {echo ",";}?>

                <?php } ?>
              },

            "wpckan_dataset_extras":{
              <?php foreach ($include_fields_extra as $index_extra => $extra) {?>
                <?php $extra_count=count($include_fields_extra);?>
                "wpkan_dataset_extras-<?php echo $extra;?>":"<?php echo $dataset[$extra];?>"<?php if ($index_extra == $extra_count - 1) { echo "";} else {echo ",";}?>
              <?php } ?>
            }
          }<?php if ($index_data == $dataset_count - 1) { echo "";} else {echo ",";}?>

        <?php } ?>
      }
    }
