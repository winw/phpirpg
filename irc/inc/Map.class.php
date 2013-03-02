<?php
 class MapArea {
  private $iX1;
  private $iY1;
  private $iX2;
  private $iY2;
  
  private $sZone;
  
  public function __construct($iX1, $iY1, $iX2, $iY2, $sZone) {
   $this->iX1 = $iX1;
   $this->iY1 = $iY1;
   $this->iX2 = $iX2;
   $this->iY2 = $iY2;
   $this->sZone = $sZone;
  }
  
  public function in($iX, $iY) {
   return Utils::between($iX, $this->iX1, $this->iX2) && Utils::between($iY, $this->iY1, $this->iY2);
  }
  
  public function getZone() {
   return $this->sZone;
  }
  
 }
 
 class Map {
  private $sDirectory;
  private $aMapSize = array('width' => 0, 'height' => 0);
  private $aMap;
  private $aoAreas = array();
  private $aiPlayers = array();
  private $oXml;
  
  public function __construct($sDirectory) {
   if (!is_dir($sDirectory)) {
    throw new Exception('No such directory');
   }
   
   $this->sDirectory = realpath($sDirectory);
   
   $this->loadMap();
  }
  
  private function loadMap() {
   if (!file_exists($sXml = $this->sDirectory.'/map.xml')) {
    throw new Exception('No such file');
   }
   
   $oXml = simplexml_load_file($sXml);
   
   if (!$oXml) {
    throw new Exception('Failed to load xml file');
   }

   if (!isset($oXml->attributes()->width) || !isset($oXml->attributes()->height)) {
    throw new Exception('Map file must contain width and height attributes');
   }
   
   $this->oXml = $oXml;
   
   $this->aMapSize['width'] = (int)$oXml->attributes()->width;
   $this->aMapSize['height'] = (int)$oXml->attributes()->height;
   
   // Loading areas
   foreach ($oXml->areas->area as $oArea) {
    if ($oArea->attributes()->shape == 'rect') { // @todo : checks
     list($iX1, $iY1, $iX2, $iY2) = explode(',', $oArea->attributes()->coords);
     $this->aoAreas[] = new MapArea($iX1, $iY1, $iX2, $iY2, (string)$oArea->attributes()->href);
    }
   }
  }
  
  public function getWidth() {
   return $this->aMapSize['width'];
  }
  
  public function getHeight() {
   return $this->aMapSize['height'];
  }
  
  public function getXml() {
   return $this->oXml;
  }
  
  public function getZone($iX, $iY) {
   foreach ($this->aoAreas as &$oArea) {
    if ($oArea->in($iX, $iY)) {
     return $oArea->getZone();
    }
   }
  }
 }
?>
