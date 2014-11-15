<?php
 class ModPenalties extends Module {
  const REASON_MSG = 1;
  const REASON_PART = 2;
  const REASON_QUIT = 3;
  const REASON_NOTICE = 4;
  const REASON_KICK = 5;
  const REASON_CTCP = 6;
  const REASON_NICK = 7;
  const REASON_ACTION = 8;
  const REASON_LOGOUT = 9;
 
  public function onLoad(){}
  
  public function getPenaltiesTime($iLevel, $iNb) {
   return $iNb * pow(Configuration::BASE_MULTIPLICATOR, $iLevel);
  }
  
  private function doPenaltie($iIdIrpgUser, $iNb, $iReason) {
   $oIrpgUsers = new dbIrpgUsers();
   
   if ($oIrpgUser = $oIrpgUsers->writable()->select()->where('id = ?', $iIdIrpgUser)->fetch()) {
    $iPenalties = self::getPenaltiesTime($oIrpgUser->level, $iNb);
    $oIrpgUser->time_to_level += $iPenalties;
    $oIrpgUser->save();
    
    $sMessage = '[penalties] '.Utils::duration($iPenalties).' is added to '.$oIrpgUser->login."'s clock for ";
    switch ($iReason) {
     case self::REASON_MSG: $sMessage .= 'channel message'; break;
     case self::REASON_PART: $sMessage .= 'parting'; break;
     case self::REASON_QUIT: $sMessage .= 'quitting'; break;
     case self::REASON_NOTICE: $sMessage .= 'channel notice'; break;
     case self::REASON_KICK: $sMessage .= 'being kicked'; break;
     case self::REASON_CTCP: $sMessage .= 'channel ctcp'; break;
     case self::REASON_NICK: $sMessage .= 'nick change'; break;
     case self::REASON_ACTION: $sMessage .= 'channel action'; break;
     case self::REASON_LOGOUT: $sMessage .= 'logout'; break;
     default:
      $sMessage .= '??';
    }
    
    $sMessage .= ', next level in '.Utils::duration($oIrpgUser->time_to_level);
    
    $this->msg($this->getGameChannel(), $sMessage);
   }
  }
  
  public function onUserLogout(ParsedMask $oWho, $iIdIrpgUser) {
   $this->doPenaltie($iIdIrpgUser, 40, self::REASON_LOGOUT);
  }

  public function onCtcp(ParsedMask $oWho, $sTarget, $sMessage){
   if ($this->isGameChannel($sTarget) && ($iIdIrpgUser = $this->getUserIdFromMask($oWho))) {
    $this->doPenaltie($iIdIrpgUser, 100+strlen($sMessage), self::REASON_CTCP);
   }
  }
  public function onMsg(ParsedMask $oWho, $sTarget, $sMessage){
   if ($this->isGameChannel($sTarget) && ($iIdIrpgUser = $this->getUserIdFromMask($oWho))) {
    $this->doPenaltie($iIdIrpgUser, 50+strlen($sMessage), self::REASON_MSG);
   }
  }
  public function onPart(ParsedMask $oWho, $sChannel, $sMessage){
   if ($this->isGameChannel($sChannel) && ($iIdIrpgUser = ModuleManager::dispatchTo('ModChannelUsers', 'getOldUserIdFromMask', $oWho))) {
    $this->doPenaltie($iIdIrpgUser, 100+strlen($sMessage), self::REASON_PART);
   }
  }
  public function onKick(ParsedMask $oWho, $sChannel, $sToNick, $sMessage){
   if ($this->isGameChannel($sChannel)) {
    $oMask = $this->getMaskFromNick($sToNick);
    if ($oMask && ($iIdIrpgUser = ModuleManager::dispatchTo('ModChannelUsers', 'getOldUserIdFromMask', $oMask))) {
     $this->doPenaltie($iIdIrpgUser, 200, self::REASON_KICK);
    }
   }
  }
  public function onQuit(ParsedMask $oWho, $sMessage){
   if ($iIdIrpgUser = ModuleManager::dispatchTo('ModChannelUsers', 'getOldUserIdFromMask', $oWho)) {
    $this->doPenaltie($iIdIrpgUser, 50, self::REASON_QUIT);
   }
  }
  public function onNick(ParsedMask $oWho, $sNewNick){
   if ($iIdIrpgUser = $this->getUserIdFromNick($sNewNick)) {
    $this->doPenaltie($iIdIrpgUser, 50, self::REASON_NICK);
   }
  }
  public function onNotice(ParsedMask $oWho, $sTarget, $sMessage){
   if ($this->isGameChannel($sTarget) && ($iIdIrpgUser = $this->getUserIdFromMask($oWho))) {
    $this->doPenaltie($iIdIrpgUser, 100+strlen($sMessage), self::REASON_NOTICE);
   }
  }
  public function onAction(ParsedMask $oWho, $sTarget, $sMessage){
   if ($this->isGameChannel($sTarget) && ($iIdIrpgUser = $this->getUserIdFromMask($oWho))) {
    $this->doPenaltie($iIdIrpgUser, 50+strlen($sMessage), self::REASON_ACTION);
   }
  }
  public function onCtcpReply(ParsedMask $oWho, $sTarget, $sMessage){
   // Dilemme :)
  }
  
  public function onWhoLine(ParsedMask $oWho, $sTarget, $sFlags, $sDescription){}
  public function onJoin(ParsedMask $oWho, $sChannel){}
  public function onNamesLine($sChannel, array $aUsers){}
  public function onRaw($iRaw, $sArguments){}
  public function onEndOfWho($sTarget){}
  public function onUnload(){}
 }
