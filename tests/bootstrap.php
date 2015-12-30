<?php
ini_set('error_reporting', E_ALL); // or error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

date_default_timezone_set( "UTC" );

// make sure we have the right include path.
set_include_path( get_include_path() . (";" . dirname(__FILE__) . '\..' ));

require_once "vendor/autoload.php";
?>