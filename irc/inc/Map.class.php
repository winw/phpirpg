<?php
 class Map {
  private $sDirectory;
  private $aMapSize = array('width' => 0, 'height' => 0);
  
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
   
   print_r($oXml);
  }
 }
?>
