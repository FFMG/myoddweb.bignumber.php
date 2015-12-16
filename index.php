<?php
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
new MyOddWeb\BigNumber();
echo "BigNumber rules!";
?>
  </body>
</html>
