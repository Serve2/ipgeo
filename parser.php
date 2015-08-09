<?php
$inetnum = null;
$country = null;
$servername = "localhost";
$username = "";
$password = "";
$dbname = "ipgeo";



$conn = new PDO("mysql:host=$servername", $username, $password);
$query="DROP DATABASE $dbname";
$conn->exec($query);
$query="CREATE DATABASE IF NOT EXISTS $dbname";
$conn->exec($query);
try{
$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
$query="CREATE TABLE $dbname (ip_from INT(12),ip_to INT(12),country VARCHAR(2));";
echo $query;
$conn->exec($query);
} catch (PDOException $e) {
  echo $e->getMessage();
}

function startsWith($haystack, $needle) {
    return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
}
$handle = @fopen("source/ripe.db", "r");
$count = 0;
$file = 'test.txt';
//$current = file_get_contents($file);
if ($handle) {
    while (($buffer = fgets($handle, 4096)) !== false) {
        if (startsWith($buffer, 'inetnum')) {
          $inetnum = str_replace('inetnum:', '', $buffer);
//$current .= $buffer;
          $range = explode(' - ', $inetnum);
          $dotCount = substr_count($range[0], '.');
          $lastChar = substr($range[0], -1);
          if($dotCount == 2) {
              $range[0] . '.0';
              $range[0] = ip2long(trim($range[0]));
echo 'fixed';
          } else if ($lastChar == '.') {
              $range[0] . '0';
              $range[0] = ip2long(trim($range[0]));
echo 'fixed';
          } else {
              $range[0] = ip2long(trim($range[0]));
          }
          $lastChar = substr($range[1], -1);
          $dotCount = substr_count($range[1], '.');
          if($dotCount == 2) {
              $range[1] . '.0';
              $range[1] = ip2long(trim($range[1]));
echo 'fixed';
          } else if ($lastChar == '.') {
              $range[1] . '0';
              $range[1] = ip2long(trim($range[1]));
echo 'fixed';
          } else {
              $range[1] = ip2long(trim($range[1]));
          }
          
        }
        if(startsWith($buffer, 'country')) {
          $country = strtoupper(trim(str_replace('country:', '', $buffer)));
          if($inetnum != null) {
$count++;
if ($count % 20000 == 0) {
    echo $count;
    echo $country;
    echo $inetnum;
}
$query = "INSERT INTO ipgeo (ip_from, ip_to, country) VALUES ('$range[0]','$range[1]','$country')";
try {
$conn->exec($query);
//echo 'INSERTING...';
} catch (PDOException $e)
    {
    echo $sql . "<br>" . $e->getMessage();
    }
          $country = null;
          $inetnum = null;

          }
        }
    }
    fclose($handle);
//file_put_contents($file, $current);
}
