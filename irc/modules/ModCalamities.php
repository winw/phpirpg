<?php
 class ModCalamities extends Module {
  private $aaCalamities = array();
  private $iCheckDelay;
  private $iProbability;
  
  public function onLoad(){
   $oXml = ModuleManager::dispatchTo('ModMap', 'getXml');
   // Loading calamities
   $oAttributes = $oXml->calamities->attributes();
   
   if (!isset($oAttributes->probability, $oAttributes->every)) {
    throw new ArgumentException();
   }
   
   $this->iCheckDelay = (int)$oAttributes->every;
   $this->iProbability = round(Utils::expressionToRatio($oAttributes->probability) / $this->iCheckDelay);
   
   foreach ($oXml->calamities->calamity as $oCalamity) {
    $oAttributes = $oCalamity->attributes();
    if (!isset($oAttributes->zone, $oAttributes->penality)) {
     throw new ArgumentException();
    }

    $aFromTo = explode('-', $oAttributes->penality);
    $this->aaCalamities[(string) $oAttributes->zone][] = array(
     'penality' => array('from' => $aFromTo[0], 'to' => isset($aFromTo[1]) ? $aFromTo[1] : (float)$aFromTo[0]),
     'message' => (string)$oCalamity
    );
   }
   
   $oTimer = new Timer($this->iCheckDelay, 0, function(){
    $this->doCheckCalamities();
   });
   
   TimerManager::add(__CLASS__.'doCheckCalamities', $oTimer);
  }
  
  private function doCheckCalamities() {
   if (rand(1, $this->iProbability) == 1) {
    $oIrpgUsers = new dbIrpgUsers();
    if ($oIrpgUser = $oIrpgUsers->select('id, x, y')->where('irpg_users.id IN (SELECT channel_users.id_irpg_user FROM channel_users WHERE channel_users.id_irpg_user IS NOT NULL)')->order('RAND()')->fetch()) {
     $sZone = ModuleManager::dispatchTo('ModMap', 'getZone', $oIrpgUser->x, $oIrpgUser->y);

     if (($sZone != '') && isset($this->aaCalamities[$sZone])) {
      $aCalamity = $this->aaCalamities[$sZone][rand(0, count($this->aaCalamities[$sZone])-1)];
      
      $iPenalityRatio = rand($aCalamity['penality']['from'], $aCalamity['penality']['to']) / 100;
      
      $this->doCalamity((int)$oIrpgUser->id, $aCalamity['message'], $iPenalityRatio);
     }
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
  public function onUnload(){
   if (TimerManager::exists(__CLASS__.'doCheckCalamities')) {
    TimerManager::del(__CLASS__.'doCheckCalamities');
   }
  }
 }
