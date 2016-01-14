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
set_time_limit(180);
include ( "src/bignumber.php" );

$time_pre = microtime(true);

$y = new BigNumber("123456789");
$y = new BigNumber("1234567890987654321");

$y->Div("123456789");
echo $y->ToString(), "<br>"; // 10000000008.00000007290000066339
                             // 10        8.0000000729000006633900060368490549353263999114702391943791766688505076865396199475105415223459278533


$time_post = microtime(true);
$exec_time = $time_post - $time_pre;
echo "{$exec_time}ms<br>";

echo "<br />";
echo "BigNumber rules!";
?>
  </body>
</html>
