<?php 
if ((!isset($_GET['q'])) && (!isset($_GET['q']))) { header("Location:../../index.php"); }
else { echo "<i>- Error 404: the page you requested does not exist or is not available anymore. You will be redirect in few seconds...</i>"; header("refresh:7; url=index.php"); die(); }
?>