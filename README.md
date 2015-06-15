wp-ckan
=======

A wordpress plugin for integrating CKAN http://ckan.org/ and WP http://wordpress.org/.

# Description

wpckan is a wordpress plugin that exposes a series of functionalities to bring content stored in CKAN to Wordpress' UI and also provide mechanisms for archiving content generated on Wordpress into a CKAN instance.

# Features

## Feature 1: Add related CKAN datasets to posts.

Plugin presents a metabox while users are editing posts with an autocompletion input field that
allows the user to add related CKAN datasets. Suggestions for related datasets and its metadata (title, description, and resources) are shown to the user while typing in the input field. Users can add a certain number of datasets that will get stored along the post's metadata.

In order to use this information, this plugin exposes the **[wpckan_related_datasets]** shortcode for embedding information about related datasets on the content of the post.
The shortcode has following parameters:

* **group**:  (Optional)
Specify the name (Not title) of a group available on the target CKAN instance in order to filter the related datasets to ONLY those assigned to it.

* **organization**:  (Optional)
Specify the name (Not title) of an organization available on the target CKAN instance in order to filter the related datasets to ONLY those assigned to it.

Note: If both **group** and **organization** parameters are specified then the dataset has to be asssigned to both in order to be returned by the shortcode.

* **include_fields_dataset**:  (Optional) Comma-separated string.
Per default, this shortcode shows only title and notes of the CKAN dataset (See http://demo.ckan.org/api/3/action/package_search?q=spending). A list of attributes can be specified to present more information. Possible values: "title", "notes", "url", "license", "license_url" "metadata_created", "metadata_modified", "author" , "author_email"

* **include_fields_resources**:  (Optional) Comma-separated string.
Per default, this shortcode shows only name, description and format of the resources (See http://demo.ckan.org/api/3/action/package_search?q=spending). A list of attributes can be specified to present more information. Possible values: "name", "description", "revision_timestamp", "format", "created"

* **limit**: (Optional) Number.
Limits the amount of datasets shown by the shortcode string.

* **filter**: (Optional) Number.
Filters the datasets according to following criteria:
  * '0' (ALL): Return all datasets (Default)
  * '1' (ONLY WITH RESOURCES): Return only datasets featuring at least one resource.

* **filter_fields**: (Optional) JSON.
Filters the datasets according to the content of the datasets' extra fields. The list of fields and values is specified as JSON string. The name of the fields must match exactly (case unsensitive) but for the value the php strpos() function will be employed. The OR operator will be applied if more than 1 key/value combination are given. See examples below.

#### Pagination

* **page**: (Optional) Number.
When used together with **limit**, returned datasets will get paginated. In case of possible pagination, this parameter specifies which page is returned. If there are not enough related datasets to paginate, this parameter will be ignored.
Example: if there are 8 related datasets, limit = 2, page = 2, then datasets 2 and 3 will be returned. Mind that order begins on 1.

* **prev_page_link**: (Optional) String.
If provided, and as long **limit** and **page** are also given parameters, shows a link to this URL. The default text is "Previous"

* **prev_page_title**: (Optional) String.
Replaces "Previous" (Standard text) with the specified text.

* **next_page_link**: (Optional) String.
If provided, and as long **limit** and **page** are also given parameters, shows a link to this URL. The default text is "Next"

* **next_page_title**: (Optional) String.
Replaces "Next" (Standard text) with the specified text.

## Advanced

* **blank_on_empty**: (Optional) Boolean.
Returns an empty string "" if no datasets have been found to return

Examples:
```php
[wpckan_related_datasets]
[wpckan_related_datasets limit="3"]
[wpckan_related_datasets limit="3" page="2"]
[wpckan_related_datasets limit="3" page="2" prev_page_link="http://test?prev_page" next_page_link="http://test?next_page"]
[wpckan_related_datasets include_fields_dataset="title,description,author"]
[wpckan_related_datasets include_fields_dataset="title,description,author" include_fields_resources="name,description,created"]
[wpckan_related_datasets limit="3" filter_fields='{"spatial-text":"England","date":"2015"}']
[wpckan_related_datasets blank_on_empty='true']
```

An example showing how the information returned by this shortcode will be structured:

```html
<div class="wpckan_dataset_list">
  <ul>
    <li>
      <div class="wpckan_dataset">
        <div class="wpckan_dataset_title"><a href="http://link_to_dataset">Title</a></div>
        <div class="wpckan_dataset_notes">Notes</div>
        <div class="wpckan_dataset_license">License</div>
        <div class="wpckan_dataset_author">Author</div>
        /*.... other fields ....*/
        <div class="wpckan_resources_list">
          <ul>
            <li>
              <div class="wpckan_resource">
                <div class="wpckan_resource_name"><a href="http://link_to_resource">Name</a></div>
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
<div class="wpckan_dataset_list_pagination">
  <a href="#">Previous</a>
  <a href="#">Next</a>
</div>
```

Also, the plugin exposes the  **[wpckan_number_of_related_datasets]** shortcode for returning the number of related datasets assigned to the post as a customizable link so a summary can be presented on the wordpress side.
The shortcode has following parameters:

* **group**:  (Optional)
Specify the name (Not title) of a group available on the target CKAN instance in order to filter the related datasets to ONLY those assigned to it.

* **organization**:  (Optional)
Specify the name (Not title) of an organization available on the target CKAN instance in order to filter the related datasets to ONLY those assigned to it.

Note: If both **group** and **organization** parameters are specified then the dataset has to be asssigned to both in order to be returned by the shortcode.

* **limit**: (Optional) Number.
Limits the amount of datasets shown by the shortcode.

* **filter**: (Optional) Number.
Filters the datasets according to following criteria:
  * '0' (ALL): Return all datasets (Default)
  * '1' (ONLY WITH RESOURCES): Return only datasets featuring at least one resource.


* **filter_fields**: (Optional) JSON.
Filters the datasets according to the content of the datasets' extra fields. The list of fields and values is specified as JSON string. The name of the fields must match exactly (case unsensitive) but for the value the php strpos() function will be employed. The OR operator will be applied if more than 1 key/value combination are given. See examples below.

* **link_url**:  (Optional)
Specify the URL to link the produced output with some other resource (i.e: in the CKAN instance)

* **prefix**:  (Optional)
Prepends a string before the number.

* **suffix**:  (Optional)
Appends a string after the number.

## Advanced

* **blank_on_empty**: (Optional) Boolean.
Returns an empty string "" if no datasets have been found to return

Examples:
```php
[wpckan_number_of_related_datasets]
[wpckan_number_of_related_datasets link_url="http://link_to_more"]
[wpckan_number_of_related_datasets group="news"]
[wpckan_number_of_related_datasets group="news" limit="1"]
[wpckan_number_of_related_datasets group="news" suffix=" datasets found in the news."]
[wpckan_number_of_related_datasets group="news" prefix="Number of datasets: (" suffix=")" link_url="http://link_to_more"]
[wpckan_number_of_related_datasets limit="3" filter_fields='{"spatial-text":"England","date":"2015"}']
[wpckan_number_of_related_datasets blank_on_empty="true"]
```
An example (corresponding to the last example above) showing how the information returned by this shortcode will be structured:

```html
<div class="wpckan_dataset_number">
  <p><a target="_blank" href="http://link_to_more">Number of datasets: (5)</a></p>
</div>
```

## Feature 2: Query lists of CKAN datasets

Plugin exposes a function which returns a list of CKAN datasets resulting after querying
CKAN's API. Resulting datasets can be filtered by organization, group and/or specifying a textual
search.

The results of this function can be shown anywhere on a Wordpress instance (Posts,
Pages, etc..) by calling the **[wpckan_query_datasets query="QUERY"]** shortcode. Per default, this shortcode shows only title and description of the dataset.
The shortcode has following parameters:

* **query**: (Mandatory) Term to query the database.

* **organization**: (Optional) Filter dataset results by showing only those belonging to a certain organization.

* **group**: (Optional) Filter dataset results by showing only those belonging to a certain group.

* **include_fields_dataset**:  (Optional) Comma-separated.
Per default, this shortcode shows only title (with link to the dataset's URL) and notes of the CKAN dataset (See http://demo.ckan.org/api/3/action/package_search?q=spending). A list of attributes can be specified to present more information. Possible values: "title", "notes", "url", "license", "license_url" "metadata_created", "metadata_modified", "author" , "author_email"

* **include_fields_resources**:  (Optional) Comma-separated.
Per default, this shortcode shows only name (with link to the resources's URL), description and format of the resources (See http://demo.ckan.org/api/3/action/package_search?q=spending). A list of attributes can be specified to present more information. Possible values: "name", "description", "revision_timestamp", "format", "created"

* **limit**: (Optional) Number.
Limits the amount of datasets shown by the shortcode.

* **filter**: (Optional) Number.
Filters the datasets according to following criteria:
  * '0' (ALL): Return all datasets (Default)
  * '1' (ONLY WITH RESOURCES): Return only datasets featuring at least one resource.


* **filter_fields**: (Optional) JSON.
Filters the datasets according to the content of the datasets' extra fields. The list of fields and values is specified as JSON string. The name of the fields must match exactly (case unsensitive) but for the value the php strpos() function will be employed. The OR operator will be applied if more than 1 key/value combination are given. See examples below.

#### Pagination

* **page**: (Optional) Number.
When used together with **limit**, returned datasets will get paginated. In case of possible pagination, this parameter specifies which page is returned. If there are not enough related datasets to paginate, this parameter will be ignored.
Example: if there are 8 related datasets, limit = 2, page = 2, then datasets 2 and 3 will be returned. Mind that order begins on 1.

* **prev_page_link**: (Optional) String.
If provided, and as long **limit** and **page** are also given parameters, shows a link to this URL. The default text is "Previous"

* **prev_page_title**: (Optional) String.
Replaces "Previous" (Standard text) with the specified text.

* **next_page_link**: (Optional) String.
If provided, and as long **limit** and **page** are also given parameters, shows a link to this URL. The default text is "Next"

* **next_page_title**: (Optional) String.
Replaces "Next" (Standard text) with the specified text.

## Advanced

* **blank_on_empty**: (Optional) Boolean.
Returns an empty string "" if no datasets have been found to return

Examples:
```php
[wpckan_query_datasets query="coal"]
[wpckan_query_datasets query="corruption" limit="5"]
[wpckan_query_datasets query="corruption" limit="5" page="1"]
[wpckan_query_datasets query="politics" limit="3" page="2" prev_page_link="http://test?prev_page" next_page_link="http://test?next_page"]
[wpckan_query_datasets query="forestry" organization="odmcambodia" group="news"]
[wpckan_query_datasets query="elections" include_fields_dataset="title,notes,license" include_fields_resources="name,description,created"]
[wpckan_query_datasets limit="3" filter_fields='{"spatial-text":"England","date":"2015"}']
[wpckan_query_datasets query="coal" blank_on_empty='true']
```

```html
<div class="wpckan_dataset_list">
  <ul>
    <li>
      <div class="wpckan_dataset">
        <div class="wpckan_dataset_title"><a href="http://link_to_dataset">Title</a></div>
        <div class="wpckan_dataset_notes">Notes</div>
        <div class="wpckan_dataset_license">License</div>
        <div class="wpckan_dataset_author">Author</div>
        /*.... other fields ....*/
        <div class="wpckan_resources_list">
          <ul>
            <li>
              <div class="wpckan_resource">
                <div class="wpckan_resource_name"><a href="http://link_to_resource">Name</a></div>
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
<div class="wpckan_dataset_list_pagination">
<a href="#">Previous</a>
<a href="#">Next</a>
</div>
```

## Feature 3: Archiving WP Posts in CKAN

The plugin presents a metabox while users are editing posts. It allows users to specify if the post should be archived as a CKAN dataset. The plugin polls the CKAN instance and retrieves the list of available organizations and groups in order for users to be able to determine to which organization or group the dataset will be assign to. Also, when that particular post will be archived.

This feature archives the custom fields along with the title and description. If a valid URL is found in the value of the custom fields, a new resource will be added to the dataset.

**WARNING** However, custom fields beginning with **_** or **wpckan_** will not be stored.

## CORS Support disabled for CKAN >2.3

Taken from http://docs.ckan.org/en/latest/changelog.html#id1:

> Cross-Origin Resource Sharing (CORS) support is no longer enabled by default. Previously, Access-Control-Allow-* response headers were added for all requests, with Access-Control-Allow-Origin set to the wildcard value *.
> To re-enable CORS, use the new ckan.cors configuration settings (ckan.cors.origin_allow_all and ckan.cors.origin_whitelist).

So, mind that the CKAN instance which this plugin is used with needs to allow all origins or whitelist the domain where the wpckan is installed.

# Installation

1. Either download the files as zip or clone recursively (contains submodules) <code>git clone https://github.com/OpenDevelopmentMekong/wpckan.git --recursive</code> into the Wordpress plugins folder.
2. Activate the plugin through the 'Plugins' menu in WordPress

# Development

1. Install composer http://getcomposer.org/
2. Edit composer.json for adding/modifying dependencies versions
3. Install dependencies <code>composer install</code>

# Requirements

* PHP 5 >= 5.2.0

# Uses

* Analog logger https://github.com/jbroadway/analog
* Silex CKAN PHP Client https://github.com/SilexConsulting/CKAN_PHP
* Twitter's typeahead https://github.com/twitter/typeahead.js/

# Copyright and License

This material is copyright (c) 2014-2015 East-West Management Institute, Inc. (EWMI).

It is open and licensed under the GNU General Public License (GPL) v3.0 whose full text may be found at:

http://www.fsf.org/licensing/licenses/gpl-3.0.html
