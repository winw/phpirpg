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
  
  public static function between($i1, $i2, $i3) {
   return ($i1 >= $i2) && ($i1 <= $i3);
  }
  
  public static function expressionToRatio($sExpression) {
   $aRatio = explode('/', $sExpression);
   if (isset($aRatio[1])) {
    return round($aRatio[1] / $aRatio[0]);
   } else {
    return (int)$sExpression;
   }
  }
  
  /*
   * @desc Permet d'assigner une clé en fonction de la valeur d'un champ
  *
  * @param $aoElems tableau d'objets ou d'array à analyser
  * @param $sKey clé à utiliser pour la construction du tableau
  * @param $bMulti true si la clée utilisée peut être multiple (renvoie donc un tableau de tableau)
  *
  * @return array
  */
  public static function assignInKey(array $aoElems, $sKey = 'id', $bMulti = false) {
   $aRet = array();
  
   foreach ($aoElems as $oElem) {
    if ($bMulti) {
     $aRet[is_object($oElem) ? $oElem->{$sKey} : $oElem[$sKey]][] = $oElem;
    } else {
     $aRet[is_object($oElem) ? $oElem->{$sKey} : $oElem[$sKey]] = $oElem;
    }
   }
  
   return $aRet;
  }
 }
?>
