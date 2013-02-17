<?php
 class ModMap extends Module {
  private $oMap;
  
  public function onLoad(){
   $this->oMap = new Map(BASE_PATH.'maps/map-quest');
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
