<?php
 class ModChannelUsers extends Module {
  private $bInWho = false;
  private $aWhoBuffer = array();
  private $oInstance;
  private $aiOldUsers = array();
  
  public function onLoad() {
   $this->oInstance = dbInstance::get('site');
  }

  public function onWhoLine(ParsedMask $oWho, $sTarget, $sFlags, $sDescription){
   if ($this->bInWho) {
    $this->aWhoBuffer[] = array($oWho, $sTarget);
   }
  }
  
  public function onJoin(ParsedMask $oWho, $sChannel){
   $oChannelUsers = new dbChannelUsers();
   
   $oChannelUser = $oChannelUsers->writable()->select('*, IF(date_autologin IS NULL, 0, DATE_ADD(date_autologin, INTERVAL 10 MINUTE) > NOW()) AS autologin')->where('channel = ? AND nick = ?', $sChannel, $oWho->getNick())->fetch();
   if (!$oChannelUser) {
    $oChannelUser = $oChannelUsers->create();
    $oChannelUser->channel = $sChannel;
    $oChannelUser->nick = $oWho->getNick();
    $oChannelUser->id_irpg_user = new dbDontEscapeString('NULL');
    $oChannelUser->date_autologin = new dbDontEscapeString('NULL');
   } else if ($this->isGameChannel($sChannel)) {
    if ($oChannelUser->autologin) {
     $oChannelUser->date_autologin = new dbDontEscapeString('NULL');
     ModuleManager::dispatch('doUserLogin', $oWho, $oChannelUser->id_irpg_user, true);
    }
   }
   $oChannelUser->user = $oWho->getUser();
   $oChannelUser->host = $oWho->getHost();
   $oChannelUser->save();
   
   if ($this->isMe($oWho->getNick())) {
    $this->bInWho = true;
    $this->who($sChannel);
   }
  }
  
  public function onPart(ParsedMask $oWho, $sChannel, $sMessage){
   $oChannelUsers = new dbChannelUsers();
   if ($oChannelUser = $oChannelUsers->writable()->select()->where('channel = ? AND nick = ?', $sChannel, $oWho->getNick())->fetch()) {
    $this->aiOldUsers[(string)$oWho] = (int)$oChannelUser->id_irpg_user;
    $oChannelUser->delete();
   }
  }
  
  public function onKick(ParsedMask $oWho, $sChannel, $sToNick, $sMessage){
   $oChannelUsers = new dbChannelUsers();
   if ($oChannelUser = $oChannelUsers->writable()->select()->where('channel = ? AND nick = ?', $sChannel, $oWho->getNick())->fetch()) {
    $this->aiOldUsers[(string)$oWho] = (int)$oChannelUser->id_irpg_user;
    $oChannelUser->delete();
   }
  }
  
  public function onQuit(ParsedMask $oWho, $sMessage){
   $oChannelUsers = new dbChannelUsers();
   if ($aoChannelUsers = $oChannelUsers->writable()->select()->where('nick = ?', $oWho->getNick())->fetchAll()) {
    foreach ($aoChannelUsers as $oChannelUser) {
     if ($this->isGameChannel($oChannelUser->channel)) {
      if ($sMessage == '*.net *.split' || $sMessage == 'registered') {
       $oChannelUser->date_autologin = new dbDontEscapeString('NOW()');
       $oChannelUser->save();
       continue;
      }
     }
     $this->aiOldUsers[(string)$oWho] = (int)$oChannelUser->id_irpg_user;
     $oChannelUser->delete();
    }
   }
  }
  
  public function onNick(ParsedMask $oWho, $sNewNick){
   $oChannelUsers = new dbChannelUsers();
   $aoChannelUsers = $oChannelUsers->writable()->select()->where('nick = ?', $oWho->getNick())->fetchAll();
   foreach ($aoChannelUser as $oChannelUser) {
    $oChannelUser->nick = $sNewNick;
    $oChannelUser->save();
   }
  }

  public function onEndOfWho($sTarget){ // Normalement c'est exécute qu'une fois lorsque le bot join le salon
   if ($this->bInWho) {
    $oChannelUsers = new dbChannelUsers();
    
    $aWasLoggedUsers = array();
    
    if ($aoChannelUsers = $oChannelUsers->select('CONCAT(nick, "!", user, "@", host) AS mask, id_irpg_user')->where('channel = ? AND NOT ISNULL(id_irpg_user) AND DATE_ADD(date_autologin, INTERVAL 10 MINUTE) > NOW()', $this->getGameChannel())->fetchAll()) {
     foreach ($aoChannelUsers as $oChannelUser) {
      $aWasLoggedUsers[(string)$oChannelUser->mask] = (int)$oChannelUser->id_irpg_user;
     }
    }

    $this->oInstance->exec('TRUNCATE TABLE channel_users;');

    foreach ($this->aWhoBuffer as $aWhoUser) {
     if ($sTarget == $aWhoUser[1]) { // On sait jamais
      $oWho = $aWhoUser[0];

      $oChannelUser = $oChannelUsers->create();
      
      $oChannelUser->channel = $sTarget;
      $oChannelUser->nick = $oWho->getNick();
      $oChannelUser->user = $oWho->getUser();
      $oChannelUser->host = $oWho->getHost();
      
      if (isset($aWasLoggedUsers[(string)$oWho])) { // Relog automatique
       $oChannelUser->id_irpg_user = $aWasLoggedUsers[(string)$oWho];
       $oChannelUser->date_autologin = new dbDontEscapeString('NULL');
       $oChannelUser->save();
       
       ModuleManager::dispatch('doUserLogin', $oWho, $aWasLoggedUsers[(string)$oWho], true);
      } else {
       $oChannelUser->id_irpg_user = new dbDontEscapeString('NULL');
       $oChannelUser->date_autologin = new dbDontEscapeString('NULL');
       $oChannelUser->save();
      }

     }
    }
    // A ce point, liste des utilisateurs présents sur le salon est à jour
    
    $this->aWhoBuffer = array();
    $this->bInWho = false;
    
    ModuleManager::dispatch('onUserListUpdated');
   }
  }
  
  public function getOldUserIdFromMask(ParsedMask $oWho) {
   if (isset($this->aiOldUsers[(string)$oWho])) {
    return $this->aiOldUsers[(string)$oWho];
   }
  }
  
  public function onMsg(ParsedMask $oWho, $sTarget, $sMessage) {}
  public function onCtcp(ParsedMask $oWho, $sTarget, $sMessage){}
  public function onNotice(ParsedMask $oWho, $sTarget, $sMessage){}
  public function onAction(ParsedMask $oWho, $sTarget, $sMessage){}
  public function onCtcpReply(ParsedMask $oWho, $sTarget, $sMessage){}
  public function onNamesLine($sChannel, array $aUsers){}
  public function onRaw($iRaw, $sArguments){}
 }
?>
