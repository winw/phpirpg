<?php
 abstract class IrcCommands {
  public function pong($sText) {
   return $this->writeLineNow('PONG '.$sText);
  }
  
  public function join($sChannel) {
   return $this->writeLine('JOIN '.$sChannel);
  }
  
  public function part($sChannel, $sMessage = '') {
   return $this->writeLine('PART '.$sChannel.' :'.$sMessage);
  }
  
  public function msg($sTarget, $sMessage) {
   return $this->writeLine('PRIVMSG '.$sTarget.' :'.$sMessage);
  }
  
  public function action($sTarget, $sMessage) {
   return $this->ctcp($sTarget, 'ACTION '.$sMessage);
  }
  
  public function notice($sTarget, $sMessage) {
   return $this->writeLine('NOTICE '.$sTarget.' :'.$sMessage);
  }
  
  public function nick($sNick) {
   return $this->writeLine('NICK '.$sNick);
  }
  
  public function who($sTarget, $sFlags = '') {
   return $this->writeLine('WHO '.rtrim($sTarget.' '.$sFlags));
  }
  
  public function quit($sMessage = '') {
   return $this->writeLine('QUIT :'.$sMessage);
  }
  
  public function ctcp($sTarget, $sMessage) {
   return $this->msg($sTarget, "\x01".$sMessage."\x01");
  }
  
  public function ctcpReply($sTarget, $sMessage) {
   return $this->notice($sTarget, "\x01".$sMessage."\x01");
  }
  
  public function mode($sTarget, $sModes) {
   return $this->writeLine('MODE '.$sTarget.' '.$sModes);
  }
  
  public function op($sChannel, $sNick) {
   $this->mode($sChannel, '+o '.$sNick);
  }
  
  public function deop($sChannel, $sNick) {
   $this->mode($sChannel, '-o '.$sNick);
  }
  
  abstract protected function writeLine($sLine);
  abstract protected function writeLineNow($sLine);
 }
?>
