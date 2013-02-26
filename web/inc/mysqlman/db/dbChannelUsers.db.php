<?php
class dbChannelUsers extends dbControl {
 const DRIVER = 'mysql';
 protected $sTable = 'channel_users';
 protected $sInstance = 'phpirpg';
 protected $sRef = '';
 protected $aFields = array (
  'nick' => '',
  'channel' => '',
  'id_irpg_user' => '',
  'date_autologin' => '',
  'user' => '',
  'host' => '',
);
 protected $aPrimary = array (
  0 => 'nick',
  1 => 'channel',
);
 protected $aTypes = array (
  'nick' => '%s',
  'channel' => '%s',
  'id_irpg_user' => '%u',
  'date_autologin' => '%s',
  'user' => '%s',
  'host' => '%s',
);
}
?>
