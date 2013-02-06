<?php
 /**
 *   Copyright (C) 2007-2011 win (winwarrior@hotmail.com)
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
 * Classe de gestion sgbd (Gestion des requetes)
 * @package db.mysqlman
 * @version 20110225
 * @author win (winwarrior@hotmail.com)
 * @license http://www.gnu.org/licenses/gpl.txt GNU Public License
 * @copyright Copyright (C) 2007-2011 win
 */
 
 class dbDontEscapeString {
  private $sVal = '';
  public function __construct($sVal) {
   $this->sVal = $sVal;
  }
  public function __toString() {
   return $this->sVal;
  }
 }
?>
