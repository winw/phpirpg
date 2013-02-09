<?php
 class ModChannelUsers extends Module {
  private $bInWho = false;
  private $aWhoBuffer = array();
  private $oInstance;
  
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
   } else if ($sChannel == $this->getGameChannel()) {
    if ($oChannelUser->autologin) {
     // Auto login user
     $oChannelUser->date_autologin = new dbDontEscapeString('NULL');
    }
   }
   $oChannelUser->user = $oWho->getUser();
   $oChannelUser->host = $oWho->getHost();
   $oChannelUser->save();
   
   if ($oWho->getNick() === $this->getMyNick()) {
    $this->bInWho = true;
    $this->who($sChannel);
   }
  }
  
  public function onPart(ParsedMask $oWho, $sChannel, $sMessage){
   $oChannelUsers = new dbChannelUsers();
   if ($oChannelUser = $oChannelUsers->writable()->select()->where('channel = ? AND nick = ?', $sChannel, $oWho->getNick())->fetch()) {
    $oChannelUser->delete();
   }
  }
  
  public function onKick(ParsedMask $oWho, $sChannel, $sToNick, $sMessage){
   $oChannelUsers = new dbChannelUsers();
   if ($oChannelUser = $oChannelUsers->writable()->select()->where('channel = ? AND nick = ?', $sChannel, $oWho->getNick())->fetch()) {
    $oChannelUser->delete();
   }
  }
  
  public function onQuit(ParsedMask $oWho, $sMessage){
   $oChannelUsers = new dbChannelUsers();
   if ($aoChannelUsers = $oChannelUsers->writable()->select()->where('nick = ?', $oWho->getNick())->fetchAll()) {
    foreach ($aoChannelUsers as $oChannelUser) {
     if ($oChannelUser->channel == $this->getGameChannel()) {
      if ($sMessage == '*.net *.split' || $sMessage == 'registered') {
       $oChannelUser->date_autologin = new dbDontEscapeString('NOW()');
       $oChannelUser->save();
       continue;
      }
     }
     $oChannelUser->delete();
    }
   }
  }
  
  public function onNick(ParsedMask $oWho, $sNewNick){
   $oChannelUsers = new dbChannelUsers();
   if ($oChannelUser = $oChannelUsers->writable()->select()->where('channel = ? AND nick = ?', $sChannel, $oWho->getNick())->fetch()) {
    $oChannelUser->nick = $sNewNick();
    $oChannelUser->save();
   }
  }

  public function onEndOfWho($sTarget){
   if ($this->bInWho) {
    $oChannelUsers = new dbChannelUsers();
    
    $aWasLoggedUsers = array();
    
    if ($aoChannelUsers = $oChannelUsers->select('CONCAT(nick, "!", user, "@", host) AS mask, id_irpg_user')->where('channel = ? AND NOT ISNULL(id_irpg_user) AND (ISNULL(date_autologin) OR DATE_ADD(date_autologin, INTERVAL 10 MINUTE) > NOW())', $this->getGameChannel())->fetchAll()) {
     foreach ($aoChannelUsers as $oChannelUser) {
      $aWasLoggedUsers[(string)$oChannelUser->mask] = (int)$oChannelUser->id_irpg_user;
     }
    }
    
    $this->oInstance->exec('TRUNCATE TABLE channel_users;');
    
    foreach ($this->aWhoBuffer as $aWhoUser) {
     if ($sTarget === $aWhoUser[1]) { // On sait jamais
      $oWho = $aWhoUser[0];

      $oChannelUser = $oChannelUsers->create();
      
      $oChannelUser->channel = $sTarget;
      $oChannelUser->nick = $oWho->getNick();
      $oChannelUser->user = $oWho->getUser();
      $oChannelUser->host = $oWho->getHost();
      
      if (isset($aWasLoggedUser[$oWho])) { // Relog automatique
       $oChannelUser->id_irpg_user = $aWasLoggedUser[$oWho];
      }
      
      $oChannelUser->save();
     }
    }
    // A ce point, liste des utilisateurs présents sur le salon est à jour
    
    $this->aWhoBuffer = array();
    $this->bInWho = false;
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
