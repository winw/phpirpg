<?php
 abstract class IrcCommands {
  public function pong($sText) {
   return $this->writeLine('PONG '.$sText);
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
   return $this->msg($sTarget, "\x01".$sMessage."\x01");
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
  
  abstract protected function writeLine($sLine);
 }
?>
