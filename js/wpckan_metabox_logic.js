function wpckan_related_dataset_metabox_on_change(){
  console.log("wpckan_related_dataset_metabox_on_change");
}

function wpckan_related_dataset_metabox_add(){
  console.log("wpckan_related_dataset_metabox_add");

  var entry = jQuery('<p></p>');
  var field = jQuery('<input class="new" type="text" id="wpckan_dataset_url_field_0" name="wpckan_dataset_url_field_0" value="" size="25" />');;
  field.on("change",function(){
    alert("change");
  });
  var del = jQuery('<input class="button delete" type="button" value="-"/>');
  del.on("click",function(){
    alert("delete");
  });
  entry.append(field);
  entry.append(del);

  jQuery('#wpckan_related_datasets_entries').append(entry);
}
