<?php

  require 'vendor/autoload.php';
  use Silex\ckan\CkanClient;
  use Analog\Analog;

  function wpckan_log($text) {
    Analog::log ($text);
  }

?>
