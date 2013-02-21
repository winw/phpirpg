<?php
 class ModuleManager {
  private static $aoModules = array();
  
  public static function add(Module &$oModule) {
   $sClass = get_class($oModule);
   
   if (isset(self::$aoModules[$sClass])) {
    throw new Exception('Module '.$sClass.' already loaded');
   }
   
   self::$aoModules[$sClass] = $oModule;
   $oModule->onLoad();
  }
  
  public static function dispatch($sMethod) {
   $aArguments = func_get_args();
   
   $sMethod = array_shift($aArguments);
   
   foreach (self::$aoModules as &$oModule) {
    if (method_exists($oModule, $sMethod)) {
     call_user_func_array(array($oModule, $sMethod), $aArguments);
    }
   }
  }
  
  public static function dispatchTo($sClass, $sMethod) {
   $aArguments = func_get_args();
   
   $sClass = array_shift($aArguments);
   $sMethod = array_shift($aArguments);
   
   if (!isset(self::$aoModules[$sClass])) {
    throw new Exception('Unknown module '.$sClass);
   }
   
   if (!method_exists(self::$aoModules[$sClass], $sMethod)) {
    throw new ArgumentException($sMethod);
   }
   
   return call_user_func_array(array(self::$aoModules[$sClass], $sMethod), $aArguments);
  }
 }
?>
