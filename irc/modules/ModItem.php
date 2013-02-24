<?php
 class ModItem extends Module {
  public function onLoad(){}
  
  private function calculateItemLevel($iLevel) {
   for ($i = $iLevel * M_PI_2; $i > 1; --$i) {
    if (rand(1, pow(M_PI_2, $i / 5)) == 1) {
     return round($i);
    }
   }
   
   return 1;
  }

  public function onUserLevelUp($iIrpgUserId, $iNewLevel, $iTimeToNextLevel) {
   $this->msg($this->getGameChannel(), '#'.$iIrpgUserId.' found a level '.self::calculateItemLevel($iNewLevel).' item.');
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
