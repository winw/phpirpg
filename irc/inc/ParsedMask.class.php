<?php
 class parsedMask {
  private $sHost = '';
  private $sNick = '';
  private $sUser = '';
  private $sMask = '';
  
  public function __construct($sMask) {
   $this->sMask = $sMask;
   
   $iUser = strpos($sMask, '!');
   $iHost = strpos($sMask, '@', $iUser+1);
   
   if ($iUser !== false && $iHost !== false) {
    $this->sNick = substr($sMask, 0, $iUser);
    $this->sUser = substr($sMask, $iUser+1, $iHost-$iUser-1);
    $this->sHost = substr($sMask, $iHost+1);
   }
  }
  
  public function getHost() {
   return $this->sHost;
  }
  
  public function getNick() {
   return $this->sNick;
  }
  
  public function getUser() {
   return $this->sUser;
  }
  
  public function __toString() {
   return $this->sMask;
  }
 }
?>
