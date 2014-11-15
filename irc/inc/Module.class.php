<?php
 abstract class Module extends IrcCommands {
  protected $oCore;
  protected $oIrc;
  private $aCache = array();
  
  public final function __construct(Irc &$oIrc, Core &$oCore) {
   $this->oIrc = $oIrc;
   $this->oCore = $oCore;
  }
  
  protected function writeLine($sLine) {
   $this->oIrc->writeLine(str_replace("\n", ' -- ',$sLine));
  }
  
  protected function writeLineNow($sLine) {
   $this->oIrc->writeLineNow(str_replace("\n", ' -- ',$sLine));
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

  public function isCached($sKey) {
   if (isset($this->aCache[$sKey]) && (time() > $this->aCache[$sKey][0])) {
    unset($this->aCache[$sKey]);
   }
   return isset($this->aCache[$sKey]);
  }
  
  public function getCache($sKey) {
   return $this->isCached($sKey) ? $this->aCache[$sKey][1] : null;
  }
  
  public function setCache($sKey, $mValue, $iCacheTime) {
   $this->aCache[$sKey] = array(time()+$iCacheTime, $mValue);
  }
  public function getUserIdFromMask(ParsedMask $oWho) {
   $oChannelUsers = new dbChannelUsers();
   if ($oChannelUser = $oChannelUsers->select('id_irpg_user')->where('channel = ? AND nick = ? AND user = ? AND host = ? AND id_irpg_user IS NOT NULL', $this->getGameChannel(), $oWho->getNick(), $oWho->getUser(), $oWho->getHost())->fetch()) {
    return (int)$oChannelUser->id_irpg_user;
   }
  }
  
  public function getUserIdFromNick($sNick) {
   $oChannelUsers = new dbChannelUsers();
   if ($oChannelUser = $oChannelUsers->select('id_irpg_user')->where('channel = ? AND nick = ? AND id_irpg_user IS NOT NULL', $this->getGameChannel(), $sNick)->fetch()) {
    return (int)$oChannelUser->id_irpg_user;
   }
  }
  
  public function getMaskFromNick($sNick) {
   $oChannelUsers = new dbChannelUsers();
   if ($oChannelUser = $oChannelUsers->select('CONCAT(nick, "!", user, "@", host) AS mask')->where('nick = ?', $sNick)->fetch()) {
    return new ParsedMask((string)$oChannelUser->mask);
   }
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
  abstract public function onUnload();
 }
