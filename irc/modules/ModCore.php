<?php
 class IrpgUser {
  private $iIdUser = null;
  private $oWho;
  
  public function __construct(ParsedMask $oWho) {   
   $this->oWho = $oWho;
   $this->refresh();
  }
  
  private function __clone(){}
  
  public function refresh() {
   $oChannelUsers = new dbChannelUsers();
   if ($oChannelUser = $oChannelUsers->select('id_irpg_user')->where('channel = ? AND nick = ? AND user = ? AND host = ?', IRPG_CHANNEL, $this->oWho->getNick(), $this->oWho->getUser(), $this->oWho->getHost())->fetch()) {
    if ($oChannelUser->id_irpg_user) {
     $this->iIdUser = (int)$oChannelUser->id_irpg_user;
    }
   }
  }
  
  public function isLogged() {
   return $this->iIdUser !== null;
  }
  
  public function getCoords() {
   if ($this->isLogged()) {
    $oIrpgUsers = new dbIrpgUsers();
    if ($oIrpgUser = $oIrpgUsers->select('x, y')->where('id = ?', $this->iIdUser)->fetch()) {
     return array('x' => $oIrpgUser->x, 'y' => $oIrpgUser->y);
    }
   }
  }
 }
 
 class ModCore extends Module {
  private static function encodePassword($sPassword) {
   return sha1($sPassword.'%'.strrev($sPassword));
  }
  
  public function onCtcp(ParsedMask $oWho, $sTarget, $sMessage){
   if (!strcasecmp($sMessage, 'VERSION')) {
    $this->ctcpReply($oWho->getNick(), 'VERSION phpirpg beta');
   }
  }
  
  public function onMsg(ParsedMask $oWho, $sTarget, $sMessage){
   $oCurrentUser = new IrpgUser($oWho);
   $oIrpgUsers = new dbIrpgUsers();
   
   if ($this->isGameChannel($sTarget)) {
    if ($oCurrentUser->isLogged()) {
     //addPenalties($oCurrentUser)
    }
   } else if ($this->isMe($sTarget)) {
    $aTokens = explode(' ', $sMessage);
    $iNbTokens = count($aTokens);
    if ($iNbTokens > 0) {
     switch (strtoupper($aTokens[0])) {
      case 'REGISTER':
       if ($iNbTokens != 5) {
        $this->msg($oWho->getNick(), 'Syntax: REGISTER <login> <password> <email> <type>');
       } else if ($oCurrentUser->isLogged()) {
        $this->msg($oWho->getNick(), 'You are already registered and logged');
       } else {
        list(,$sLogin, $sPassword, $sEmail, $sType) = $aTokens;
        if (!filter_var($sEmail, FILTER_VALIDATE_EMAIL)) {
         $this->msg($oWho->getNick(), $sEmail.' is not a valid email address');
        } else if ($oIrpgUsers->select('1')->where('login = ?', $sLogin)->fetch()) {
         $this->msg($oWho->getNick(), 'This login is already used');
        } else if (strlen($sPassword) < 6) {
         $this->msg($oWho->getNick(), 'This password is too short, it must be > 5 caracters');
        } else if (!in_array($sType, array('test'))) {
         $this->msg($oWho->getNick(), 'This type is unknown, types are : test');
        } else {
         try {
          $oIrpgUser = $oIrpgUsers->create();
          $oIrpgUser->date_created = new dbDontEscapeString('NOW()');
          $oIrpgUser->login = $sLogin;
          $oIrpgUser->password = self::encodePassword($sPassword);
          $oIrpgUser->email = $sEmail;
          $oIrpgUser->save();
          $this->msg($oWho->getNick(), 'Ok, your account is successfully created');
          
          $this->doUserLogin($oWho, (int)$oIrpgUser->id);
          
          // Login procedure
         } catch (Exception $e) {
          $this->msg($oWho->getNick(), 'An error occured when creating your account, please try again later');
         }
        }
       }
      break;
      case 'LOGIN':
       if ($oCurrentUser->isLogged()) {
        $this->msg($oWho->getNick(), 'You are already logged');
       } else if ($iNbTokens != 3) {
        $this->msg($oWho->getNick(), 'Syntax: LOGIN <login> <password>');
       } else {
        list(,$sLogin, $sPassword) = $aTokens;
        $oIrpgUser = $oIrpgUsers->select('id')->where('login = ? AND password = ?', $sLogin, self::encodePassword($sPassword))->fetch();
        if (!$oIrpgUser) {
         $this->msg($oWho->getNick(), 'Wrong login and/or password');
        } else {
         // Login procedure
         $this->msg($oWho->getNick(), 'Ok, login successfull');
         
         $this->doUserLogin($oWho, (int)$oIrpgUser->id);
        }
       }
      break;
     }
    } else {
     $this->msg($oWho->getNick(), 'Availables commands : REGISTER, LOGIN');
    }
   }
  }
  
  public function doUserLogin(ParsedMask $oWho, $iIdIrpgUser, $bSilent = false) {
   $oChannelUsers = new dbChannelUsers();
   $oIrpgUsers = new dbIrpgUsers();
   if ($oChannelUser = $oChannelUsers->writable()->select()->where('channel = ? AND nick = ? AND user = ? AND host = ?', IRPG_CHANNEL, $oWho->getNick(), $oWho->getUser(), $oWho->getHost())->fetch()) {
    $oChannelUser->id_irpg_user = $iIdIrpgUser;
    $oChannelUser->save();
    if ($oIrpgUser = $oIrpgUsers->writable()->select()->where('id = ?', $iIdIrpgUser)->fetch()) {
     $oIrpgUser->date_login = new dbDontEscapeString('NOW()');
     $oIrpgUser->save();
     if (!$bSilent) {
      $this->msg(IRPG_CHANNEL, $oWho->getNick().' is now online with username '.$oIrpgUser->login);
     }
     ModuleManager::dispatch('onUserLogin', $oWho, (int)$oIrpgUser->id);
    }
   }
  }
  
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
  public function onLoad(){}
 }
?>
