<?php
class dbIrpgUsers extends dbControl {
 const DRIVER = 'mysql';
 protected $sTable = 'irpg_users';
 protected $sInstance = 'phpirpg';
 protected $sRef = 'id';
 protected $aFields = array (
  'id' => '',
  'date_created' => '',
  'date_login' => '',
  'date_no_battle' => '',
  'login' => '',
  'password' => '',
  'email' => '',
  'x' => '',
  'y' => '',
  'options' => '',
  'time_to_level' => '',
  'level' => '',
  'time_idled' => '',
);
 protected $aPrimary = array (
  0 => 'id',
);
 protected $aTypes = array (
  'id' => '%u',
  'date_created' => '%s',
  'date_login' => '%s',
  'date_no_battle' => '%s',
  'login' => '%s',
  'password' => '%s',
  'email' => '%s',
  'x' => '%u',
  'y' => '%u',
  'options' => '%u',
  'time_to_level' => '%u',
  'level' => '%u',
  'time_idled' => '%u',
);
}
?>
