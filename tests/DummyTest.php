<?php

class HelloWorldTest extends PHPUnit_Framework_TestCase
{

    private $assertion = false;

    public function setUp()
    {
        // init vars here
        $this->assertion = true;
    }

    public function tearDown()
    {
        // undo stuff here
    }

    public function testHelloWorld()
    {
        $this->assertEquals($this->assertion,True);
    }
}
