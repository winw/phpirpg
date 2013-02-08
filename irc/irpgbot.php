#!/usr/bin/php -ddisplay_errors=1; -dsafe_mode=0; -derror_reporting=-1
<?php
 set_time_limit(0);
 ignore_user_abort(false);

 define('BASE_PATH', __DIR__.'/');
 
 require_once 'inc/IrcEvents.interface.php';
 
 require_once 'inc/IrcCommands.class.php';
 
 require_once 'inc/Module.class.php';
 
 require_once 'inc/ModuleManager.class.php';

 require_once 'core.class.php';
 require_once 'irc.class.php';
 
 require_once 'inc/ChannelUsers.class.php';
 
 include 'modules/ModCore.php';

 require_once 'inc/mysqlman/dbDontEscapeString.class.php';
 require_once 'inc/mysqlman/dbInstance.class.php';
 require_once 'inc/mysqlman/dbClassGen.class.php';
 dbClassGen::$sPath = BASE_PATH.'_cache/db/';
 require_once 'inc/mysqlman/dbControl.class.php';
 require_once 'inc/mysqlman/dbObject.class.php';
 
 /* Création des instances pdo */
 $oPdo = new PDO('mysql:dbname=phpirpg;host=localhost', 'root', 'toor', Array(
  PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
 ));
 dbInstance::create('site', $oPdo);
 
 /* Generation des fichiers de bases de données si on est pas en prod */
 if (1) {
  foreach (dbInstance::getList() as $sName) {
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
 
 function debug($sLine) {
  echo '= '.$sLine."\n";
 }
 
 define('SERVER_IP', '83.140.172.210');
 define('SERVER_PORT', 6667);
 define('SERVER_NETSPLIT', '#^*.net *.split$#');
 
 define('IRPG_NICK', 'win');
 define('IRPG_USER', 'phpirpgbot');
 define('IRPG_DESCRIPTION', 'phpirpg');
 define('IRPG_CHANNEL', '#win');
 
 
 $oCore = new Core();
 $oIrc = new Irc($oCore, array(
  'nick' => IRPG_NICK,
  'user' => IRPG_USER,
  'description' => IRPG_DESCRIPTION,
  'channel' => IRPG_CHANNEL,
  'netsplit' => SERVER_NETSPLIT
 ));
 
 while (true) {
  try {
   if ($oCore->connect(array(
    'ip' => SERVER_IP,
    'port' => SERVER_PORT
   ))) {
    $oIrc->connected();
    for (; $oCore->isConnected(); usleep(1000)) {
     for (; ($oLine = $oCore->parseLine()) !== null; usleep(1000)) {
      $oIrc->parse($oLine);
     }
     
     $oIrc->tick();
    }
   }
  } catch (SocketException $e) {
   debug('Disconnected : '.$e->getMessage());
  }

  $oCore->disconnect();
  
  $oIrc->disconnected();
  
  sleep(30); // On attend 30s avant la reconnection
 }
?>
