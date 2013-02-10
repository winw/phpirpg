<?php
 class Timer {
  private $oClosure;
  private $iDelay;
  private $iCount;
  private $iNextDelay;
  
  public function __construct($iDelay, $iCount, Closure $oClosure) {
   if (!is_numeric($iDelay) || ($iDelay < 0) || !is_numeric($iCount) || ($iCount < 0) || !is_callable($oClosure)) {
    throw new ArgumentException();
   }
   
   $this->iDelay = $iDelay;
   $this->iCount = $iCount == 0 ? null : $iCount;
   $this->oClosure = $oClosure;
   
   $this->updateDelay();
  }
  
  public function tick() {
   if (microtime(true) >= $this->iNextDelay) {
    if ($this->iCount === null) {
     $oClosure =& $this->oClosure; // Fix php bug
     $oClosure();
     $this->updateDelay();
    } else if ($this->iCount > 0) {
     $oClosure =& $this->oClosure;
     $oClosure();
     if (--$this->iCount == 0) {
      $this->iNextDelay = 0;
     } else {
      $this->updateDelay();
     }
    }
   }
  }
  
  public function isFinished() {
   return $this->iNextDelay === 0;
  }
  
  private function updateDelay() {
   $this->iNextDelay = microtime(true)+$this->iDelay;
  }
 }
?>
