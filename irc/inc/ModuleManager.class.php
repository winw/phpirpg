<?php
 class ModuleManager {
  private static $aoModules = array();
  private static $bPreventPropagation = false;
  
  public static function add(Module &$oModule) {
   $sClass = get_class($oModule);
   
   if (isset(self::$aoModules[$sClass])) {
    throw new Exception('Module '.$sClass.' already loaded');
   }
   
   self::$aoModules[$sClass] = $oModule;
   
   self::dispatchTo($sClass, 'onLoad');
  }
  
  public static function del(Module &$oModule) {
   $sClass = get_class($oModule);
   
   if (!isset(self::$aoModules[$sClass])) {
    throw new Exception('Module '.$sClass.' unknown');
   }
   
   self::dispatchTo($sClass, 'onUnload');
   
   unset(self::$aoModules[$sClass]);
  }
  
  public static function delFromName($sClass) {
   if (!isset(self::$aoModules[$sClass])) {
    throw new Exception('Module '.$sClass.' unknown');
   }
   
   return self::del(self::$aoModules[$sClass]);
  }
  
  public static function clear() {
   self::dispatch('onUnload');
   self::$aoModules = array();
  }
  
  public static function getList() {
   return array_keys(self::$aoModules);
  }
  
  public static function dispatch($sMethod) {
   $aArguments = func_get_args();
   
   $sMethod = array_shift($aArguments);
   
   $aReturn = array();
   
   foreach (self::$aoModules as $sClass => &$oModule) {
    if (self::$bPreventPropagation) {
     self::$bPreventPropagation = false;
     break;
    }
    if (method_exists($oModule, $sMethod)) {
     $aReturn[$sClass] = call_user_func_array(array($oModule, $sMethod), $aArguments);
    }
   }
   
   return $aReturn;
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
  
  public static function preventPropagation() {
   self::$bPreventPropagation = true;
  }
 }