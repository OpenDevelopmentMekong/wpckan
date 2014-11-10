wp-ckan
=======

A wordpress plugin for integrating CKAN and WP.

## Description

wp-ckan is a wordpress plugin that exposes a series of functionalities to bring content stored in CKAN to Wordpress' UI and also provide mechanisms for archiving content generated on Wordpress into a CKAN instance.

## Features

### Feature 1: Add related CKAN datasets to posts.

Plugin presents a Metabox while editing Posts with an autocompletion input field that
allows the user to add related CKAN datasets. Related datasets are rendered through a
live preview of the contents and metadata included in the dataset (title, description, and
resources)

Add an option on the settings for the user to choose if the previews of the related datasets
are shown automatically or not and to configure how they will be shown. Alternatively,
users can use shortcodes or template tags for embedding information about related
datasets on other parts of the posts or pages.

Template Tags:
```php
<?php wp_ckan_get_related_datasets('post_id','preview'); ?>
```
Shortcodes:
```php
[wp_ckan_related_datasets show_preview=”yes” ]
```

### Feature 2: Query lists of CKAN datasets

Plugin will expose a function that returns a list of CKAN datasets resulting after querying
CKAN's API. Datasets can be queried after Organization, Group and/or specifying a textual
search.
The results of this function can be shown anywhere on a Wordpress instance (Posts,
Pages, etc..) by calling a shortcode or template tag. This function could be integrated
within the search mechanism of the chosen Wordpress theme (JEO) in order to present
both contents from WP and CKAN together (i.e 2 column result list)

Template Tags:
```php
<?php wp_ckan_query_datasets('query','limit'); ?>
<?php wp_ckan_query_datasets_from_organization('query','organization','limit'); ?>
<?php wp_ckan_query_datasets_from_group('query','group','limit'); ?>
```
Shortcodes:
```php
[wp_ckan_query_datasets query=”forestry” organization=”odmcambodia” group=”news”
limit=”5”]
```

Returns a list of datasets found after querying the CKAN database. 4 possible parameters:
1. query: a text to search forestry.
2. organization: filter datasets that belong to specified organization.
3. group: filter datasets that are assigned to specified group
4. limit: limit the number of results

### Feature 3: Archiving WP Posts in CKAN

The plugin will expose a function that archives WP-generated content into CKAN. This function
can be configured to be triggered when a post is published/modified or periodically (hourly, daily,
weekly). For instance, using Hooks to trigger this function when a Wordpress' post is created,
modified or deleted (See List of Wordpress Hooks).
In order to be able to identify and relate Wordpress' content (Posts) with archived content in CKAN,
Wordpress' unique id identifier (UUID) will be used.

## Installation

## License
