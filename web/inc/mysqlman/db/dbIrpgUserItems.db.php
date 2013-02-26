<?php
class dbIrpgUserItems extends dbControl {
 const DRIVER = 'mysql';
 protected $sTable = 'irpg_user_items';
 protected $sInstance = 'phpirpg';
 protected $sRef = '';
 protected $aFields = array (
  'id_irpg_user' => '',
  'type' => '',
  'level' => '',
  'name' => '',
  'options' => '',
);
 protected $aPrimary = array (
  0 => 'id_irpg_user',
  1 => 'type',
);
 protected $aTypes = array (
  'id_irpg_user' => '%u',
  'type' => '%u',
  'level' => '%u',
  'name' => '%s',
  'options' => '%u',
);
}
?>
