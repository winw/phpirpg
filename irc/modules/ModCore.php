<?php
 class ModCore extends Module {
  private function isLogged(ParsedMask $oWho) {
   return $this->getUserId($oWho) != 0;
  }
  
  private function getUserId(ParsedMask $oWho) {
   $oChannelUsers = new dbChannelUsers();
   if ($oChannelUser = $oChannelUsers->select('id_irpg_user')->where('channel = ? AND nick = ? AND user = ? AND host = ?', $this->getGameChannel(), $oWho->getNick(), $oWho->getUser(), $oWho->getHost())->fetch()) {
    return (int)$oChannelUser->id_irpg_user;
   } else {
    return 0;
   }
  }
  
  public function onCtcp(ParsedMask $oWho, $sTarget, $sMessage){
   if (!strcasecmp($sMessage, 'VERSION')) {
    $this->ctcpReply($oWho->getNick(), 'VERSION phpirpg beta');
   }
  }
  
  public function onLoad(){}
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
