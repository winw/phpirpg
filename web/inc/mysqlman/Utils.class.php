<?php
 class Utils {
  public static $sCharset = 'utf-8';
  public static $iCompat = ENT_QUOTES;
  
  public static function number($number)
  {
    $number = number_format($number, 0, ',', ' ');
    return str_replace(" ", "&nbsp;", $number);
  }
  
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
  
  public static function hte($sText, $sCharset = false) {
  if ($sCharset === false) $sCharset = self::$sCharset;
  return htmlentities($sText, self::$iCompat, $sCharset);
  }
  
  public static function getPages($iNow, $iResults, $iNums = 5) {
   $iMoit = floor($iNums/2);
   $iDebut = ($iNow-$iMoit);
   $iFin = ($iNow+$iMoit);
   $aRet = array();
   if ($iFin > $iResults) {
    $iFin = $iResults;
    $iDebut -= $iMoit;
   }
   if ($iDebut < 1) {
    $iDebut = $iNow = 1;
    $iFin += $iMoit;
   }
   
   return range($iDebut, min($iFin, $iResults));
  }
  
 }
?>