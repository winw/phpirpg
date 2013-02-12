<?php
 abstract class Module extends IrcCommands {
  private $oCore;
  private $oIrc;
  
  public final function __construct(Irc &$oIrc, Core &$oCore) {
   $this->oIrc = $oIrc;
   $this->oCore = $oCore;
  }
  
  protected function writeLine($sLine) {
   $this->oCore->writeLine($sLine);
  }
  
  public function getMyNick() {
   return $this->oIrc->aConfiguration['nick'];
  }
  
  public function getGameChannel() {
   return $this->oIrc->aConfiguration['channel'];
  }
  
  public function isChannel($sTarget) {
   return (in_array(substr($sTarget, 0, 1), $this->oIrc->aServerConfiguration['chantypes'], true));
  }
  
  public function isMe($sTarget) {
   return $this->getMyNick() === $sTarget;
  }
  
  public function isGameChannel($sChannel) {
   return $this->getGameChannel() === $sChannel;
  }
  
  abstract public function onLoad();
  abstract public function onMsg(ParsedMask $oWho, $sTarget, $sMessage);
  abstract public function onNotice(ParsedMask $oWho, $sTarget, $sMessage);
  abstract public function onCtcp(ParsedMask $oWho, $sTarget, $sMessage);
  abstract public function onAction(ParsedMask $oWho, $sTarget, $sMessage);
  abstract public function onCtcpReply(ParsedMask $oWho, $sTarget, $sMessage);
  abstract public function onWhoLine(ParsedMask $oWho, $sTarget, $sFlags, $sDescription);
  abstract public function onNamesLine($sChannel, array $aUsers);
  abstract public function onJoin(ParsedMask $oWho, $sChannel);
  abstract public function onPart(ParsedMask $oWho, $sChannel, $sMessage);
  abstract public function onKick(ParsedMask $oWho, $sChannel, $sToNick, $sMessage);
  abstract public function onQuit(ParsedMask $oWho, $sMessage);
  abstract public function onNick(ParsedMask $oWho, $sNewNick);
  abstract public function onRaw($iRaw, $sArguments);
  abstract public function onEndOfWho($sTarget);
 }
?>
