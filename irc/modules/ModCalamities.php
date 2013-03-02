<?php
 class ModCalamities extends Module {
  private $aaCalamities = array();
  
  public function onLoad(){
   $oXml = ModuleManager::dispatchTo('ModMap', 'getXml');
   // Loading calamities
   foreach ($oXml->calamities->calamity as $oCalamity) {
    $oAttributes = $oCalamity->attributes();
    if (!isset($oAttributes->zone, $oAttributes->ratio, $oAttributes->penality)) {
     throw new ArgumentException();
    }
    $aTmp = array('ratio' => (float) $oAttributes->ratio);
    $aFromTo = explode('-', $oAttributes->penality);
    $aTmp['penality'] = array('from' => $aFromTo[0], 'to' => isset($aFromTo[1]) ? $aFromTo[1] : (float)$aFromTo[0]);
    $aTmp['message'] = (string)$oCalamity;
    $this->aaCalamities[(string) $oAttributes->zone][] = $aTmp;
   }
  }
  
  public function onUserMove($iIdIrpgUser, $iOldX, $iOldY, $iNewX, $iNewY) {
   $sZone = ModuleManager::dispatchTo('ModMap', 'getZone', $iNewX, $iNewY);

   if ($sZone != '' && isset($this->aaCalamities[$sZone])) {
    $aCalamity = $this->aaCalamities[$sZone][rand(0, count($this->aaCalamities[$sZone])-1)];

    if (rand(1, 100) <= $aCalamity['ratio']) {
     $iPenalityRatio = rand($aCalamity['penality']['from'], $aCalamity['penality']['to']) / 100;
     
     $this->doCalamity($iIdIrpgUser, $aCalamity['message'], $iPenalityRatio);
    }
   }
  }
  
  private function doCalamity($iIdIrpgUser, $sMessage, $iPenalityRatio) {
   $oIrpgUsers = new dbIrpgUsers();
   
   if ($oIrpgUser = $oIrpgUsers->writable()->select()->where('id = ?', $iIdIrpgUser)->fetch()) {
    $iPenalties = round($oIrpgUser->time_to_level * $iPenalityRatio);

    if ($iPenalties > 0) {
     $oIrpgUser->time_to_level += $iPenalties;
     $oIrpgUser->save();
     
     $this->msg($this->getGameChannel(), '[calamity] '.str_replace('%username', $oIrpgUser->login, $sMessage).' This terrible calamity slowed '.Utils::duration($iPenalties).' from level '.$oIrpgUser->level.', next level in '.Utils::duration($oIrpgUser->time_to_level));
    }
   }
  }
  
  public function onCtcp(ParsedMask $oWho, $sTarget, $sMessage){}
  public function onMsg(ParsedMask $oWho, $sTarget, $sMessage){}
  public function onPart(ParsedMask $oWho, $sChannel, $sMessage){}
  public function onKick(ParsedMask $oWho, $sChannel, $sToNick, $sMessage){}
  public function onQuit(ParsedMask $oWho, $sMessage){}
  public function onNick(ParsedMask $oWho, $sNewNick){}
  public function onNotice(ParsedMask $oWho, $sTarget, $sMessage){}
  public function onAction(ParsedMask $oWho, $sTarget, $sMessage){}
  public function onCtcpReply(ParsedMask $oWho, $sTarget, $sMessage){}
  public function onWhoLine(ParsedMask $oWho, $sTarget, $sFlags, $sDescription){}
  public function onJoin(ParsedMask $oWho, $sChannel){}
  public function onNamesLine($sChannel, array $aUsers){}
  public function onRaw($iRaw, $sArguments){}
  public function onEndOfWho($sTarget){}
 }
?>
