<?php
 /**
 *   Copyright (C) 2011 BONNIN Florian (winwarrior@hotmail.com)
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

 /**
 * Classe de détection de pollution http
 * @package security.requestpollution
 * @version 20110607
 * @author BONNIN Florian (winwarrior@hotmail.com)
 * @license http://www.gnu.org/licenses/gpl.txt GNU Public License
 * @copyright Copyright (C) 2011 BONNIN Florian
 */
 class security_requestPollution {
  /**
  * @desc Séparateur des paramètres get
  * @var string
  */
  const ARG_SEPARATOR = '&';
  /**
  * @desc Mode de détection
  * @var integer
  */
  const MODE_GET = 0x01;
  
  /**
  * @desc Recherche si de la pollution est présente et retourne un tableau des paramètres dupliqués
  *
  * @param integer Mode de détection
  *
  * @return array
  */ 
  public static function check($iMode = self::MODE_GET) {
   if (!isset($_SERVER['QUERY_STRING'])) {
    return false;
   }
   $aArgs = explode(self::ARG_SEPARATOR, rawurldecode($_SERVER['QUERY_STRING']));
   $aKeys = $aDuplicate = array();

   foreach ($aArgs AS $sArg) {
    $sKey = current(explode('=', $sArg));

    if (strpos($sKey, '[') === false) { // Si ce n'est pas un array
     if (in_array($sKey, $aKeys)) { // Si la clé est en double
      if (!in_array($sKey, $aDuplicate)) {
       $aDuplicate[] = $sKey; // Si la clé n'est pas déjà dans notre tableau de clés dubliquées
      }
     } else {
      $aKeys[] = $sKey; // Sinon on ajoute la clé dans notre tableau de clés trouvées
     }
    }
   }
   
   return $aDuplicate;
  }

 }
?>
