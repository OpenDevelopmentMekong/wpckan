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
        <?php
          foreach ($include_fields_dataset as $field_name):
            $should_link_to_dataset = in_array($field_name, array("title")) ? true : false;
            if ($uses_ckanext_fluent && array_key_exists($field_name."_translated",$dataset) && !wpckan_is_null_or_empty_string($dataset[$field_name."_translated"])):
              $field_name = $field_name . "_translated";
            endif;
						if (isset($dataset[$field_name])):?>
	            <div class="wpckan_dataset_<?php echo($field_name); ?>">
	              <?php
                $to_print = $dataset[$field_name];
                if (wpckan_is_date($to_print)):
                  $to_print= wpckan_print_date($to_print);
                endif;
                if (is_array($dataset[$field_name])):
                  $to_print =  !empty($dataset[$field_name][$current_language]) ? $dataset[$field_name][$current_language] : $dataset[$field_name]["en"];
                endif;
								if ($should_link_to_dataset): ?>
                  <h5>
  	                <a <?php if ($target_blank_enabled){ echo 'target="_blank"';} ?>  href="<?php echo wpckan_get_link_to_dataset($dataset["name"]) ?>">
  										<?php echo $to_print; ?>
  									</a>
                  </h5>
	              <?php
	              else: ?>
									<?php echo $to_print; ?>
	              <?php
	              endif; ?>
	              </div>
          <?php
						endif;
          endforeach; ?>
        <?php if (array_key_exists("resources",$dataset) && !empty($include_fields_resources)){ ?>
          <div class="wpckan_resources_list">
            <ul>
              <?php foreach ($dataset["resources"] as $resource){ ?>
                <li>
                  <div class="wpckan_resource">
                    <?php
											foreach ($include_fields_resources as $field_name):
                        $should_link_to_dataset = in_array($field_name, array("name")) ? true : false;
                        if ($uses_ckanext_fluent && array_key_exists($field_name."_translated",$resource) && !wpckan_is_null_or_empty_string($resource[$field_name."_translated"])):
                          $field_name = $field_name . "_translated";
                        endif;
												if (isset($resource[$field_name])):?>
	                        <div class="wpckan_resource_<?php echo($field_name); ?>">
	                          <?php
                            $to_print = $resource[$field_name];
                            if (is_array($resource[$field_name])):
                              $to_print =  !empty($resource[$field_name][$current_language]) ? $resource[$field_name][$current_language] : $resource[$field_name]["en"];
                            endif;
														if ($should_link_to_dataset): ?>
															<a <?php if ($target_blank_enabled){ echo 'target="_blank"';} ?>  href="<?php echo wpckan_get_link_to_dataset($resource["name"]) ?>">
																<?php echo $to_print; ?>
															</a>
	                          <?php
	                          else: ?>
	                            <?php echo $to_print; ?>
	                          <?php
	                          endif; ?>
	                        </div>
                      <?php
												endif;
                      endforeach; ?>
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
