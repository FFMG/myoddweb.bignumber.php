<?php
ini_set('error_reporting', E_ALL); // or error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

date_default_timezone_set( "UTC" );

use MyOddWeb\BigNumber;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
               "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="author" content="FFMG" />
    <meta name="website" content="http://www.myoddweb.com" />
    <title>Sample Implementation of BigNumber.php</title>
  </head>
  <body>
<?php
include ( "src/bignumber.php" );

$rad = \MyOddWeb\BigNumber( \MyOddWeb\BigNumber::pi() )->ToDegree(10);
$x = $rad->ToString();
echo $x;

echo "<br />";
echo "BigNumber rules!";
?>
  </body>
</html>
