<?php
 class ArgumentException extends \Exception {
 }
 
 class SocketException extends \Exception {
 }
 
 class parsedLine implements ArrayAccess, Countable  {
  private $sType = '';
  private $aData = array();
  private $sRaw = '';
  private $iNbData = 0;
  
  public function __construct($sRaw, $sType, array $aData = array()) {
   $this->sRaw = $sRaw;
   $this->sType = $sType;
   $this->aData = $aData;
   $this->iNbData = count($aData);
  }
  
  public function getType() {
   return $this->sType;
  }
  
  public function offsetExists($mOffset) {
   return isset($this->aData[$mOffset]);
  }
  
  public function offsetGet($mOffset) {
   return $this->aData[$mOffset];
  }
  
  public function offsetSet($mOffset, $mValue) {
  }
  
  public function offsetUnset($mOffset) {
  }
  
  public function count() {
   return $this->iNbData;
  }
  
  public function __toString() {
   return $this->sRaw;
  }
 }
 
 class Core extends IrcCommands {
  private $aConfiguration = array();
  private $bConnected = false;
  private $rSocket;
  
  public function connect(array $aConfiguration) {
   if ($this->bConnected) {
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
   if (!$this->bConnected) {
    throw new Exception('Not connected');
   }
   
   if ($this->rSocket !== null) {
    fclose($this->rSocket);
    $this->rSocket = null;
   }
   
   $this->bConnected = false;
  }
  
  private function doConnect() {
   $rSocket = fsockopen($this->aConfiguration['ip'], $this->aConfiguration['port'], $iErrno, $sErrstr, 5);
   if (!$rSocket) {
    throw new SocketException($sErrstr);
   }
   
   stream_set_blocking($rSocket, 0);
   
   $this->rSocket = $rSocket;
   
   if ($this->isConnected()) {
    $this->bConnected = true;
    return true;
   }
   
   return false;
  }
  
  public function parseLine() {
   $sLine = $this->readLine();

   if ($sLine) {
    if (preg_match('/^PING (.*)$/i', $sLine, $aRegs)) {
     return new ParsedLine($sLine, 'PING', array($aRegs[1]));
    } else if (preg_match('/^:([^ ]+) JOIN ([^ ]+)/', $sLine, $aRegs)) {
     return new ParsedLine($sLine, 'JOIN', array($aRegs[1], $aRegs[2]));
    } else if (preg_match('/^:([^ ]+) PART ([^ ]+)(?:\s:)?(.*)?$/', $sLine, $aRegs)) {
     return new ParsedLine($sLine, 'PART', array($aRegs[1], $aRegs[2], $aRegs[3]));
    } else if (preg_match('/^:([^ ]+) PRIVMSG ([^ ]+) :(.*)$/', $sLine, $aRegs)) {
     return new ParsedLine($sLine, 'PRIVMSG', array($aRegs[1], $aRegs[2], $aRegs[3]));
    } else if (preg_match('/^:([^ ]+) KICK ([^ ]+) ([^ ]+)(?:\s:)?(.*)?$/', $sLine, $aRegs)) { //>> :win!~win@warriorhouse.net KICK #win Shiwang :TRAITRE
     return new ParsedLine($sLine, 'KICK', array($aRegs[1], $aRegs[2], $aRegs[3], $aRegs[4]));
    } else if (preg_match('/^:([^ ]+) NICK :([^ ]+)/', $sLine, $aRegs)) { //:win51!~phpirpgbo@par95-2-78-213-76-33.fbx.proxad.net NICK :phpirpgbot
     return new ParsedLine($sLine, 'NICK', array($aRegs[1], $aRegs[2]));
    } else if (preg_match('/^:([^ ]+) QUIT(?:\s:?)(.*)?$/', $sLine, $aRegs)) {
     return new ParsedLine($sLine, 'QUIT', array($aRegs[1], $aRegs[2]));
    } else if (preg_match('/^[^ ]+ (\d+)/', $sLine, $aRegs)) {
     return new ParsedLine($sLine, 'RAW', array($aRegs[1]));
    } else {
     return new ParsedLine($sLine, 'UNKNOWN');
    }
   }
  }
  
  public function writeLine($sLine) {
   if (!$this->bConnected || !$this->isConnected()) {
    throw new SocketException('Not connected');
   }
   return fputs($this->rSocket, $sLine."\r\n");
  }
  
  public function readLine() {
   if (!$this->bConnected || !$this->isConnected()) {
    throw new SocketException('Not connected');
   }
   return trim(fgets($this->rSocket, 512));
  }
  
  public function isConnected() {
   return ($this->rSocket && !feof($this->rSocket));
  }
 }
?>
