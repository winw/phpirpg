<?php
 class IrpgUser {
  private $iIdUser = null;
  private $sGameChannel = '#win';
  private $oWho;
  
  public function __construct(ParsedMask $oWho) {
   $oChannelUsers = new dbChannelUsers();
   if ($oChannelUser = $oChannelUsers->select('id_irpg_user')->where('channel = ? AND nick = ? AND user = ? AND host = ?', $this->sGameChannel, $oWho->getNick(), $oWho->getUser(), $oWho->getHost())->fetch()) {
    if ($oChannelUser->id_irpg_user) {
     $this->iIdUser = (int)$oChannelUser->id_irpg_user;
    }
   }
   $this->oWho = $oWho;
  }
  
  public function isLogged() {
   return $this->iIdUser !== null;
  }
  
  public function setId($iId) {
   if (!$this->isLogged()) {
    $oChannelUsers = new dbChannelUsers();
    if ($oChannelUser = $oChannelUsers->writable()->select()->where('channel = ? AND nick = ? AND user = ? AND host = ?', $this->sGameChannel, $this->oWho->getNick(), $this->oWho->getUser(), $this->oWho->getHost())->fetch()) {
     $oChannelUser->id_irpg_user = $iId;
     $oChannelUser->save();
     $this->iIdUser = $Id;
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
  
  public function onLoad(){}
  
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
          $oCurrentUser->setId((int)$oIrpgUser->id);
          // Login procedure
         } catch (Exception $e) {
          $this->msg($oWho->getNick(), 'An error occured where creating your account, please try again later');
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
        $oUser = $oIrpgUsers->select('id')->where('login = ? AND password = ?', $sLogin, self::encodePassword($sPassword))->fetch();
        if (!$oUser) {
         $this->msg($oWho->getNick(), 'Wrong login and/or password');
        } else {
         // Login procedure
         $this->msg($oWho->getNick(), 'Ok, login successfull');
         $oCurrentUser->setId((int)$oUser->id);
        }
       }
      break;
     }
    } else {
     $this->msg($oWho->getNick(), 'Availables commands : REGISTER, LOGIN');
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
 }
?>
