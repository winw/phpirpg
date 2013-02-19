<?php
 class ModMap extends Module {
  private $oMap;
  
  public function onLoad(){
   $this->oMap = new Map(BASE_PATH.'maps/map-quest');
   
   $oTimer = new Timer(10, 0, function(){
    $oIrpgUsers = new dbIrpgUsers();
    $aoIrpgUsers = $oIrpgUsers->writable()->select('id, x, y')->where('irpg_users.id IN (SELECT channel_users.id_irpg_user FROM channel_users WHERE id IS NOT NULL)')->fetchAll();
    
    foreach ($aoIrpgUsers as &$oIrpgUser) {
     $iRandX = rand(-2, 2); // Ajuster en fonction des stats du personnage
     $iRandY = rand(-2, 2); // Idem
     
     $iNewX = ($oIrpgUser->x + $iRandX) % $this->oMap->getWidth();
     if ($iNewX < 0) {
      $iNewX = $this->oMap->getWidth() + $iNewX;
     }
     
     $iNewY = ($oIrpgUser->y + $iRandY) % $this->oMap->getHeight();
     if ($iNewY < 0) {
      $iNewY = $this->oMap->getHeight() + $iNewY;
     }
     
     $oIrpgUser->x = $iNewX;
     $oIrpgUser->y = $iNewY;
     
     $oIrpgUser->save();
    }
   });
   
   TimerManager::add(__CLASS__.'move', $oTimer);
  }
  
  public function onUserMove(ParsedMask $oWho, $iIrpgUserId, $iX, $iY, $iNewX, $iNewY) {
   
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
