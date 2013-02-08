<?php
 class ModCore extends Module implements IrcEvents {
  public function onLoad() {
   echo "hello, i am loaded !\n";
  }
  
  public function onMsg(ParsedMask $oWho, $sTarget, $sMessage) {
   if ($sMessage == '!x') {
    ChannelUsers::debug();
   } else if ($sMessage == '!y') {
    $this->msg($sTarget, 'coucou !');
   }
  }
  
  public function onWhoLine(ParsedMask $oWho, $sChannel, $sFlags, $sDescription){}
  public function onJoin(ParsedMask $oWho, $sChannel){}
  public function onPart(ParsedMask $oWho, $sChannel, $sMessage){}
  public function onKick(ParsedMask $oWho, $sChannel, $sToNick, $sMessage){}
  public function onQuit(ParsedMask $oWho, $sMessage){}
  public function onNick(ParsedMask $oWho, $sNewNick){}
 }
?>
