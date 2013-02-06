<?php
 class parsedMask {
  private $sHost = '';
  private $sNick = '';
  private $sUser = '';
  private $sMask = '';
  
  public function __construct($sMask) {
   $this->sMask = $sMask;
   
   $iUser = strpos($sMask, '!');
   $iHost = strpos($sMask, '@', $iUser+1);
   
   if ($iUser !== false && $iHost !== false) {
    $this->sNick = substr($sMask, 0, $iUser);
    $this->sUser = substr($sMask, $iUser+1, $iHost-$iUser-1);
    $this->sHost = substr($sMask, $iHost+1);
   }
  }
  
  public function getHost() {
   return $this->sHost;
  }
  
  public function getNick() {
   return $this->sNick;
  }
  
  public function getUser() {
   return $this->sUser;
  }
  
  public function __toString() {
   return $this->sMask;
  }
 }
 
 class Irpg {
  private $oCore;
  private $bConnected = false;
  private $aConfiguration = array();
  
  public function __construct(Core &$oCore, array $aConfiguration) {
   $this->oCore = $oCore;
   if (!isset($aConfiguration['nick'], $aConfiguration['user'], $aConfiguration['description'], $aConfiguration['channel'])) {
    throw new ArgumentException();
   }
   $this->aConfiguration = $aConfiguration;
  }
  
  public function connected() {
   if ($this->bConnected) {
    throw new Exception('Already connected');
   }
   
   $this->oCore->writeLine('USER '.$this->aConfiguration['user'].' '.PHP_OS.' null :'.$this->aConfiguration['description']);
   $this->oCore->writeLine('NICK '.$this->aConfiguration['nick']);
   
   $this->bConnected = true;
   
   debug('Connected');
  }
  
  public function disconnected() {
   if (!$this->bConnected) {
    throw new Exception('Not connected');
   }
   
   $this->bConnected = false;
  }
  
  public function parse(parsedLine $oLine) {
   switch ($oLine->getType()) {
    case 'PING':
     $this->oCore->pong($oLine[0]);
    break;
    case 'JOIN':
     $this->handleJoin(new ParsedMask($oLine[0]), $oLine[1]);
    break;
    case 'PART':
     $this->handlePart(new ParsedMask($oLine[0]), $oLine[1], $oLine[2]);
    break;
    case 'KICK':
     $this->handleKick(new ParsedMask($oLine[0]), $oLine[1], $oLine[2], $oLine[3]);
    break;
    case 'QUIT':
     $this->handleQuit(new ParsedMask($oLine[0]), $oLine[1]);
    break;
    case 'RAW':
     switch ($oLine[0]) {
      case 422:
      case 376;
       $this->oCore->join($this->aConfiguration['channel']);
      break;
      default:
       debug('Unknown raw :'.$oLine);
     }
    break;
    default:
     debug('UnHandled: '.$oLine);
   }
  }
  
  private function handleJoin(ParsedMask $oWho, $sChannel) {
   $this->oCore->msg($sChannel, '[join] nick : '.$oWho->getNick().' || user : '.$oWho->getUser().' || host : '.$oWho->getHost());
  }
  
  private function handlePart(ParsedMask $oWho, $sChannel, $sMessage) {
   $this->oCore->msg($sChannel, '[part] nick : '.$oWho->getNick().' || user : '.$oWho->getUser().' || host : '.$oWho->getHost().' || message : '.$sMessage);
  }
  
  private function handleKick(ParsedMask $oWho, $sChannel, $sToNick, $sMessage) {
   $this->oCore->msg($sChannel, '[kick] nick : '.$oWho->getNick().' || user : '.$oWho->getUser().' || host : '.$oWho->getHost().' || to : '.$sToNick.' || message : '.$sMessage);
  }
  
  private function handleQuit(ParsedMask $oWho, $sMessage) {
   $this->oCore->msg($sChannel, '[quit] nick : '.$oWho->getNick().' || user : '.$oWho->getUser().' || host : '.$oWho->getHost().' || message : '.$sMessage);
  }
  
  public function tick() {
  }
 }
?>
