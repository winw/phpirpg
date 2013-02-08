<?php
 interface IrcEvents {
  public function onLoad();
  public function onMsg(ParsedMask $oWho, $sTarget, $sMessage);
  public function onWhoLine(ParsedMask $oWho, $sChannel, $sFlags, $sDescription);
  public function onJoin(ParsedMask $oWho, $sChannel);
  public function onPart(ParsedMask $oWho, $sChannel, $sMessage);
  public function onKick(ParsedMask $oWho, $sChannel, $sToNick, $sMessage);
  public function onQuit(ParsedMask $oWho, $sMessage);
  public function onNick(ParsedMask $oWho, $sNewNick);
 }
?>
