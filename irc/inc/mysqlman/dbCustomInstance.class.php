<?php
 /**
 *   Copyright (C) 2007-2012 win (winwarrior@hotmail.com)
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
 * Classe de gestion sgbd (benchmark des appels pdo)
 * @package db.mysqlman
 * @version 20120520
 * @author win (winwarrior@hotmail.com)
 * @license http://www.gnu.org/licenses/gpl.txt GNU Public License
 * @copyright Copyright (C) 2007-2012 win
 */
 
 class dbCustomInstance extends PDO {
  private $aaQuerys = array();
  private $iTotalTime = 0;
  
  public function __construct() {
   $aArgs = func_get_args();
   call_user_func_array(array('parent', __FUNCTION__), $aArgs);
   parent::setAttribute(PDO::ATTR_STATEMENT_CLASS, array('dbCustomStatement', array($this)));
  }
  
  public function exec() {
   $iMicroTime = microtime(true);
   $aArgs = func_get_args();
   $mReturn = call_user_func_array(array('parent', __FUNCTION__), $aArgs);
   $this->logAdd(array(
    'args' => $aArgs,
    'time' => (microtime(true)-$iMicroTime)
   ));
   return $mReturn;
  }
  
  public function query() {
   $iMicroTime = microtime(true);
   $aArgs = func_get_args();
   
   $mReturn = call_user_func_array(array('parent', __FUNCTION__), $aArgs);
   $aLog = array(
    'args' => $aArgs,
    'time' => (microtime(true)-$iMicroTime)
   );
   
   if (stripos($aArgs[0], 'SELECT ') === 0) {
    $aLog['explain'] = call_user_func_array(array('parent', __FUNCTION__), array('EXPLAIN '.$aArgs[0]))->fetchAll(PDO::FETCH_ASSOC);
   }

   $this->logAdd($aLog);
   return $mReturn;
  }
  
  public function getQuerys() {
   return $this->aaQuerys;
  }
  
  public function logAdd(array $aLog) {
   if (isset($aLog['time'], $aLog['args'])) {
    $this->aaQuerys[] = $aLog;
    $this->iTotalTime += $aLog['time'];
   }
  }
  
  public function getTotalTime() {
   return $this->iTotalTime;
  }
  
 }
?>
