<?php
 abstract class Module extends IrcCommands {
  private $oCore;
  
  public final function __construct(Core &$oCore) {
   $this->oCore = $oCore;
  }
  
  protected function writeLine($sLine) {
   $this->oCore->writeLine($sLine);
  }
 }
?>
