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
