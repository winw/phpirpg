<?php
 class Utils {
  public static function duration($iSecs) {
   $aTab = array(
    'year' => 31536000,
    'week' => 604800,
    'day' => 86400,
    'hour' => 3600,
    'minute' => 60,
    'second' => 1
   );
   
   $sReturn = '';
   
   foreach ($aTab as $sName => $iConv) {
    if ($iSecs >= $iConv) {
     $iNb = floor($iSecs / $iConv);
     $sReturn .= $iNb.' '.$sName;
     if ($iNb > 1) {
      $sReturn .= 's';
     }
     $sReturn .= ', ';
     $iSecs -= $iNb * $iConv;
    }
   }
   
   return rtrim($sReturn, ', ');
  }
 }
?>
