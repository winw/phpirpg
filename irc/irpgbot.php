#!../php/php -ddisplay_errors=1; -dsafe_mode=0; -derror_reporting=-1
<?php
 if (version_compare(PHP_VERSION, '5.4.0', '<')) {
  die("php >= 5.4.0 is required\n");
 }
 set_time_limit(0);
 ignore_user_abort(false);
 date_default_timezone_set('Europe/Paris');

 define('BASE_PATH', __DIR__.'/');
 
 require_once 'config.php';
 
 require_once 'inc/Utils.class.php';
 require_once 'inc/ParsedLine.class.php';
 require_once 'inc/IrcCommands.class.php';
 require_once 'inc/ParsedMask.class.php';
 require_once 'inc/Map.class.php';
 require_once 'inc/Module.class.php';
 require_once 'inc/Timer.class.php';
 require_once 'inc/TimerManager.class.php';
 require_once 'inc/ModuleManager.class.php';

 require_once 'core.class.php';
 require_once 'irc.class.php';

 require_once 'inc/mysqlman/dbDontEscapeString.class.php';
 require_once 'inc/mysqlman/dbPrimitiveObject.class.php';
 require_once 'inc/mysqlman/dbInstance.class.php';
 require_once 'inc/mysqlman/dbClassGen.class.php';
 dbClassGen::$sPath = BASE_PATH.'_cache/db/';
 require_once 'inc/mysqlman/dbControl.class.php';
 require_once 'inc/mysqlman/dbObject.class.php';
 
 /* Création des instances pdo */
 $oPdo = new PDO(DB_DSN, DB_LOGIN, DB_PASSWORD, Array(
  PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
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
    for ($oIrc->connected(); $oCore->isConnected(); usleep(10000)) { // 1/100ème de seconde
     for (; ($oLine = $oCore->parseLine()) !== null; usleep(10000)) { // Lecture de 100lignes/seconde
      $oIrc->parse($oLine);
     }

     TimerManager::tick();
    }
   }
  } catch (SocketException $e) {
   debug('Disconnected : '.$e->getMessage());
  }

  $oCore->disconnect();
  
  $oIrc->disconnected();
  
  TimerManager::clear();
  
  sleep(30); // On attend 30s avant la reconnection
 }
?>
