<?php
 class ModMap extends Module {
  private $oMap;
  
  const MOVE_PLAYER_DELAY = 10; // Mettre 5*60 ensuite
  
  public function onLoad(){
   $this->oMap = new Map(BASE_PATH.'maps/map-quest');
   
   $oTimer = new Timer(self::MOVE_PLAYER_DELAY, 0, function(){
    $this->doMoveUsers();
   });
   
   TimerManager::add(__CLASS__.'doMoveUsers', $oTimer);
  }
  
  public function onUserRegister(ParsedMask $oWho, $iIdIrpgUser) {
   $oIrpgUsers = new dbIrpgUsers();
   
   // Initialize random x / y on user register
   if ($oIrpgUser = $oIrpgUsers->writable()->select('id')->where('id = ?', $iIdIrpgUser)->fetch()) {
    $oIrpgUser->x = rand(0, $this->oMap->getWidth()-1);
    $oIrpgUser->y = rand(0, $this->oMap->getHeight()-1);
    $oIrpgUser->save();
   }
  }
  
  private function doMoveUsers() {
   $oIrpgUsers = new dbIrpgUsers();
   $aoIrpgUsers = $oIrpgUsers->writable()->select('id, x, y')->where('irpg_users.id IN (SELECT channel_users.id_irpg_user FROM channel_users WHERE channel_users.id_irpg_user IS NOT NULL)')->fetchAll();
   
   foreach ($aoIrpgUsers as &$oIrpgUser) {
    $iRandX = rand(-2, 2); // Ajuster en fonction des stats du personnage
    $iRandY = rand(-2, 2); // Idem
    
    $iOldX = (int)$oIrpgUser->x;
    $iOldY = (int)$oIrpgUser->y;
    
    $iNewX = ($oIrpgUser->x + $iRandX) % $this->oMap->getWidth();
    if ($iNewX < 0) {
     $iNewX += $this->oMap->getWidth();
    }
    
    $iNewY = ($oIrpgUser->y + $iRandY) % $this->oMap->getHeight();
    if ($iNewY < 0) {
     $iNewY += $this->oMap->getHeight();
    }
    
    $oIrpgUser->x = $iNewX;
    $oIrpgUser->y = $iNewY;
    
    $oIrpgUser->save();
    
    ModuleManager::dispatch('onUserMove', $oIrpgUser->id, $iOldX, $iOldY, $iNewX, $iNewY);
   }
  }
  
  public function getZone($iX, $iY) {
   return $this->oMap->getZone($iX, $iY);
  }
  
  public function getMapWidth() {
   return $this->oMap->getWidth();
  }
  
  public function getMapHeight() {
   return $this->oMap->getHeight();
  }
  
  public function onCtcp(ParsedMask $oWho, $sTarget, $sMessage){}
  public function onMsg(ParsedMask $oWho, $sTarget, $sMessage){
   if (preg_match('/^!z (\d+) (\d+)$/', $sMessage, $aRegs)) {
    $this->msg($sTarget, 'zone :'. $this->oMap->getZone($aRegs[1], $aRegs[2]));
   }
  }
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
 }
?>
