<?php
class ModReload extends Module {
 public function onMsg(ParsedMask $oWho, $sTarget, $sMessage) {
  if ($sTarget == Configuration::BOT_CHANNEL) {
   if ($sMessage == '!reload') {
    $sModules = '';
    
    if (runkit_import(BASE_PATH.'config.php', RUNKIT_IMPORT_FUNCTIONS|RUNKIT_IMPORT_CLASS_METHODS|RUNKIT_IMPORT_CLASS_CONSTS|RUNKIT_IMPORT_CLASS_PROPS|RUNKIT_IMPORT_CLASSES|RUNKIT_IMPORT_OVERRIDE)) {
     $sModules .= '*config* ';
    }
    
    ModuleManager::preventPropagation();
    
    foreach (ModuleManager::getList() as $sModule) {
     if ($sModule !== __CLASS__) {
      ModuleManager::delFromName($sModule);
     }
    }
    
    foreach (explode(' ', Configuration::BOT_MODULES) as $sModule) {
     if ($sModule !== __CLASS__) {
      if (function_exists('runkit_import')) {
       if (runkit_import(BASE_PATH.'modules/'.$sModule.'.php', RUNKIT_IMPORT_FUNCTIONS|RUNKIT_IMPORT_CLASS_METHODS|RUNKIT_IMPORT_CLASS_CONSTS|RUNKIT_IMPORT_CLASS_PROPS|RUNKIT_IMPORT_CLASSES|RUNKIT_IMPORT_OVERRIDE)) {
        $sModules .= $sModule.' ';
       }
      }
      ModuleManager::add(new $sModule($this->oIrc, $this->oCore));
     }
    }
    $this->msg($sTarget, 'OK ('.trim($sModules).')');
   }
  }
 }

 public function onLoad() {}
 public function onCtcp(ParsedMask $oWho, $sTarget, $sMessage){}
 public function onWhoLine(ParsedMask $oWho, $sTarget, $sFlags, $sDescription){}
 public function onJoin(ParsedMask $oWho, $sChannel){}
 public function onPart(ParsedMask $oWho, $sChannel, $sMessage){}
 public function onKick(ParsedMask $oWho, $sChannel, $sToNick, $sMessage){}
 public function onQuit(ParsedMask $oWho, $sMessage){}
 public function onNick(ParsedMask $oWho, $sNewNick){}
 public function onNotice(ParsedMask $oWho, $sTarget, $sMessage){}
 public function onAction(ParsedMask $oWho, $sTarget, $sMessage){}
 public function onCtcpReply(ParsedMask $oWho, $sTarget, $sMessage){}
 public function onNamesLine($sChannel, array $aUsers){}
 public function onRaw($iRaw, $sArguments){}
 public function onEndOfWho($sTarget){}
 public function onUnload(){}
}