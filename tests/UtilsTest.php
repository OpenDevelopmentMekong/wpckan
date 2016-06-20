<?php

require_once dirname(dirname(__FILE__)) . '/utils/wpckan-utils.php';

class UtilsTest extends PHPUnit_Framework_TestCase
{
  public function setUp()
  {
  }

  public function tearDown()
  {
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

  public function testSanitizeUrl()
  {
      //wpckan_sanitize_url
      $this->markTestSkipped(
              'TODO testSanitizeUrl'
            );
  }

  public function testStripMqTranslateTags()
  {
      //wpckan_strip_mqtranslate_tags
      $this->markTestSkipped(
              'TODO testStripMqTranslateTags'
            );
  }

  public function testIsValidUrl()
  {
      //wpckan_is_valid_url
      $this->markTestSkipped(
              'TODO testIsValidUrl'
            );
  }

}
