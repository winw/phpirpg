<?php
 class ModBattle extends Module {
  const HIT_ZONE = 2; // Zone autour du joueur pour qu'une rencontre soit possible
  
  const REASON_ENCOUNTER = 1;
  
  public function onLoad(){}
  
  public function onUserMove($iIrpgUserId, $iOldX, $iOldY, $iNewX, $iNewY) {
   if (rand(0,1)) { // Une chance sur 2 de batailler
    $aXCoords = array($iNewX);
    $aYCoords = array($iNewY);
    
    $iMapWidth = ModuleManager::dispatchTo('ModMap', 'getMapWidth');
    $iMapHeight = ModuleManager::dispatchTo('ModMap', 'getMapHeight');
    
    for ($i = 1; $i < self::HIT_ZONE; ++$i) {
     $aXCoords[] = ($iNewX+$i) % $iMapWidth;
     $iX = $iNewX - $i;
     if ($iX < 0) {
      $iX += $iMapWidth;
     }
     $aXCoords[] = $iX;
     $aYCoords[] = ($iNewY+$i) % $iMapHeight;
     $iY = $iNewY - $i;
     if ($iY < 0) {
      $iY += $iMapHeight;
     }
     $aYCoords[] = $iY;
    }

    $oIrpgUsers = new dbIrpgUsers();
    // On recherche un autre joueur à proximité
    $oIrpgUser = $oIrpgUsers->select()->where('irpg_users.id IN (SELECT channel_users.id_irpg_user FROM channel_users WHERE channel_users.id_irpg_user IS NOT NULL AND channel_users.id_irpg_user != ?) AND irpg_users.x IN ('.implode(',', array_unique($aXCoords)).') AND irpg_users.y IN ('.implode(',', array_unique($aYCoords)).')', $iIrpgUserId)->order('RAND()')->fetch();
    
    if ($oIrpgUser) {
     $this->doBattle($iIrpgUserId, $oIrpgUser->id, self::REASON_ENCOUNTER);
    }
   }
  }
  
  private function doBattle($iIrpgUserIdFrom, $iIrpgUserIdTo, $iReason) {
   if ($iReason == self::REASON_ENCOUNTER) {
    $this->msg($this->getGameChannel(), '[battle] entre #'.$iIrpgUserIdFrom.' et #'.$iIrpgUserIdTo);
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
 }
?>
