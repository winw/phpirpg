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
  private $bConnected = false; // true if 001 raw was retieved
  private $bWho = false; // true if we are currently processing who request
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
  }
  
  public function disconnected() {
   if ($this->bConnected) {
    $this->bConnected = false;
   }
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
    case 'NICK':
     $this->handleNick(new ParsedMask($oLine[0]), $oLine[1]);
    break;
    case 'PRIVMSG':
     $this->handlePrivmsg(new ParsedMask($oLine[0]), $oLine[1], $oLine[2]);
    break;
    case 'RAW':
     switch ($oLine[0]) {
      case 001:
       $this->bConnected = true;
       debug('Connected');
      break;
      case 315: // End of who
       $this->bWho = false;
      break;
  /*
<< who #win
>> :underworld1.no.quakenet.org 352 win #win ~phpirpgbo par95-2-78-213-76-33.fbx.proxad.net *.quakenet.org phpirpgbot H :3 phpirpg
>> :underworld1.no.quakenet.org 352 win #win ~shiwang unexpected.users.quakenet.org *.quakenet.org Shiwang H@x :3 Expect the unexpected.
>> :underworld1.no.quakenet.org 352 win #win ~win warriorhouse.net *.quakenet.org win H@ :0 win
>> :underworld1.no.quakenet.org 352 win #win TheQBot CServe.quakenet.org *.quakenet.org Q H*@d :3 The Q Bot
>> :underworld1.no.quakenet.org 315 win #win :End of /WHO list.
  */
      case 352: // Who line               chan    user    host    serv  nick    flags   hops description
       if (preg_match('/^:[^ ]+ 352 [^ ]+ ([^ ]+) ([^ ]+) ([^ ]+) [^ ]+ ([^ ]+) ([^ ]+) :\d+ (.*)$/', $oLine, $aRegs)) {
        $oWho = new ParsedMask($aRegs[4].'!'.$aRegs[2].'@'.$aRegs[3]);
        $this->handleWhoLine($oWho, $aRegs[1], $aRegs[5], $aRegs[6]);
       }
      break;
      case 422: // Motd missing
      case 376; // End of motd
       $this->oCore->join($this->aConfiguration['channel']);
       $this->oCore->nick('phpirpgbot');
      break;
      case 432: // Erroneus nickname
      case 433: // Nick already in use
       $sNewNick = $this->aConfiguration['nick'].rand(10,99);
       
       $this->oCore->nick($sNewNick);
       
       if (!$this->bConnected) {
        $this->aConfiguration['nick'] = $sNewNick; // If we don't have received welcome message yet, we will not get reply after nickname change
       }
      break;
      default:
       debug('Unknown raw :'.$oLine);
     }
    break;
    default:
     debug('UnHandled: '.$oLine);
   }
  }
  
  private function handlePrivmsg(ParsedMask $oWho, $sChannel, $sMessage) {
   if ($sMessage == '!x') {
    ChannelUsers::debug();
   }
  }
  
  private function handleWhoLine(ParsedMask $oWho, $sChannel, $sFlags, $sDescription) {
   $oUser =& ChannelUsers::findByMask($oWho);
   $bNew = ($oUser === null);
   
   if ($bNew) {
    $oUser = new ChannelUser($oWho);
   }
   
   $oUser->setFlags($sFlags);
   $oUser->setDescription($sDescription);
   
   if ($bNew) {
    ChannelUsers::add($oUser);
   }
  }

  private function handleJoin(ParsedMask $oWho, $sChannel) {
   $this->oCore->msg($sChannel, '[join] nick : '.$oWho->getNick().' || user : '.$oWho->getUser().' || host : '.$oWho->getHost());
   if ($oWho->getNick() == $this->aConfiguration['nick']) {
    $this->bWho = true;
    $this->oCore->who($sChannel);
   }
   
   $oUser =& ChannelUsers::findByMask($oWho); //ChannelUsers::findByUserAndHost($oWho);
   
   if ($oUser) {
    if ($oUser->inNetsplit()) {
     //$oUser->setNick($oWho->getNick());
     $oUser->setNetsplit(false);
     $this->oCore->msg($oUser->getNick(), _('You where reconnected without penalties after netsplit');
    }
   } else {
    ChannelUsers::add(new ChannelUser($oWho));
   }
  }
  
  private function handlePart(ParsedMask $oWho, $sChannel, $sMessage) {
   $this->oCore->msg($sChannel, '[part] nick : '.$oWho->getNick().' || user : '.$oWho->getUser().' || host : '.$oWho->getHost().' || message : '.$sMessage);
   
   $oUser =& ChannelUsers::findByMask($oWho);
   if ($oUser) {
    ChannelUsers::del($oUser);
   }
  }
  
  private function handleKick(ParsedMask $oWho, $sChannel, $sToNick, $sMessage) {
   $this->oCore->msg($sChannel, '[kick] nick : '.$oWho->getNick().' || user : '.$oWho->getUser().' || host : '.$oWho->getHost().' || to : '.$sToNick.' || message : '.$sMessage);
   
   $oUser =& ChannelUsers::findByMask($oWho);
   if ($oUser) {
    ChannelUsers::del($oUser);
   }
  }
  
  private function handleQuit(ParsedMask $oWho, $sMessage) {
   $this->oCore->msg($this->aConfiguration['channel'], '[quit] nick : '.$oWho->getNick().' || user : '.$oWho->getUser().' || host : '.$oWho->getHost().' || message : '.$sMessage);
   
   $bNetsplit = (bool)preg_match($this->aConfiguration['netsplit'], $sMessage);
   
   $oUser =& ChannelUsers::findByMask($oWho);
   if ($oUser) {
    if ($bNetsplit) {
     $oUser->setNetsplit(time());
    } else {
     ChannelUsers::del($oUser);
    }
   }
  }
  
  private function handleNick(ParsedMask $oWho, $sNewNick) {
   $bMe = false;
   if ($oWho->getNick() === $this->aConfiguration['nick']) { // If it's the nickname of the bot, we change it in the configuration
    $this->aConfiguration['nick'] = $sNewNick;
    $bMe = true;
   }
   $this->oCore->msg($this->aConfiguration['channel'], '[nick] nick : '.$oWho->getNick().' || user : '.$oWho->getUser().' || host : '.$oWho->getHost().' || newnick : '.$sNewNick.' || me : '.($bMe ? 'Y' : 'N'));
   
   $oUser =& ChannelUsers::findByMask($oWho);
   if ($oUser) {
    $oUser->setNick($sNewNick);
   }
  }
  
  public function tick() {
  }
 }

?>
