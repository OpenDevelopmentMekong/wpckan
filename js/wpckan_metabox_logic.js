const DATASET_ID_ATTR = "wpckan-dataset-id";
const DATASET_TITLE_ATTR = "wpckan-dataset-title";
const CKAN_BASE_URL = "wpckan-base-url";
const CKAN_API_URL = "wpckan-api-url";

var field;
var datasetList;
var addButton;

var DEBUG = false;

var datasets = [];

jQuery( document ).ready(function() {

  //Init div elements
  field = jQuery('#wpckan_related_datasets_add_field');
  datasetList = jQuery('#wpckan_related_datasets_list');
  addButton = jQuery("#wpckan_related_datasets_add_button");

  getFormValue();
  updateAndListDatasets();

  clearField();
  field.removeAttr("disabled");
	addButton.addClass("disabled");

  // Instantiate the Bloodhound suggestion engine
  var suggestions = new Bloodhound({
    datumTokenizer: function (datum) {
      return Bloodhound.tokenizers.whitespace(datum.value);
    },
    queryTokenizer: Bloodhound.tokenizers.whitespace,
    remote: {
      url: field.attr(CKAN_API_URL) + '3/action/package_search?q=%QUERY',
      prepare: function (query, settings) {
        settings.dataType = "jsonp"
        settings.url = settings.url.replace('%QUERY', query)
        return settings;
      },
      wildcard: '%QUERY',
      filter: function (json) {
        if (json.success){
          return jQuery.map(json.result.results, function (dataset) {
            return {
              id: dataset.id,
              title: dataset.title
            };
          });
        }
      }
    },limit:20
  });

  // Initialize the Bloodhound suggestion engine
  suggestions.initialize();

  // Instantiate the Typeahead UI
  jQuery('.typeahead').typeahead(null, {
    hint: true,
    minLength: 1,
    highlight: true,
    displayKey: 'title',
    source: suggestions.ttAdapter()
  }).on("typeahead:selected",function(event,item,dataset){
    if (DEBUG) {
      console.log(item["id"]);
      console.log(item["title"]);
    }

    jQuery(this).attr(DATASET_ID_ATTR,item["id"]);
    jQuery(this).attr(DATASET_TITLE_ATTR,escape(item["title"]));
    addButton.removeClass("disabled");
  });

  // TODO improve
  jQuery('.delete').on("click",function(){
    var dataset_id = jQuery(this).attr(DATASET_ID_ATTR);
    removeDatasetWithIdForEntry(dataset_id,this);
  });

	if (DEBUG) {
    console.log("wpckan_metabox_logic.js document ready");
  }

});

function wpckan_related_dataset_metabox_on_input(){
  if (DEBUG) console.log("wpckan_related_dataset_metabox_on_input");

  addButton.addClass("disabled");
}

function wpckan_related_dataset_metabox_add(){
  if (DEBUG) console.log("wpckan_related_dataset_metabox_add");

  var dataset_id = field.attr(DATASET_ID_ATTR);
  var dataset_title = field.attr(DATASET_TITLE_ATTR);

  var dataset = getDatasetWithId(dataset_id);
  if (dataset){
    clearField();
    return;
  }

  if (dataset_id){
   addDataset(true,dataset_id,dataset_title);
   clearField();
  }

}

function updateAndListDatasets(){
  if (DEBUG) {
    console.log("updateAndListDatasets");
  }

  for (var index in datasets){
    dataset = datasets[index];
    addDataset(false,dataset["dataset_id"],dataset["dataset_title"]);
  }

}

function getFormValue(){
  var datasets_json = jQuery("#wpckan_add_related_datasets_datasets").val();
  if (DEBUG) {
    console.log("getFormValue "+ datasets_json);
  }
  if (datasets_json) datasets = JSON.parse(datasets_json);
}

function setFormValue(){
  var datasets_json = JSON.stringify(datasets);
  if (DEBUG) {
    console.log("setFormValue "+ datasets_json);
  }
  jQuery("#wpckan_add_related_datasets_datasets").val(datasets_json);
}

function addDataset(save_in_array,dataset_id,dataset_title){

  if (dataset_id === undefined){
    return;
  }

  var dataset_url = field.attr(CKAN_BASE_URL) + "/dataset/" + dataset_id;

  if (save_in_array){
    datasets.push({"dataset_id": dataset_id, "dataset_title": dataset_title});
    if (DEBUG) {
      console.log("Added dataset with id: " + dataset_id + " datasets in array: "+ datasets.length);
    }
    setFormValue();
  }

  var entry = jQuery('<p><a target="_blank" href='+dataset_url+'>'+unescape(dataset_title)+'</a>   </p>');
  var del = jQuery('<a class="delete error" '+DATASET_ID_ATTR+'='+dataset_id+' href="#">Delete</a>');
  // TODO improve
  jQuery(del).on("click",function(){
    var dataset_id = jQuery(this).attr(DATASET_ID_ATTR);
    removeDatasetWithIdForEntry(dataset_id,this);
  });
  entry.append(del);
  datasetList.append(entry);
}

//TODO optimize (using lodash)
function removeDatasetWithIdForEntry(id,entry){
  entry.parentNode.remove();  //use plain javascript here to get the parent
  var datasetIndex = getDatasetIndexWithId(id);
  if (datasetIndex){
    if (DEBUG) console.log("removing " + id + " from datasets");
    datasets.splice(datasetIndex,1);
    setFormValue();
    return;
  }

}

//TODO optimize (using lodash)
function getDatasetWithId(id){
  for (index in datasets){
    if (datasets[index]["dataset_id"] == id){
      return datasets[index];
    }
  }
  return null;
}

//TODO optimize (using lodash)
function getDatasetIndexWithId(id){
  for (index in datasets){
    if (datasets[index]["dataset_id"] == id){
      return index;
    }
  }
  return null;
}

function clearField(){
  field.val("");
}

function deserialize(s) {
  return unescape(s);
}

function serialize(s) {
  return escape(s);
}
