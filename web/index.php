<?php error_reporting(E_ALL & ~E_WARNING);
 define('BASE_PATH', dirname(__FILE__).'/');
 require_once 'inc/mysqlman/dbDontEscapeString.class.php';
 require_once 'inc/mysqlman/dbInstance.class.php';
 require_once 'inc/mysqlman/dbClassGen.class.php';
dbClassGen::$sPath = BASE_PATH.'inc/mysqlman/db/';
 require_once 'inc/mysqlman/dbControl.class.php';
 require_once 'inc/mysqlman/dbObject.class.php';
 require_once 'inc/mysqlman/Utils.class.php';
 require_once 'inc/mysqlman/dbPrimitiveObject.class.php';
 
 /* Création des instances pdo */
 $oPdo = new PDO('mysql:dbname=phpirpg;host=localhost', 'root', '', Array(
  PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
  PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
  PDO::ATTR_EMULATE_PREPARES => true
 ));
 dbInstance::create('phpirpg', $oPdo);
 
 /* Generation des fichiers de bases de données si on est pas en prod */
 if (1) {
  foreach (dbInstance::getList() AS $sName) {
   dbClassGen::fromInstanceName($sName);
  }
 }
 
 /* Chargement des DBs */
 $aFiles = glob(dbClassGen::$sPath.'*.db.php');
 if ($aFiles) {
  foreach ($aFiles AS $sFile) {
   include_once($sFile);
  }
 }
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="Content-Script-Type" content="text/javascript" />
<meta http-equiv="Content-Style-Type" content="text/css" />
<meta http-equiv="Content-Language" content="fr" />
<link rel="stylesheet" type="text/css" href="inc/css/styles.css" media="all" />
<title>#idle-rpg @ Quakenet.org !</title>
</head>
<body>
<div class="header">
<a href="?q=news"><img src="inc/pict/header.png" alt="" /></a></div>
<div class="menu"><a href="?q=news">News</a> • <a href="?q=map">World Map</a> • <a href="?q=rank">Rank</a> • <a href="?q=stats">Statistics</a> • <a href="?q=story">Story</a> • <a href="?q=dev">Development</a> • <a href="?q=faq">FAQ</a> <span class="menuright"><?php $oChannelUsers = new dbChannelUsers(); $iNb = $oChannelUsers->select('COUNT(1) as nb')->where('id_irpg_user IS NOT NULL')->fetch()->nb; { echo '<strong>'.$iNb.'</strong> player'.(($iNb > 1) ? 's' : ''); } ?> connected. <strong><a href="irc://irc.quakenet.org/idle-rpg">Join us!</a></strong></span></div>
<div class="global"><?php
if (!isset($_GET['q'])) $_GET['q'] = 'news';
switch ($_GET['q']) {
case 'news': include_once('inc/pages/news.php'); break;  
case 'map':	include_once('inc/pages/map.php'); break;
case 'rank': include_once('inc/pages/rank.php'); break;
case 'stats': include_once('inc/pages/stats.php'); break;
case 'dev': include_once('inc/pages/dev.php'); break;
case 'story': include_once('inc/pages/story.php'); break;
case 'faq':	include_once('inc/pages/faq.php'); break;
default: header('Status: 404 Not Found', false, 404);
}
?></div>
<div class="footer"><strong>©</strong> idle-rpg created by <a href="mailto:&#119;&#105;&#110;&#064;&#119;&#097;&#114;&#114;&#105;&#111;&#114;&#104;&#111;&#117;&#115;&#101;&#046;&#110;&#101;&#116;">win</a> and <a href="mailto:&#115;&#104;&#105;&#119;&#097;&#110;&#103;&#064;&#111;&#114;&#097;&#110;&#103;&#101;&#046;&#102;&#114;">Shiwang</a> • All rights reserved • Since 2013. </div>
</body>
</html>
