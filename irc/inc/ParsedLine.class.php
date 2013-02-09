<?php
 class parsedLine implements \ArrayAccess, \Countable  {
  private $sType = '';
  private $aData = array();
  private $sRaw = '';
  private $iNbData = 0;
  
  public function __construct($sRaw, $sType, array $aData = array()) {
   $this->sRaw = $sRaw;
   $this->sType = $sType;
   $this->aData = $aData;
   $this->iNbData = count($aData);
  }
  
  public function getType() {
   return $this->sType;
  }
  
  public function offsetExists($mOffset) {
   return isset($this->aData[$mOffset]);
  }
  
  public function offsetGet($mOffset) {
   return $this->aData[$mOffset];
  }
  
  public function offsetSet($mOffset, $mValue) {
  }
  
  public function offsetUnset($mOffset) {
  }
  
  public function count() {
   return $this->iNbData;
  }
  
  public function __toString() {
   return $this->sRaw;
  }
 }
?>
