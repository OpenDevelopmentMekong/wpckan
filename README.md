wp-ckan
=======

A wordpress plugin for integrating CKAN and WP.

## Description

wp-ckan is a wordpress plugin that exposes a series of functionalities to bring content stored in CKAN to Wordpress' UI and also provide mechanisms for archiving content generated on Wordpress into a CKAN instance.

## Features

### Feature 1: Add related CKAN datasets to posts.

Plugin presents a metabox while users are editing posts with an autocompletion input field that
allows the user to add related CKAN datasets. Suggestions for related datasets and its metadata (title, description, and resources) are shown to the user while typing in the input field. Users can add a certain number of datasets that will get stored along the post's metadata.

In order to use this information, this plugin exposes a shortcode for embedding information about related
datasets on the content of the post.
The plugin can be extended by adding following parameters:

* **include_fields_dataset**:  (Optional) Comma-separated.
Per default, this shortcode shows only title (with link to the dataset's URL) and notes of the CKAN dataset (See http://demo.ckan.org/api/3/action/package_search?q=spending). A list of attributes can be specified to present more information. Possible values: "title", "notes", "license", "license_url" "metadata_created", "metadata_modified", "author" , "author_email"

* **include_fields_resources**:  (Optional) Comma-separated.
Per default, this shortcode shows only name (with link to the resources's URL), description and format of the resources (See http://demo.ckan.org/api/3/action/package_search?q=spending). A list of attributes can be specified to present more information. Possible values: "name", "description", "revision_timestamp", "format", "created"

Examples:
```php
[wp_ckan_related_datasets]
[wp_ckan_related_datasets include_fields_dataset="title,description,author"]
[wp_ckan_related_datasets include_fields_dataset="title,description,author" include_fields_resources="name,description,created"]
```

An example showing how the information returned by this shortcode will be structured:

```html
<div class="wpckan_dataset_list">
  <ul>
    <li>
      <div class="wpckan_dataset">
        <div class="wpckan_dataset_title">Title</div>
        <div class="wpckan_dataset_notes">Notes</div>
        <div class="wpckan_dataset_license">License</div>
        <div class="wpckan_dataset_author">Author</div>
        /*.... other fields ....*/
        <div class="wpckan_resources_list">
          <ul>
            <li>
              <div class="wpckan_resource">
                <div class="wpckan_resource_name">Name</div>
                <div class="wpckan_resource_description">Description</div>
                /*.... other fields ....*/
              </div>
            </li>
            /*.... other resources ....*/
          </ul>
        </div>
      </div>
    </li>
    /*.... other dataset <li> ....*/
  </ul>
</div>
```

### Feature 2: Query lists of CKAN datasets

Plugin will expose a function that returns a list of CKAN datasets resulting after querying
CKAN's API. Datasets can be queried after Organization, Group and/or specifying a textual
search.
The results of this function can be shown anywhere on a Wordpress instance (Posts,
Pages, etc..) by calling a shortcode. Per default, this shortcode shows only title and description of the dataset.
The plugin can be extended by adding following parameters:

* **query**: (Mandatory) Term to query the database.

* **organization**: (Optional) Filter dataset results by showing only those belonging to a certain organization.

* **group**: (Optional) Filter dataset results by showing only those belonging to a certain group.

* **include_fields_dataset**:  (Optional) Comma-separated.
Per default, this shortcode shows only title (with link to the dataset's URL) and notes of the CKAN dataset (See http://demo.ckan.org/api/3/action/package_search?q=spending). A list of attributes can be specified to present more information. Possible values: "title", "notes", "license", "license_url" "metadata_created", "metadata_modified", "author" , "author_email"

* **include_fields_resources**:  (Optional) Comma-separated.
Per default, this shortcode shows only name (with link to the resources's URL), description and format of the resources (See http://demo.ckan.org/api/3/action/package_search?q=spending). A list of attributes can be specified to present more information. Possible values: "name", "description", "revision_timestamp", "format", "created"

Examples:
```php
[wp_ckan_query_datasets query="coal"]
[wp_ckan_query_datasets query="forestry" organization="odmcambodia" group="news"]
[wp_ckan_query_datasets query="elections" include_fields_dataset="title,notes,license" include_fields_resources="name,description,created"]
```

```html
<div class="wpckan_dataset_list">
  <ul>
    <li>
      <div class="wpckan_dataset">
        <div class="wpckan_dataset_title">Title</div>
        <div class="wpckan_dataset_notes">Notes</div>
        <div class="wpckan_dataset_license">License</div>
        <div class="wpckan_dataset_author">Author</div>
        /*.... other fields ....*/
        <div class="wpckan_resources_list">
          <ul>
            <li>
              <div class="wpckan_resource">
                <div class="wpckan_resource_name">Name</div>
                <div class="wpckan_resource_description">Description</div>
                /*.... other fields ....*/
              </div>
            </li>
          </ul>
          /*.... other resources ....*/
        </div>
      </div>
    </li>
  /*.... other dataset <li> ....*/
  </ul>
</div>
```

### Feature 3: Archiving WP Posts in CKAN

The plugin presents a metabox while users are editing posts. It allows users to specify if the post should be archived as a CKAN dataset. The plugin polls the CKAN instance and retrieves the list of available organizations and groups in order for users to be able to determine to which organization or group the dataset will be assign to. Also, when that particular post will be archived (on save or on publish).

## Installation

1. Install composer http://getcomposer.org/
2. Install dependencies <code>composer install</code>
3. Copy to the plugins folder of your wordpress installation

## Uses

* Analog logger https://github.com/jbroadway/analog
* Silex CKAN PHP Client https://github.com/SilexConsulting/CKAN_PHP
* Twitter's typeahead https://github.com/twitter/typeahead.js/

## License

TBD
