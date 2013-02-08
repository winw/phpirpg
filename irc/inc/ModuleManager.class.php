<?php
 class ModuleManager {
  private static $aMethods = array(
   'onLoad',
   'onMsg',
   'onWhoLine',
   'onJoin',
   'onPart',
   'onKick',
   'onQuit',
   'onNick'
  );
  
  private static $aoModules = array();
  
  public static function add(IrcEvents &$oModule) {
   $sClass = get_class($oModule);
   
   if (isset(self::$aoModules[$sClass])) {
    throw new Exception('Module '.$sClass.' already loaded');
   }
   
   self::$aoModules[] = $oModule;
   $oModule->onLoad();
  }
  
  public static function dispatch($sMethod) {
   $aArguments = func_get_args();
   
   $sMethod = array_shift($aArguments);
   
   if (!in_array($sMethod, self::$aMethods)) {
    throw new ArgumentException($sMethod);
   }
   
   foreach (self::$aoModules as &$oModule) {
    call_user_func_array(array($oModule, $sMethod), $aArguments);
   }
  }
  
  public static function dispatchTo($sClass, $sMethod) {
   $aArguments = func_get_args();
   
   $sClass = array_shift($aArguments);
   $sMethod = array_shift($aArguments);
   
   if (!in_array($sMethod, self::$aMethods)) {
    throw new ArgumentException($sMethod);
   }
   
   if (!isset(self::$aoModules[$sClass])) {
    throw new Exception('Unknown module '.$sClass);
   }
   
   return call_user_func_array(array(self::$aoModules[$sClass], $sMethod), $aArguments);
  }
 }
?>
