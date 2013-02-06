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
 * Classe de gestion sgbd (benchmark des statements)
 * @package db.mysqlman
 * @version 20120518
 * @author win (winwarrior@hotmail.com)
 * @license http://www.gnu.org/licenses/gpl.txt GNU Public License
 * @copyright Copyright (C) 2007-2012 win
 */
 class dbCustomStatement extends PDOStatement {
  private $oParent = null;
  
  private function __construct(PDO $oParent) {
   $this->oParent = $oParent;
  }
  
  public function execute() {
   $aArgs = func_get_args();
   $iMicroTime = microtime(true);
   $mReturn = call_user_func_array(array('parent', __FUNCTION__), $aArgs);
   array_unshift($aArgs, $this->queryString);
   $this->oParent->logAdd(array(
    'args' => $aArgs,
    'time' => (microtime(true)-$iMicroTime)
   ));
   return $mReturn;
  }
  
 }
?>
