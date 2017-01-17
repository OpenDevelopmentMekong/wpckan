<?php

require_once dirname(dirname(__FILE__)) . '/utils/wpckan-utils.php';

class UtilsTest extends PHPUnit_Framework_TestCase
{
  public function setUp()
  {
    $GLOBALS['wpckan_options'] = $this->getMockBuilder(Wpckan_Options::class)
                                   ->setMethods(['get_option'])
                                   ->getMock();

    $GLOBALS['wpckan_options']->method('get_option')
                          ->will($this->returnValueMap(array(
                               array('wpckan_setting_cache_enabled', false),
                               array('wpckan_setting_log_enabled', false)
                           )));
  }

  public function tearDown()
  {
    parent::tearDown();
  }

  public function testComposeSolrQueryFromAttrsQuery(){
    $attrs = array('query' => 'some_query');
    $arguments = compose_solr_query_from_attrs($attrs);
    $this->assertContains($arguments,"&q=\"some_query\"&rows=1000&sort=metadata_modified+desc");
  }

  public function testComposeSolrQueryFromAttrsGroup(){
    $attrs = array('group' => 'some_group_name');
    $arguments = compose_solr_query_from_attrs($attrs);
    $this->assertContains($arguments,"&fq=+groups:some_group_name&rows=1000&sort=metadata_modified+desc");
  }

  public function testComposeSolrQueryFromAttrsOrganization(){
    $attrs = array('organization' => 'some_organization_name');
    $arguments = compose_solr_query_from_attrs($attrs);
    $this->assertContains($arguments,"&fq=+owner_org:some_organization_name&rows=1000&sort=metadata_modified+desc");
  }

  public function testComposeSolrQueryFromAttrsType(){
    $attrs = array('type' => 'some_type');
    $arguments = compose_solr_query_from_attrs($attrs);
    $this->assertContains($arguments,"&fq=+type:some_type&rows=1000&sort=metadata_modified+desc");
  }

  public function testComposeSolrQueryFromAttrsOneId(){
    $attrs = array('ids' => 'some_id');
    $arguments = compose_solr_query_from_attrs($attrs);
    $this->assertContains($arguments,"&fq=+id:(some_id)&rows=1000&sort=metadata_modified+desc");
  }

  public function testComposeSolrQueryFromAttrsNoId(){
    $attrs = array('ids' => array());
    $arguments = compose_solr_query_from_attrs($attrs);
    $this->assertContains($arguments,"&rows=1000&sort=metadata_modified+desc");
  }

  public function testComposeSolrQueryFromAttrsIds(){
    $attrs = array('ids' => array('some_id','other_id'));
    $arguments = compose_solr_query_from_attrs($attrs);
    $this->assertContains($arguments,"&fq=+id:(some_id OR other_id)&rows=1000&sort=metadata_modified+desc");
  }

  public function testComposeSolrQueryFromAttrsFilterFields(){
    $attrs = array('filter_fields' => '{"spatial-text":"England","date":"2015"}');
    $arguments = compose_solr_query_from_attrs($attrs);
    $this->assertContains($arguments,"&fq=+spatial-text:England+date:2015&rows=1000&sort=metadata_modified+desc");
  }

  public function testComposeSolrQueryFromAttrsLimit(){
    $attrs = array('limit' => '10');
    $arguments = compose_solr_query_from_attrs($attrs);
    $this->assertContains($arguments,"&rows=10&sort=metadata_modified+desc");
  }

  public function testComposeSolrQueryFromAttrsDefaultLimit(){
    $attrs = array();
    $arguments = compose_solr_query_from_attrs($attrs);
    $this->assertContains($arguments,"&rows=1000&sort=metadata_modified+desc");
  }

  public function testComposeSolrQueryFromAttrsFilter(){
    $attrs = array('filter' => '1');
    $arguments = compose_solr_query_from_attrs($attrs);
    $this->assertContains($arguments,"&fq=+num_resources:[1 TO *]&rows=1000&sort=metadata_modified+desc");
  }

  public function testComposeSolrQueryFromAttrsLimitAndPage(){
    $attrs = array('limit' => '10', 'page' => '2');
    $arguments = compose_solr_query_from_attrs($attrs);
    $this->assertContains($arguments,"&rows=10&start=10&sort=metadata_modified+desc");
  }

  public function testGetExtension()
  {
      $result = wpckan_get_url_extension("http://domain.com/file.ext");
      $this->assertEquals($result,"ext");
  }

  public function testDetectAndRemoveShortCodes()
  {
      //wpckan_detect_and_remove_shortcodes_in_text
      $this->markTestSkipped(
              'TODO testDetectAndRemoveShortCodes'
            );
  }

  public function testDetectAndEchoShortCodes()
  {
      //wpckan_detect_and_echo_shortcodes_in_text
      $this->markTestSkipped(
              'TODO testDetectAndEchoShortCodes'
            );
  }

  // public function testSanitizeUrl()
  // {
  //     $result = wpckan_sanitize_url('http://data.opendevelopmentmekong.net/');
  //     $this->assertEquals($result,'http://data.opendevelopmentmekong.net');
  // }

  public function testSanitizeCsv()
  {
      $result = wpckan_remove_whitespaces('uno, dos, tres');
      $this->assertEquals($result,'uno,dos,tres');
  }

  public function testStripQTranslateTags()
  {
      //wpckan_strip_qtranslate_tags
      $this->markTestSkipped(
              'TODO testStripQTranslateTags'
            );
  }

  public function testIsInValidUrl()
  {
      $result = wpckan_is_valid_url('invalid');
      $this->assertFalse($result);
  }

  public function testIsValidUrl()
  {
      $result = wpckan_is_valid_url('http://valid');
      $this->assertTrue($result);
  }

  public function testIsValidId()
  {
      $result = wpckan_valid_id('1234');
      $this->assertTrue($result);
  }

  public function testIsInvalidId()
  {
      $result = wpckan_valid_id('');
      $this->assertFalse($result);
  }

  public function testGetDatasetIdFromUrlOk()
  {
      $result = wpckan_get_dataset_id_from_dataset_url("https://data.opendevelopmentmekong.net/dataset/123456?type=dataset");
      $this->assertEquals($result, "123456");
  }

  public function testGetDatasetIdFromUrlAnotherType()
  {
      $result = wpckan_get_dataset_id_from_dataset_url("https://data.opendevelopmentmekong.net/library_record/123456?type=library_record");
      $this->assertEquals($result, "123456");
  }

  public function testGetDatasetIdFromUrlNoParam()
  {
      $result = wpckan_get_dataset_id_from_dataset_url("https://data.opendevelopmentmekong.net/dataset/123456");
      $this->assertEquals($result, "123456");
  }

  public function testGetDatasetIdFromUrlTwoParams()
  {
      $result = wpckan_get_dataset_id_from_dataset_url("https://data.opendevelopmentmekong.net/dataset/123456?type=dataset?another_param=some_value");
      $this->assertEquals($result, "123456");
  }
}
