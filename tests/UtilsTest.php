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
    $this->assertArrayHasKey("q",$arguments);
    $this->assertEquals($arguments["q"],"some_query");
  }

  public function testComposeSolrQueryFromAttrsGroup(){
    $attrs = array('group' => 'some_group_name');
    $arguments = compose_solr_query_from_attrs($attrs);
    $this->assertArrayHasKey("fq",$arguments);
    $this->assertContains(" groups:some_group_name",$arguments["fq"]);
  }

  public function testComposeSolrQueryFromAttrsOrganization(){
    $attrs = array('organization' => 'some_organization_name');
    $arguments = compose_solr_query_from_attrs($attrs);
    $this->assertArrayHasKey("fq",$arguments);
    $this->assertContains(" owner_org:some_organization_name",$arguments["fq"]);
  }

  public function testComposeSolrQueryFromAttrsType(){
    $attrs = array('type' => 'some_type');
    $arguments = compose_solr_query_from_attrs($attrs);
    $this->assertArrayHasKey("fq",$arguments);
    $this->assertContains(" type:some_type",$arguments["fq"]);
  }

  public function testComposeSolrQueryFromAttrsOneId(){
    $attrs = array('ids' => 'some_id');
    $arguments = compose_solr_query_from_attrs($attrs);
    $this->assertArrayHasKey("fq",$arguments);
    $this->assertContains(" id:(some_id)",$arguments["fq"]);
  }

  public function testComposeSolrQueryFromAttrsNoId(){
    $attrs = array('ids' => array());
    $arguments = compose_solr_query_from_attrs($attrs);
    $this->assertFalse(array_key_exists("fq",$arguments));
  }

  public function testComposeSolrQueryFromAttrsIds(){
    $attrs = array('ids' => array('some_id','other_id'));
    $arguments = compose_solr_query_from_attrs($attrs);
    $this->assertArrayHasKey("fq",$arguments);
    $this->assertContains(" id:(some_id OR other_id)",$arguments["fq"]);
  }

  public function testComposeSolrQueryFromAttrsFilterFields(){
    $attrs = array('filter_fields' => '{"spatial-text":"England","date":"2015"}');
    $arguments = compose_solr_query_from_attrs($attrs);
    $this->assertArrayHasKey("fq",$arguments);
    $this->assertContains(" spatial-text:England",$arguments["fq"]);
    $this->assertContains(" date:2015",$arguments["fq"]);
  }

  public function testComposeSolrQueryFromAttrsLimit(){
    $attrs = array('limit' => '10');
    $arguments = compose_solr_query_from_attrs($attrs);
    $this->assertArrayHasKey("rows",$arguments);
    $this->assertEquals(10,$arguments["rows"]);
  }

  public function testComposeSolrQueryFromAttrsFilter(){
    $attrs = array('filter' => '1');
    $arguments = compose_solr_query_from_attrs($attrs);
    $this->assertArrayHasKey("fq",$arguments);
    $this->assertContains(" num_resources:[1 TO *]",$arguments["fq"]);
  }

  public function testComposeSolrQueryFromAttrsLimitAndPage(){
    $attrs = array('limit' => '10', 'page' => '2');
    $arguments = compose_solr_query_from_attrs($attrs);
    $this->assertArrayHasKey("rows",$arguments);
    $this->assertArrayHasKey("start",$arguments);
    $this->assertEquals(10,$arguments["rows"]);
    $this->assertEquals(10,$arguments["start"]);
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
      $result = wpckan_sanitize_csv('uno, dos, tres');
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
}
