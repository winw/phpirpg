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
 * Classe de gestion sgbd (Objet de résultat ayant quelques fonctions supplémentaires)
 * @package db.mysqlman
 * @version 20120423
 * @author win (winwarrior@hotmail.com)
 * @license http://www.gnu.org/licenses/gpl.txt GNU Public License
 * @copyright Copyright (C) 2007-2012 win
 */
 class dbPrimitiveObject implements ArrayAccess {
  public function offsetExists($sKey) {
   return isset($this->{$sKey});
  }
  
  public function offsetSet($sKey, $mValue) {
   $this->{$sKey} = $mValue;
  }

  public function offsetUnset($sKey) {
   unset($this->{$sKey});
  }
  
  public function offsetGet($sKey) {
   return isset($this->{$sKey}) ? $this->{$sKey} : false;
  }
  
  public function __isset($sKey) {
   return isset($this->{$sKey});
  }
  
  public function __get($sKey) {
   return isset($this->{$sKey}) ? $this->{$sKey} : false;
  }
  
  public function __set($sKey, $mValue) {
   $this->{$sKey} = $mValue;
  }

  public function __unset($sKey) {
   unset($this->{$sKey});
  }
 }
?>
