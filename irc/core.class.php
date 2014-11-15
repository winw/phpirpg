<?php
 class ArgumentException extends \Exception {
 }
 
 class SocketException extends \Exception {
 }
 
 class Core {
  private $aConfiguration = array();
  private $rSocket;
  
  public function connect(array $aConfiguration) {
   if ($this->isConnected()) {
    throw new Exception('Already connected to '.$this->aConfiguration['ip']);
   }
   
   if (!isset($aConfiguration['ip'])) {
    throw new ArgumentException();
   }
   
   if (!isset($aConfiguration['port'])) {
    $aConfiguration['port'] = 6667;
   }
   
   $this->aConfiguration = $aConfiguration;
   
   return $this->doConnect();
  }
  
  public function disconnect() {
   if (!$this->isConnected()) {
    throw new Exception('Not connected');
   }
   
   if ($this->rSocket !== null) {
    fclose($this->rSocket);
    $this->rSocket = null;
   }
  }
  
  private function doConnect() {
   $rSocket = fsockopen($this->aConfiguration['ip'], $this->aConfiguration['port'], $iErrno, $sErrstr, 5);
   if (!$rSocket) {
    throw new SocketException($sErrstr);
   }
   
   stream_set_blocking($rSocket, 0);
   
   $this->rSocket = $rSocket;
   
   return $this->isConnected();
  }
  
  public function parseLine() {
   $sLine = $this->readLine();

   if ($sLine) {
    if (preg_match('/^PING (.*)$/i', $sLine, $aRegs)) {
     return new ParsedLine($sLine, 'PING', array($aRegs[1]));
    } else if (preg_match('/^:([^ ]+) JOIN (?:\:)?([^ ]+)/', $sLine, $aRegs)) {
     return new ParsedLine($sLine, 'JOIN', array($aRegs[1], $aRegs[2]));
    } else if (preg_match('/^:([^ ]+) PART ([^ ]+)(?:\s:)?(.*)?$/', $sLine, $aRegs)) {
     return new ParsedLine($sLine, 'PART', array($aRegs[1], $aRegs[2], $aRegs[3]));
    } else if (preg_match('/^:([^ ]+) PRIVMSG ([^ ]+) :(.*)$/', $sLine, $aRegs)) {
    
     if (preg_match("/^\x01ACTION (.*)\x01$/", $aRegs[3], $aRegs2)) {
      return new ParsedLine($sLine, 'ACTION', array($aRegs[1], $aRegs[2], $aRegs2[1]));
     } else if (preg_match("/^\x01(.*)\x01$/", $aRegs[3], $aRegs2)) {
      return new ParsedLine($sLine, 'CTCP', array($aRegs[1], $aRegs[2], $aRegs2[1]));
     } else {
      return new ParsedLine($sLine, 'MSG', array($aRegs[1], $aRegs[2], $aRegs[3]));
     }
     
    } else if (preg_match('/^:([^ ]+) NOTICE ([^ ]+) :(.*)$/', $sLine, $aRegs)) {
    
     if (preg_match("/^\x01(.*)\x01$/", $aRegs[3], $aRegs2)) {
      return new ParsedLine($sLine, 'CTCPREPLY', array($aRegs[1], $aRegs[2], $aRegs2[1]));
     } else {
      return new ParsedLine($sLine, 'NOTICE', array($aRegs[1], $aRegs[2], $aRegs[3]));
     }
     
    } else if (preg_match('/^:([^ ]+) KICK ([^ ]+) ([^ ]+)(?:\s:)?(.*)?$/', $sLine, $aRegs)) { //>> :win!~win@warriorhouse.net KICK #win Shiwang :TRAITRE
     return new ParsedLine($sLine, 'KICK', array($aRegs[1], $aRegs[2], $aRegs[3], $aRegs[4]));
    } else if (preg_match('/^:([^ ]+) NICK :([^ ]+)/', $sLine, $aRegs)) { //:win51!~phpirpgbo@par95-2-78-213-76-33.fbx.proxad.net NICK :phpirpgbot
     return new ParsedLine($sLine, 'NICK', array($aRegs[1], $aRegs[2]));
    } else if (preg_match('/^:([^ ]+) QUIT(?:\s:?)(.*)?$/', $sLine, $aRegs)) {
     return new ParsedLine($sLine, 'QUIT', array($aRegs[1], $aRegs[2]));
    } else if (preg_match('/^[^ ]+ (\d+)(?:\s)?(.*)/', $sLine, $aRegs)) {
     return new ParsedLine($sLine, 'RAW', array($aRegs[1], $aRegs[2]));
    } else {
     return new ParsedLine($sLine, 'UNKNOWN');
    }
   }
  }
  
  public function writeLine($sLine) {
   if (!$this->isConnected()) {
    throw new SocketException('Not connected');
   }
   return fputs($this->rSocket, substr($sLine, 0, 510)."\r\n");
  }
  
  public function readLine() {
   if (!$this->isConnected()) {
    throw new SocketException('Not connected');
   }
   return trim(fgets($this->rSocket, 512));
  }
  
  public function isConnected() {
   return ($this->rSocket && !feof($this->rSocket));
  }
 }