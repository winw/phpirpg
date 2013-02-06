<?php
 /**
 *   Copyright (C) 2007-2013 win (winwarrior@hotmail.com)
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
 * Classe de gestion sgbd (Objet de résultat)
 * @package db.mysqlman
 * @version 20130130
 * @author win (winwarrior@hotmail.com)
 * @license http://www.gnu.org/licenses/gpl.txt GNU Public License
 * @copyright Copyright (C) 2007-2013 win
 */
 class dbObject {
  /**
  * @desc Nom de la classe permettant de ne pas proteger la chaine de caractere passée
  * @var string
  */
  const CLASS_ESCAPE = 'dbDontEscapeString';
  
  /**
  * @desc Tableau des données
  * @var array
  */
  protected $aCurrent = array();
  
  /**
  * @desc Tableau des champs changés
  * @var array
  */
  protected $aFieldsUpdated = array();
  
  /**
  * @desc Tableau des données avant modification
  * @var array
  */
  protected $aPCurrent = array();
  
  /**
  * @desc True si l'enregistrement existe physiquement (donc pas en cours de création)
  * @var boolean
  */
  protected $bExists = false;
  
  /**
  * @desc Nom de la table courante
  * @var string
  */
  protected $sTable = '';
  
  /**
  * @desc Tableau des clés primaires
  * @var array
  */
  protected $aPrimary = array();
  
  /**
  * @desc Tableau des types des champs (format sprintf)
  * @var array
  */
  protected $aTypes = array();
  
  /**
  * @desc Nom du champ de reference pour identifier l'enregistrement
  * @var string
  */
  protected $sRef = '';

  /**
  * @desc Objet de la classe de control courante
  * @var object
  */
  private $oControl = null;
  
  /**
  * @desc Objet de l'instance PDO courante
  * @var object
  */
  private $oInstance = null;
  
  /**
  * @desc True pour activer l'écriture automatique (plus besoin de faire $object->save())
  * @var boolean
  */
  private $bAutoWrite = false;

  /**
  * @desc Initialisation de l'objet
  *
  * @param object Objet de controle (dbControl)
  * @param mixed Tableau des résultats à assigner // false pour créer un nouvel enregistrement
  *
  * @return void
  */ 
  public function __construct(dbControl &$oControl, $aResult = false) {
   $this->oControl = &$oControl;
   $this->aCurrent = $oControl->getFields();
   $this->sTable = $oControl->getTable();
   $this->aPrimary = $oControl->getPrimaryKeys();
   $this->aTypes = $oControl->getTypes();
   $this->sRef = $oControl->getRefKey();
   $this->oInstance = &$oControl->getInstance();
   if (is_array($aResult)) {
    foreach ($aResult AS $sKey => $sValue) {
     $this->aCurrent[$sKey] = $sValue;
    }
    $this->aPCurrent = $this->aCurrent;
    $this->bExists = true;
   }
  }

  /**
  * @desc Destructeur, sauvegarde l'enregistrement si l'enregistrement automatique a été activé
  *
  * @return void
  */ 
  public function __destruct() {
   if ($this->bAutoWrite) {
    $this->save(false);
   }
  }
  
  /**
  * @desc Active / Désactive l'écriture automatique
  *
  * @param boolean True pour activer l'écriture automatique
  *
  * @return void
  */ 
  public function setAutoWrite($bChoice) {
   $this->bAutoWrite = (bool) $bChoice;
  }
  
  /**
  * @desc Ecriture des données dans la table
  *
  * @param boolean True pour forcer l'écriture dans la table
  *
  * @return boolean
  */ 
  public function save($bForce = false) {
   if ((count($this->aFieldsUpdated) == 0) && !$bForce) {
    return true;   
   }

   if ($this->bExists) {
    $aUpdate = $this->makeUpdate();
    // @todo : revoir le comportement avec l'autowrite en cas d'erreur, $this->aFieldsUpdated n'est pas réinitialisé.
    $bRes = (bool) $this->oInstance->prepare($aUpdate['query'])->execute($aUpdate['params']);
   } else {
    $aInsert = $this->makeInsert();
    // @todo : pareil que le commentaire du dessus
    $bRes = (bool) $this->oInstance->prepare($aInsert['query'])->execute($aInsert['params']);
    if ($bRes) {
     if ($this->sRef) {
      $this->aCurrent[$this->sRef] = $this->oInstance->lastInsertId();
     }
     $this->bExists = true;
    }
   }
   
   if ($bRes) {
    $this->aPCurrent = $this->aCurrent;
   }
   
   $this->aFieldsUpdated = array();
   return $bRes;
  }
  
  /**
  * @desc Supprime l'enregistrement courant
  *
  * @return boolean
  */ 
  public function delete() {
   $this->aFieldsUpdated = array();
   if ($this->bExists) {
    $aDelete = $this->makeDelete();
    return (bool) $this->oInstance->prepare($aDelete['query'])->execute($aDelete['params']);
   }
   throw new Exception('Impossible de supprimer un enregistrement qui n\'existe pas');
  }
  
  /**
  * @desc Retourne le tableau de l'enregistrement courant
  *
  * @return array
  */
  public function getArray() {
   return $this->aCurrent;
  }
  
  /**
  * @desc Assigne une valeur à un champ
  *
  * @param string Nom du champ
  * @param string Valeur
  *
  * @return void
  */ 
  public function __set($sField, $sValue) {
   if (!$this->fieldExists($sField)) throw new Exception('Le champ "'.$sField.'" n\'existe pas');
   $bDontEscape = (is_object($sValue) && (get_class($sValue) == dbObject::CLASS_ESCAPE));
   if (!is_scalar($sValue) && !$bDontEscape) throw new Exception('Type incorrect sur le champ "'.$sField.'"');
   $sTmp = ($bDontEscape ? $sValue : sprintf($this->aTypes[$sField], $sValue));
   if ($bDontEscape || ($sTmp != $this->aCurrent[$sField])) {
    $this->aCurrent[$sField] = ($bDontEscape ? $sValue : sprintf($this->aTypes[$sField], $sValue));
    $this->aFieldsUpdated[$sField] = true;
   }
  }
  
  /**
  * @desc Récupere la valeur à un champ
  *
  * @param string Nom du champ
  *
  * @return string
  */ 
  public function __get($sField) {
   if (!$this->fieldExists($sField)) throw new Exception('Le champ "'.$sField.'" n\'existe pas');
   return $this->aCurrent[$sField];
  }
  
  /**
  * @desc Retourne true si le champ existe
  *
  * @param string Nom du champ
  *
  * @return boolean
  */ 
  public function __isset($sField) {
   return $this->fieldExists($sField);
  }
  
  /**
  * @desc Anti clone
  */ 
  public function __clone() {
   throw new Exception('Impossible de cloner cet objet');
  }
  
  /**
  * @desc Indique si le champ existe
  *
  * @param string Nom du champ
  *
  * @return bool
  */ 
  protected function fieldExists($sField) {
   return array_key_exists($sField, $this->aCurrent);
  }
  
  /**
  * @desc Génere une requete insert pour l'enregistrement courant
  *
  * @return array
  */ 
  private function makeInsert() {
   $sQuery = 'INSERT INTO '.$this->oControl->protectField($this->sTable).' ';
   $sQuery .= '('.implode(',', array_map(array($this->oControl, 'protectField'), array_keys($this->aCurrent))).') ';
   $aSet = $aParams = array();
   foreach ($this->aCurrent AS $sKey => $mValue) {
    if (is_object($mValue) && (get_class($mValue) == dbObject::CLASS_ESCAPE)) {
     $aSet[] = strval($mValue);
    } else {
     $aSet[] = '?';
     $aParams[] = $mValue;
    }
   }
   $sQuery .= 'VALUES ('.implode(', ', $aSet).')';
   return array('query' => $sQuery, 'params' => $aParams);
  }
  
  /**
  * @desc Génere une requete update pour l'enregistrement courant
  *
  * @return array
  */ 
  private function makeUpdate() {
   $sQuery = 'UPDATE '.$this->oControl->protectField($this->sTable).' SET ';
   $aSet = $aParams = array();
   foreach (array_keys($this->aFieldsUpdated) AS $sKey) {
    $mValue = $this->aCurrent[$sKey];
    if (is_object($mValue) && (get_class($mValue) == dbObject::CLASS_ESCAPE)) {
     $aSet[] = $this->oControl->protectField($sKey).' = '.strval($mValue);
    } else {
     $aSet[] = $this->oControl->protectField($sKey).' = ?';
     $aParams[] = $mValue;
    }
   }
   $sQuery .= implode(', ', $aSet);
   $aWhere = $this->makeWhere();
   $sQuery .= $aWhere['query'];
   $aParams = array_merge($aParams, $aWhere['params']);

   return array('query' => $sQuery, 'params' => $aParams);
  }
  
  /**
  * @desc Génere la condition where pour une requete update
  *
  * @return array
  */ 
  private function makeWhere() {
   $aQuery = $aParams = array();
   if ($this->sRef) {
    $aQuery[] = $this->oControl->protectField($this->sRef).' = ?';
    $aParams[] = $this->aPCurrent[$this->sRef];
   } else if ($this->aPrimary) {
    foreach ($this->aPrimary AS $sKey) {
     $mValue = $this->aPCurrent[$sKey];
     if (is_object($mValue) && (get_class($mValue) == dbObject::CLASS_ESCAPE)) {
      $aQuery[] = $this->oControl->protectField($sKey).' = '.strval($mValue);
     } else if (is_null($mValue)) {
      $aQuery[] = $this->oControl->protectField($sKey).' IS NULL';
     } else {
      $aQuery[] = $this->oControl->protectField($sKey).' = ?';
      $aParams[] = $mValue;
     }
    }
   } else {
    foreach ($this->aPCurrent AS $sKey => $mValue) {
     if (!isset($this->aTypes[$sKey])) {
      continue;
     }
     if (is_object($mValue) && (get_class($mValue) == dbObject::CLASS_ESCAPE)) {
      $aQuery[] = $this->oControl->protectField($sKey).' = '.strval($mValue);
     } else if (is_null($mValue)) {
      $aQuery[] = $this->oControl->protectField($sKey).' IS NULL';
     } else {
      $aQuery[] = $this->oControl->protectField($sKey).' = ?';
      $aParams[] = $mValue;
     }
    }
   }

   if ($aQuery){
    return array('query' => ' WHERE ('.implode(' AND ', $aQuery).') ', 'params' => $aParams);
   } else {
    return array('query' => '', 'params' => array());
   }
  }
  
  /**
  * @desc Génere une requete de suppression pour l'enregistrement courant
  *
  * @return array
  */ 
  private function makeDelete() {
   $sQuery = 'DELETE FROM '.$this->oControl->protectField($this->sTable).' ';
   $aWhere = $this->makeWhere();
   $sQuery .= $aWhere['query'];
   return array('query' => $sQuery, 'params' => $aWhere['params']);
  }
 }
?>
