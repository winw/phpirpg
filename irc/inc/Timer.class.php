<?php
 class Timer {
  private $mCallback;
  private $iDelay;
  private $iCount;
  private $iNextDelay;
  
  public function __construct($iDelay, $iCount, $mCallback) {
   if (!is_numeric($iDelay) || ($iDelay < 0) || !is_numeric($iCount) || ($iCount < 0) || !is_callable($mCallback)) {
    throw new ArgumentException();
   }
   
   $this->iDelay = $iDelay;
   $this->iCount = $iCount == 0 ? null : $iCount;
   $this->mCallback = $mCallback;
   
   $this->updateDelay();
  }
  
  public function tick() {
   if (microtime(true) >= $this->iNextDelay) {
    if ($this->iCount === null) {
     $this->updateDelay();
     call_user_func($this->mCallback); // Fix php bug
    } else if ($this->iCount > 0) {
     if (--$this->iCount == 0) {
      $this->iNextDelay = 0;
     } else {
      $this->updateDelay();
     }
     call_user_func($this->mCallback); // Fix php bug
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
