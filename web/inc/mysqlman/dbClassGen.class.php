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
 * Classe de gestion sgbd (generation de fichiers par rapport à la structure)
 * @package db.mysqlman
 * @version 20121012
 * @author win (winwarrior@hotmail.com)
 * @license http://www.gnu.org/licenses/gpl.txt GNU Public License
 * @copyright Copyright (C) 2007-2012 win
 */
 class dbClassGen {
  /**
  * @desc Prefix du nom des tables
  * @var string
  */
  const CLASS_PREFIX = 'db';
  /**
  * @desc Repertoire où seront stockés les fichiers de classe
  * @var string
  */
  public static $sPath = 'db/';
  /**
  * @desc Instance PDO utilisée
  * @var object
  */
  private static $oInstance = null;
  
  /**
  * @desc Créé les fichiers de classe à partir d'une instance dbInstance
  *
  * @param string Nom de l'instance
  * @param string Prefixe classes
  *
  * @return boolean
  */ 
  public static function fromInstanceName($sName, $sPrefix = '') {
   if (!dbInstance::exists($sName)) throw new Exception('L\'instance portant le nom "'.$sName.'" n\'existe pas');
   self::$oInstance = dbInstance::get($sName);
   $aTables = self::getTables();

   foreach ($aTables AS $sTable) {
    $aStructure = self::getTableStructure($sTable);
    $sPath = self::$sPath.self::CLASS_PREFIX.self::sanitizeTableName($sPrefix.$sTable).'.db.php';
    if (!@file_put_contents($sPath, self::getClassContents($sName, $sPrefix, $sTable, $aStructure))) throw new Exception('Impossible d\'ecrire le fichier "'.$sPath.'"');
   }
   return true;
  }
  
  /**
  * @desc Genere le contenu de la classe
  *
  * @param string Nom de l'instance
  * @param string Prefix de la bdd
  * @param string Nom de la table
  * @param Array Données de generation
  *
  * @return string
  */ 
  private static function getClassContents($sInstance, $sPrefix, $sTable, Array $aData) {
   $sBuffer  = "<?php\n";
   $sBuffer .= 'class '.self::CLASS_PREFIX.self::sanitizeTableName($sPrefix.$sTable)." extends dbControl {\n";
   $sBuffer .= " const DRIVER = '".self::getDriverName()."';\n";
   $sBuffer .= " protected \$sTable = '{$sTable}';\n";
   $sBuffer .= " protected \$sInstance = '{$sInstance}';\n";
   $sBuffer .= " protected \$sRef = '{$aData['ref']}';\n";
   $sBuffer .= ' protected $aFields = '.var_export($aData['fields'], true).";\n";
   $sBuffer .= ' protected $aPrimary = '.var_export($aData['primary'], true).";\n";
   $sBuffer .= ' protected $aTypes = '.var_export($aData['type'], true).";\n";
   $sBuffer .= "}\n";
   $sBuffer .= "?>\n";
   return $sBuffer;
  }
  
  /**
  * @desc Formate le nom d'une table pour qu'elle soit utilisable
  *
  * @param string Nom de la table
  *
  * @return string
  */ 
  private static function sanitizeTableName($sName) {
   $aTab = preg_split("/[^a-z0-9]+/i", $sName, -1, PREG_SPLIT_NO_EMPTY);
   $sReturn = '';
   foreach ($aTab AS $sTab) $sReturn .= ucfirst($sTab);
   return $sReturn;
  }
  
  /**
  * @desc Retourne le nom des tables de la bdd
  *
  * @return Array
  */ 
  private static function getTables() {
   if (self::getDriverName() == 'mysql') {
    return self::query('SHOW TABLES');
   } else if (self::getDriverName() == 'sqlite') {
    return self::query('SELECT name FROM SQLite_master WHERE type IN ("table", "view")');
   }
   throw new Exception('Le SGBD "'.self::getDriverName().'" n\'est pas pris en charge');
  }
  
  /**
  * @desc Retourne la structure de la table
  *
  * @param string Nom de la table
  *
  * @return Array
  */ 
  private static function getTableStructure($sName) {
   $aReturn = $aPrimary = $aType = array();
   $sRef = '';
   if (self::getDriverName() == 'mysql') {
    $aDescribe = self::query('SHOW COLUMNS FROM `'.$sName.'`', PDO::FETCH_ASSOC);

    foreach ($aDescribe AS $aRes) {
     $aReturn[$aRes['Field']] = (string) $aRes['Default'];
     if (stripos($aRes['Extra'], 'auto_increment') !== false) $sRef = $aRes['Field'];
     if ($aRes['Key'] == 'PRI') $aPrimary[] = $aRes['Field'];
     if (preg_match('/^(tiny|small|medium|big|)int\((\d+)\)/i', $aRes['Type'], $aRet)) {
      $bUnsigned = (stripos($aRes['Type'], 'unsigned') !== false);
      $bFill = (stripos($aRes['Type'], 'zerofill') !== false);
      if ($bUnsigned && $bFill) $aType[$aRes['Field']] = '%0'.$aRet[2].'u';
      else if ($bUnsigned) $aType[$aRes['Field']] = '%u';
      else if ($bFill) $aType[$aRes['Field']] = '%0'.$aRet[2].'d';
      else $aType[$aRes['Field']] = '%d';
     }
     else if (preg_match('/^decimal\((\d+),(\d+)\)/i', $aRes['Type'], $aRet)) {
      $aType[$aRes['Field']] = '%.'.$aRet[2].'F';
     }
     else if (preg_match('/^float\((\d+),(\d+)\)/i', $aRes['Type'], $aRet)) {
      $aType[$aRes['Field']] = '%.'.$aRet[2].'F';
     }
     else $aType[$aRes['Field']] = '%s';
    }
   } if (self::getDriverName() == 'sqlite') {
    $aDescribe = self::query('SELECT sql FROM SQLite_master WHERE type IN ("table", "view") AND name = "'.$sName.'"');
    if ($aDescribe) {
     if (preg_match('#^CREATE TABLE "[^"]+"\s+\(\s*(.*)\s*\)\s*$#is', $aDescribe[0], $aRegs)) {
      $aTokens = array_filter(
       array_map('trim', explode(',', $aRegs[1])),
       function($sData){ return $sData !== ''; }
      );
      $aFields = array();
      
      foreach ($aTokens AS $sToken) {
       if (substr($sToken, 0, 1) == '"') {
        for ($i = 1, $j = strlen($sToken); $i < $j; ++$i) {
         if ($sToken[$i] == '"') {
          $aFields[] = substr($sToken, 1, $i-1);
          break;
         }
        }
       }
      }
      
      if ($aFields) {
       foreach ($aFields AS $sField) {
        $aReturn[$sField] = '';
        $aType[$sField] = '%s';
       }
      }
     }
    }
   }

   return array(
    'primary' => $aPrimary,
    'ref' => $sRef,
    'fields' => $aReturn,
    'type' => $aType
   );
  }
  
  /**
  * @desc Retourne le nom du driver utilisé
  *
  * @return String
  */ 
  private static function getDriverName() {
   return self::$oInstance->getAttribute(PDO::ATTR_DRIVER_NAME);
  }
  
  /**
  * @desc Retourne un objet de ressource PDO
  *
  * @param string Requete SQL
  * @param integer PDO::FETCH_* http://fr2.php.net/manual/fr/pdo.constants.php
  *
  * @return Array
  */ 
  private static function query($sQuery, $iMode = PDO::FETCH_COLUMN) {
   return self::$oInstance->query($sQuery)->fetchAll($iMode);
  }
 }
?>
