<?php
 class ModLevel extends Module {
  const CHECK_LEVEL = 5; // Check level every X seconds
  const BASE_TIME = 60;
  
  public function onLoad(){
   $oTimer = new Timer(self::CHECK_LEVEL, 0, function() {
    $this->doCheckLevels();
   });
   TimerManager::add(__CLASS__.'checkLevels', $oTimer);
  }
  
  private function calculateTimeToLevel($iLevel) {
   return intval(self::BASE_TIME * pow(M_PI_2, $iLevel));
  }

  public function onUserRegister(ParsedMask $oWho, $iIdIrpgUser) {
   $oIrpgUsers = new dbIrpgUsers();
   
   // Initialize level related times
   if ($oIrpgUser = $oIrpgUsers->writable()->select('id')->where('id = ?', $iIdIrpgUser)->fetch()) {
    $oIrpgUser->level = 1;
    $oIrpgUser->time_to_level = self::calculateTimeToLevel($oIrpgUser->level);
    $oIrpgUser->save();
   }
  }
  
  private function doCheckLevels() {
   $oIrpgUsers = new dbIrpgUsers();
   $aoIrpgUser = $oIrpgUsers->select()->writable()->where('irpg_users.id IN (SELECT channel_users.id_irpg_user FROM channel_users WHERE channel_users.id_irpg_user IS NOT NULL)')->order('RAND()')->fetchAll();
   foreach ($aoIrpgUser as &$oIrpgUser) {
    $iTimeToLevel = $oIrpgUser->time_to_level - self::CHECK_LEVEL;
    $oIrpgUser->time_idled += self::CHECK_LEVEL;
    
    if ($iTimeToLevel > 0) {
     $oIrpgUser->time_to_level = $iTimeToLevel;
     $oIrpgUser->save();
    } else { // Level up
     $oIrpgUser->level++;
     $oIrpgUser->time_to_level = self::calculateTimeToLevel($oIrpgUser->level);
     $oIrpgUser->save();
     $this->msg($this->getGameChannel(), '[level up] '.$oIrpgUser->login.' has attained level '.$oIrpgUser->level.'. Next level in '.Utils::duration($oIrpgUser->time_to_level));
     ModuleManager::dispatch('onUserLevelUp', (int)$oIrpgUser->id, (int)$oIrpgUser->level, (int)$oIrpgUser->time_to_level);
    }
   }
  }
  
  public function onCtcp(ParsedMask $oWho, $sTarget, $sMessage){}
  public function onMsg(ParsedMask $oWho, $sTarget, $sMessage){}
  public function onWhoLine(ParsedMask $oWho, $sTarget, $sFlags, $sDescription){}
  public function onJoin(ParsedMask $oWho, $sChannel){}
  public function onPart(ParsedMask $oWho, $sChannel, $sMessage){}
  public function onKick(ParsedMask $oWho, $sChannel, $sToNick, $sMessage){}
  public function onQuit(ParsedMask $oWho, $sMessage){}
  public function onNick(ParsedMask $oWho, $sNewNick){}
  public function onNotice(ParsedMask $oWho, $sTarget, $sMessage){}
  public function onAction(ParsedMask $oWho, $sTarget, $sMessage){}
  public function onCtcpReply(ParsedMask $oWho, $sTarget, $sMessage){}
  public function onNamesLine($sChannel, array $aUsers){}
  public function onRaw($iRaw, $sArguments){}
  public function onEndOfWho($sTarget){}
  public function onUnload(){
   if (TimerManager::exists(__CLASS__.'checkLevels')) {
    TimerManager::del(__CLASS__.'checkLevels');
   }
  }
 }