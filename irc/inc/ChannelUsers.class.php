<?php
 class ChannelUser {
  private $aUser = array(
   'nick' => '',
   'user' => '',
   'host' => '',
   'flags' => '',
   'description' => ''
  );
  
  public function __construct(ParsedMask $oMask = null) {
   if ($oMask !== null) {
    $this->setNick($oMask->getNick())->setUser($oMask->getUser())->setHost($oMask->getHost());
    debug('Creating user object '.$oMask->getNick());
   }
  }
  
  public function __call($sName, array $aArguments) {
   $sKey = strtolower(substr($sName, 3));
   $sMethod = substr($sName, 0, 3);
   
   if (!in_array($sMethod, array('set', 'get'), true) || !isset($this->aUser[$sKey])) {
    throw new ArgumentException($sName);
   }
   
   if ($sMethod == 'get') {
    return $this->aUser[$sKey];
   } else {
    $this->aUser[$sKey] = $aArguments[0];
    return $this;
   }
  }
  
  public function isVoice() {
   return $this->isOperator() || strpos($this->aUser['flags'], '+') !== false;
  }
  
  public function isOperator() {
   return strpos($this->aUser['flags'], '@') !== false;
  }
  
 }
 
 class ChannelUsers {
  private static $aoUsers = array();
  
  public static function findByNick($sNick) {
   foreach (self::$aoUsers as &$oUser) {
    if ($oUser->getNick() === $sNick) {
     return $oUser;
    }
   }
  }
  
  public static function findByMask(ParsedMask $oMask) {
   foreach (self::$aoUsers as &$oUser) {
    if (($oUser->getNick() === $oMask->getNick()) && ($oUser->getUser() === $oMask->getUser()) && ($oUser->getHost() === $oMask->getHost())) {
     return $oUser;
    }
   }
  }
  
  public static function add(ChannelUser $oUser) {
   $oLocalUser = self::findByNick($oUser->getNick());
   if ($oLocalUser === null) {
    debug('Creating user '.$oUser->getNick());
    self::$aoUsers[] = $oUser;
    return true;
   }
   
   return false;
  }
  
  public static function del(ChannelUser $oUser) {
   foreach (self::$aoUsers as $iKey => $oLocalUser) {
    if ($oLocalUser->getNick() === $oUser->getNick()) {
     debug('Deleting user '.$oUser->getNick());
     unset(self::$aoUsers[$iKey]);
     return true;
    }
   }
   
   return false;
  }
  
  public static function clear() {
   $this->aoUsers = array();
  }
  
  public static function debug() {
   print_r(self::$aoUsers);
  }
 }
?>
