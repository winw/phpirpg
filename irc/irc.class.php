<?php
 class Irc extends IrcCommands {
  private $oCore;
  private $bConnected = false; // true if 001 raw was retieved
  public $aConfiguration = array();
  public $aServerConfiguration = array(
   'chantypes' => array('&', '#'),
   'prefix' => array('o' => '@', 'v' => '+')
  );
  private $aModules = array();
  
  public function __construct(Core &$oCore, array $aConfiguration) {
   $this->oCore = $oCore;
   if (!isset($aConfiguration['nick'], $aConfiguration['user'], $aConfiguration['description'], $aConfiguration['channel'])) {
    throw new ArgumentException();
   }
   $this->aConfiguration = $aConfiguration;
   $this->aModules = explode(' ', IRPG_MODULES);
   $this->loadModules();
  }
  
  private function loadModules() {
   foreach ($this->aModules as $sModule) {
    include BASE_PATH.'modules/'.$sModule.'.php';
    ModuleManager::add(new $sModule($this, $this->oCore));
   }
  }
  
  public function connected() {
   if ($this->bConnected) {
    throw new Exception('Already connected');
   }
   
   $this->writeLine('USER '.$this->aConfiguration['user'].' '.PHP_OS.' null :'.$this->aConfiguration['description']);
   $this->writeLine('NICK '.$this->aConfiguration['nick']);
  }
  
  public function disconnected() {
   if ($this->bConnected) {
    $this->bConnected = false;
   }
  }
  
  protected function writeLine($sLine) {
   $this->oCore->writeLine($sLine);
  }
  
  public function parse(parsedLine $oLine) {
   switch ($oLine->getType()) {
    case 'PING':
     $this->pong($oLine[0]);
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
    case 'MSG':
     $this->handleMsg(new ParsedMask($oLine[0]), $oLine[1], $oLine[2]);
    break;
    case 'ACTION':
     $this->handleAction(new ParsedMask($oLine[0]), $oLine[1], $oLine[2]);
    break;
    case 'CTCP':
     $this->handleCtcp(new ParsedMask($oLine[0]), $oLine[1], $oLine[2]);
    break;
    case 'CTCPREPLY':
     $this->handleCtcpReply(new ParsedMask($oLine[0]), $oLine[1], $oLine[2]);
    break;
    case 'NOTICE':
     $this->handleNotice(new ParsedMask($oLine[0]), $oLine[1], $oLine[2]);
    break;
    case 'RAW':
     switch ($oLine[0]) {
      case 001:
       $this->bConnected = true;
       debug('Connected');
      break;
      case 005:
      if (preg_match('#CHANTYPES=([^ ]+)#', $oLine[1], $aRegs)) {
       $this->aServerConfiguration['chantypes'] = str_split($aRegs[1]);
      }
      if (preg_match('#PREFIX=(.+?)\((.+?)\)#', $oLine[1], $aRegs)) {
       if (($iModes = strlen($aRegs[1])) == strlen($aRegs[2])) {
        $aPrefixs = array();
        for ($i = 0; $i < $iModes; ++$i) {
         $aPrefixs[$aRegs[1][$i]] = $aRegs[2][$i];
        }
        $this->aServerConfiguration['prefix'] = $aPrefixs;
       }
      }
      break;
      case 315: // End of who
       $aArgs = explode(' ', $oLine[1]);
       if (isset($aArgs[1])) {
        ModuleManager::dispatch('onEndOfWho', $aArgs[1]);
       }
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
      case 353: // Names line
       if (preg_match('/^:[^ ]+ 353 [^ ]+ [^ ]+ ([^ ]+) :(.*)$/', $oLine, $aRegs)) {
        $aUsers = array();
        
        foreach (explode(' ', $aRegs[2]) as $sUser) {
         $sFlag = '';
         if (in_array($sUser[0], $this->aServerConfiguration['prefix'])) {
          $sFlag = $sUser[0];
          $sUser = substr($sUser, 1);
         }
         $aUsers[$sUser] = $sFlag;
        }
        
        $this->handleNamesLine($aRegs[1], $aUsers);
       }
      break;
      case 366: // End of names
      
      break;
      case 422: // Motd missing
      case 376; // End of motd
       $this->join($this->aConfiguration['channel']);
      break;
      case 432: // Erroneus nickname
      case 433: // Nick already in use
       $sNewNick = $this->aConfiguration['nick'].rand(10,99);
       
       $this->nick($sNewNick);
       
       if (!$this->bConnected) {
        $this->aConfiguration['nick'] = $sNewNick; // If we don't have received welcome message yet, we will not get reply after nickname change
       }
      break;
      default:
       debug('Unknown raw :'.$oLine);
       ModuleManager::dispatch('onRaw', $oLine[0], $oLine[1]);
     }
    break;
    default:
     debug('UnHandled: '.$oLine);
   }
  }
  
  private function handleMsg(ParsedMask $oWho, $sTarget, $sMessage) {
   ModuleManager::dispatch('onMsg', $oWho, $sTarget, $sMessage);
  }
  
  private function handleAction(ParsedMask $oWho, $sTarget, $sMessage) {
   ModuleManager::dispatch('onAction', $oWho, $sTarget, $sMessage);
  }

  private function handleCtcp(ParsedMask $oWho, $sTarget, $sMessage) {
   ModuleManager::dispatch('onCtcp', $oWho, $sTarget, $sMessage);
  }
  
  private function handleNotice(ParsedMask $oWho, $sTarget, $sMessage) {
   ModuleManager::dispatch('onNotice', $oWho, $sTarget, $sMessage);
  }
  
  private function handleCtcpReply(ParsedMask $oWho, $sTarget, $sMessage) {
   ModuleManager::dispatch('onCtcpReply', $oWho, $sTarget, $sMessage);
  }
  
  private function handleWhoLine(ParsedMask $oWho, $sTarget, $sFlags, $sDescription) {
   ModuleManager::dispatch('onWhoLine', $oWho, $sTarget, $sFlags, $sDescription);
  }
  
  private function handleNamesLine($sChannel, array $aUsers) {
   ModuleManager::dispatch('onNamesLine', $sChannel, $aUsers);
  }

  private function handleJoin(ParsedMask $oWho, $sChannel) {
   ModuleManager::dispatch('onJoin', $oWho, $sChannel);
  }
  
  private function handlePart(ParsedMask $oWho, $sChannel, $sMessage) {
   ModuleManager::dispatch('onPart',$oWho, $sChannel, $sMessage);
  }
  
  private function handleKick(ParsedMask $oWho, $sChannel, $sToNick, $sMessage) {   
   ModuleManager::dispatch('onKick', $oWho, $sChannel, $sToNick, $sMessage);
  }
  
  private function handleQuit(ParsedMask $oWho, $sMessage) {
   ModuleManager::dispatch('onQuit', $oWho, $sMessage);
  }
  
  private function handleNick(ParsedMask $oWho, $sNewNick) {
   if ($oWho->getNick() === $this->aConfiguration['nick']) { // If it's the nickname of the bot, we change it in the configuration
    $this->aConfiguration['nick'] = $sNewNick;
   }
   
   ModuleManager::dispatch('onNick', $oWho, $sNewNick);
  }
 }

?>
