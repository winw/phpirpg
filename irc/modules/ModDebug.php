<?php
 class ModDebug extends Module {
  public function onLoad() {
   echo "hello, i am loaded !\n";
  }
  
  public function onMsg(ParsedMask $oWho, $sTarget, $sMessage) {
   if ($sMessage == '!x') {
    ChannelUsers::debug();
   } else if ($sMessage == '!y') {
    $this->msg($sTarget, 'coucou !');
   } else if ($sMessage == '!q') {
    $this->quit('Restart ?');
   }
  }
  
  public function onCtcp(ParsedMask $oWho, $sTarget, $sMessage){
   if (!strcasecmp($sMessage, 'VERSION')) {
    $this->ctcpReply($oWho->getNick(), 'VERSION phpirpg beta');
   }
  }
  
  public function onWhoLine(ParsedMask $oWho, $sTarget, $sFlags, $sDescription){}
  public function onJoin(ParsedMask $oWho, $sChannel){
   $this->msg($sChannel, '[join] nick : '.$oWho->getNick().' || user : '.$oWho->getUser().' || host : '.$oWho->getHost());
  }
  public function onPart(ParsedMask $oWho, $sChannel, $sMessage){
   $this->msg($sChannel, '[part] nick : '.$oWho->getNick().' || user : '.$oWho->getUser().' || host : '.$oWho->getHost().' || message : '.$sMessage);
  }
  public function onKick(ParsedMask $oWho, $sChannel, $sToNick, $sMessage){
   $this->msg($sChannel, '[kick] nick : '.$oWho->getNick().' || user : '.$oWho->getUser().' || host : '.$oWho->getHost().' || to : '.$sToNick.' || message : '.$sMessage);
  }
  public function onQuit(ParsedMask $oWho, $sMessage){
   $this->msg($this->aConfiguration['channel'], '[quit] nick : '.$oWho->getNick().' || user : '.$oWho->getUser().' || host : '.$oWho->getHost().' || message : '.$sMessage);
  }
  public function onNick(ParsedMask $oWho, $sNewNick){}
  public function onNotice(ParsedMask $oWho, $sTarget, $sMessage){}
  public function onAction(ParsedMask $oWho, $sTarget, $sMessage){}
  public function onCtcpReply(ParsedMask $oWho, $sTarget, $sMessage){}
  public function onNamesLine($sChannel, array $aUsers){print_r(func_get_args());}
  public function onRaw($iRaw, $sArguments){}
  public function onEndOfWho($sTarget){}
 }
?>
