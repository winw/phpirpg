<?php
class dbIrpgNews extends dbControl {
 const DRIVER = 'mysql';
 protected $sTable = 'irpg_news';
 protected $sInstance = 'phpirpg';
 protected $sRef = 'id';
 protected $aFields = array (
  'id' => '',
  'title' => '',
  'contents' => '',
  'datetime' => '',
);
 protected $aPrimary = array (
  0 => 'datetime',
);
 protected $aTypes = array (
  'id' => '%d',
  'title' => '%s',
  'contents' => '%s',
  'datetime' => '%d',
);
}
?>
