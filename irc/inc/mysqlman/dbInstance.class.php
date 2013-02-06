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
 * Classe de gestion sgbd (Gestion des instances PDO)
 * @package db.mysqlman
 * @version 20110203
 * @author win (winwarrior@hotmail.com)
 * @license http://www.gnu.org/licenses/gpl.txt GNU Public License
 * @copyright Copyright (C) 2007-2011 win
 */
 class dbInstance {
  /**
  * @desc Instances PDO
  * @var array
  */
  protected static $aInstances = array();
  
  /**
  * @desc Prefixes pour les noms des classes de gestion
  * @var array
  */
  protected static $aPrefixs = array();
  
  /**
  * @desc Recuperer une instances PDO avec son nom
  *
  * @param string Nom
  *
  * @return object
  */ 
  public static function get($sNom) {
   if (!self::exists($sNom)) return false;
   return self::$aInstances[$sNom];
  }

  /**
  * @desc Vérifie si une instance PDO existe
  *
  * @param string Nom
  *
  * @return boolean
  */ 
  public static function exists($sNom) {
   return isset(self::$aInstances[$sNom]);
  }
  
  /**
  * @desc Enregistre une instance PDO
  *
  * @param string Nom
  * @param object Objet PDO
  * @param string Prefixe classes
  *
  * @return void
  */ 
  public static function create($sNom, PDO $oPdo, $sPrefix = '') {
   if (self::exists($sNom)) throw new Exception('L\'instance portant le nom "'.$sNom.'" existe deja');
   self::$aInstances[$sNom] = &$oPdo;
   if ($sPrefix !== '') $sPrefix .= '_';
   self::$aPrefixs[$sNom] = $sPrefix;
  }
  
  /**
  * @desc Supprime une instance PDO
  *
  * @param string Nom
  *
  * @return void
  */ 
  public static function delete($sNom) {
   if (!self::exists($sNom)) throw new Exception('L\'instance portant le nom "'.$sNom.'" n\'existe pas');
   unset(self::$aInstances[$sNom]);
  }
  
  /**
  * @desc Retourne le nom des instances PDO enregistrées
  *
  * @return array
  */ 
  public static function getList() {
   return array_keys(self::$aInstances);
  }
  
  /**
  * @desc Retourne le nom des prefixes
  *
  * @return array
  */ 
  public static function getPrefixs() {
   return self::$aPrefixs;
  }
 }
?>
