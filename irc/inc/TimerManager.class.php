<?php
 class TimerManager {
  public static $aoTimers = array();
  
  public static function add($sName, Timer $oTimer) {
   if (self::exists($sName)) {
    throw new Exception('Timer already exists');
   }
   
   self::$aoTimers[$sName] = $oTimer;
  }
  
  public static function del($sName) {
   if (!self::exists($sName)) {
    throw new Exception('Timer does not exists');
   }
   
   unset(self::$aoTimers[$sName]);
  }
  
  public static function exists($sName) {
   return isset(self::$aoTimers[$sName]);
  }
  
  public static function tick() {
   foreach (self::$aoTimers as $sKey => &$oTimer) {
    $oTimer->tick();
    if ($oTimer->isFinished()) {
     unset(self::$aoTimers[$sKey]);
    }
   }
  }
  
  public static function clear() {
   self::$aoTimers = array();
  }
 }
?>
