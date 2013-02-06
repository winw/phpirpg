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
 * Classe de gestion sgbd (Gestion des requetes)
 * @package db.mysqlman
 * @version 20120423
 * @author win (winwarrior@hotmail.com)
 * @license http://www.gnu.org/licenses/gpl.txt GNU Public License
 * @copyright Copyright (C) 2007-2012 win
 */
 
 abstract class dbControl {
  /**
  * @desc Requete SQL + Paramètres
  * @var array
  */
  protected $aQuery = '';
  /**
  * @desc Instance PDO
  * @var object
  */
  protected $oInstance = null;
  /**
  * @desc Resultat PDO
  * @var object
  */
  protected $oQuery = null;
  /**
  * @desc Ordres pour la requete (ORDER BY xx)
  * @var array
  */
  protected $aOrder = array();
  /**
  * @desc Conditions pour la requete (WHERE xx)
  * @var array
  */
  protected $aWhere = array();
  /**
  * @desc Champs à sélectionner (SELECT xx)
  * @var array
  */
  protected $aSelect = array();
  /**
  * @desc Limite (LIMIT xx)
  * @var string
  */
  protected $sLimit = '';
  /**
  * @desc Grouper par
  * @var string
  */
  protected $sGroupBy = '';
  /**
  * @desc Mode lecture seule
  * @var boolean
  */
  protected $bReadonly = true;
  /**
  * @desc Objet de la dernière requête préparée
  * @var PDOStatment
  */
  protected $oLastPDOStatment = NULL;
  
  
  /**
  * @desc constructeur
  */ 
  public function __construct() {
   if (!isset($this->sTable) || !isset($this->sInstance) || !isset($this->aFields) || !isset($this->aPrimary) || !isset($this->sRef) || !is_array($this->aFields) || !is_array($this->aPrimary)) throw new Exception('Variables necessaires : sTable, sInstance, aFields');
   $oInstance = dbInstance::get($this->sInstance);
   if ($oInstance === false) throw new Exception('Impossible de recuperer l\'instance portant le nom "'.$this->sInstance.'"');
   $this->oInstance = &$oInstance;
  }
  
  /**
  * @desc Récupère un resultat de la base de donnée
  *
  * @param integer PDO::FETCH_* http://fr2.php.net/manual/fr/pdo.constants.php 
  *
  * @return mixed
  */ 
  private function _fetch($iType = PDO::FETCH_ASSOC) {
   $aQuery = $this->makeQuery();
   $this->oLastPDOStatment = $this->oInstance->prepare($aQuery['query']);
   call_user_func_array(array($this->oLastPDOStatment, 'setFetchMode'), func_get_args());
   if ($this->oLastPDOStatment->execute($aQuery['params'])) {
    return $this->oLastPDOStatment->fetch($iType);
   } else {
    return false;
   }
  }
  
  /**
  * @desc Récupère des resultats de la base de donnée
  *
  * @param integer PDO::FETCH_* http://fr2.php.net/manual/fr/pdo.constants.php
  *
  * @return mixed
  */ 
  private function _fetchAll($iType = PDO::FETCH_ASSOC) {
   $aQuery = $this->makeQuery();
   $this->oLastPDOStatment = $this->oInstance->prepare($aQuery['query']);
   if ($this->oLastPDOStatment->execute($aQuery['params'])) {
    return call_user_func_array(array($this->oLastPDOStatment, 'fetchAll'), func_get_args());
   } else {
    return false;
   }
  }
  
  /**
  * @desc Remet à zero la requete
  *
  * @return void
  */ 
  private function _reset() {
   $this->aSelect = $this->aQuery = $this->aOrder = $this->aWhere = array();
   $this->sLimit = $this->sGroupBy = '';
   $this->oQuery = null;
   $this->bReadonly = true;
  }
  
  /**
  * @desc Champs à sélectionner
  *
  * @param string Champ 1 à sélectionner
  * @param string Champ 2 à sélectionner
  * @param string ...
  *
  * @return object
  */ 
  public function &select() {
   $this->aSelect = (func_num_args() > 0 ? func_get_args() : array('*'));
   return $this;
  }
  
  /**
  * @desc Conditions pour la requete (WHERE xx)
  *
  * @param string Conditions au format sprintf
  * @param mixed Arguments
  * @param mixed ...
  *
  * @return object
  */ 
  public function &where() {
   $aQuery = func_get_args();
   $sQuery = array_shift($aQuery);
   $this->aWhere[] = array($sQuery, $aQuery);
   return $this;
  }
  
  /**
  * @desc Limite pour la requete (LIMIT xx)
  *
  * @param string Limite
  *
  * @return object
  */ 
  public function &limit($sLimit) {
   $this->sLimit = $sLimit;
   return $this;
  }
  
  /**
  * @desc Groupes
  *
  * @param string Groupes
  *
  * @return object
  */ 
  public function &groupby($sGroup) {
   $this->sGroupBy = $sGroup;
   return $this;
  }
  
  /**
  * @desc Ordre pour la requete (ORDER BY xx)
  *
  * @param string Ordre
  *
  * @return object
  */ 
  public function &order($sOrder) {
   $this->aOrder[] = $sOrder;
   return $this;
  }
  
  /**
  * @desc Mode lecture seule
  *
  * @param boolean Lecture seule, true / false
  *
  * @return object
  */ 
  public function &readonly($bMode = true) {
   $this->bReadonly = (bool) $bMode;
   return $this;
  }
  
  /**
  * @desc Mode inscriptible
  *
  * @param boolean Inscriptible, true / false
  *
  * @return object
  */ 
  public function &writable($bMode = true) {
   $this->bReadonly = !$bMode;
   return $this;
  }
  
  /**
  * @desc Créé la requete SQL inline en fonction des parametres enregistrés
  *
  * @return string
  */ 
  
  protected function makeQuery() {
   if ($this->aQuery) return $this->aQuery;
   $aParams = array();
   $sQuery  = 'SELECT '.implode(',', $this->aSelect).' FROM '.$this->protectField($this->sTable).' ';
   if ($this->aWhere) {
    $sQuery .= 'WHERE ';
    $iNb = count($this->aWhere);
    foreach ($this->aWhere AS $aQueryPart) {
     $sQuery .= '('.$aQueryPart[0].')';
     if (--$iNb > 0) {
      $sQuery .= ' AND ';
     } else {
      $sQuery .= ' ';
     }
     $aParams = array_merge($aParams, $aQueryPart[1]); 
    }
   }
   if ($this->aOrder) {
    $sQuery .= 'ORDER BY '.implode(', ', $this->aOrder).' ';
   }
   if ($this->sGroupBy) {
    $sQuery .= 'GROUP BY '.$this->sGroupBy;
   }
   if ($this->sLimit) {
    $sQuery .= 'LIMIT '.$this->sLimit;
   }
   $this->aQuery = array('query' => $sQuery, 'params' => $aParams);
   return $this->aQuery;
  }
  
  /**
  * @desc Retourne les informations de debug de la dernière requête exécutée
  *
  * @return string
  */ 
  public function __toString() {
   if ($this->oLastPDOStatment !== NULL) {
    ob_start();
    $this->oLastPDOStatment->debugDumpParams();
    return ob_get_clean();
   } else {
    return '';
   }
  }
  
  /**
  * @desc Resultat de la requete
  *
  * @return object
  */ 
  public function fetch() {
   $oReturn = false;
   if ($this->sLimit === '') {
    $this->sLimit = 1; // Performances
   }
   if ($this->bReadonly) {
    $oReturn = $this->_fetch(PDO::FETCH_CLASS, 'dbPrimitiveObject');
   } else if ($oRow = $this->_fetch(PDO::FETCH_ASSOC)) {
    $oReturn = new dbObject($this, $oRow);
   }

   $this->_reset();
   return $oReturn;
  }
  
  /**
  * @desc Protège le nom d'un champ/table
  *
  * @return object
  */ 
  public function protectField($sField) {
   if ($this::DRIVER == 'mysql') {
    return '`'.$sField.'`';
   } else {
    return $sField;
   }
  }
  

  
  /**
  * @desc Resultats de la requete
  *
  * @return array
  */ 
  public function fetchAll() {
   $aReturn = array();
   if ($this->bReadonly) {
    $aReturn =  $this->_fetchAll(PDO::FETCH_CLASS, 'dbPrimitiveObject');
   } else {
    $aResults = $this->_fetchAll(PDO::FETCH_ASSOC);
    foreach ($aResults AS $oResult) {
     $aReturn[] = new dbObject($this, $oResult);
    }
   }
   $this->_reset();
   return $aReturn;
  }
  
  /**
  * @desc Créé un enregistrement
  *
  * @return object
  */ 
  public function create() {
   $this->writable();
   return new dbObject($this, false);
  }
  
  /**
  * @desc Retourne les clés primaires de la table
  *
  * @return array
  */ 
  public function getPrimaryKeys() {
   return $this->aPrimary;
  }
  
  /**
  * @desc Retourne les types des champs (format sprintf)
  *
  * @return array
  */ 
  public function getTypes() {
   return $this->aTypes;
  }

  /**
  * @desc Retourne la reference pour identifier l'enregistrement (champ en auto increment)
  *
  * @return string
  */ 
  public function getRefKey() {
   return $this->sRef;
  }
  
  /**
  * @desc Retourne le nom des champs, et leur valeur par defaut
  *
  * @return array
  */ 
  public function getFields() {
   return $this->aFields;
  }
  
  /**
  * @desc Retourne les types des champs (format humainement compréhensible)
  *
  * @return array
  */ 
  public function getHTypes() {
   $aFields = array();
   foreach ($this->aTypes AS $sName => $sType) {
    if ($sType == '%s') $aFields[$sName] = 'string';
    else if (preg_match('/^%.*f$/i', $sType)) $aFields[$sName] = 'float';
    else if (preg_match('/^%.*[diu]$/i', $sType)) $aFields[$sName] = 'integer';
    else $aFields[$sName] = 'string';
   }
   return $aFields;
  }
  
  /**
  * @desc Retourne le nom de la table utilisée
  *
  * @return string
  */ 
  public function getTable() {
   return $this->sTable;
  }
  
  /**
  * @desc Retourne l'instance pdo utilisée
  *
  * @return object
  */ 
  public function &getInstance() {
   return $this->oInstance;
  }
  
 }
?>
