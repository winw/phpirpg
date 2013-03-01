<?php
error_reporting(E_ALL & ~E_WARNING);
$data = "inc/pages/count.txt";
if(!file_exists($data)){
	$a = fopen($data, "w");
	fputs ($a, 0);
}
$a = fopen($data,"r");
$value = fgets($a);
$value++;
$a = fopen($data, "w");
fputs($a, $value);
fclose($a);
?>