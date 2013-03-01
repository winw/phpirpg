<?php
if (!isset($_GET['q'])) { header("Location: ../../index.php?q=news"); }
if (!isset($_GET['p'])) { $_GET['p'] = 1; }
$iPage = (int)$_GET['p'];
$iNb = 5;
$oInstance = dbInstance::get('phpirpg');
$oIrpgNews = new dbIrpgNews();
$aoIrpgNews = $oIrpgNews->select('SQL_CALC_FOUND_ROWS irpg_news.*')->order('id DESC')->limit(($iPage-1) * $iNb.','.$iNb)->fetchAll();
$iIrpgNews = (int)$oInstance->query('SELECT FOUND_ROWS() AS nb')->fetch(PDO::FETCH_OBJ)->nb;
$aPagination = utils::getPages($iPage, ceil($iIrpgNews / $iNb), 5);
if ($iPage > ceil($iIrpgNews / $iNb)) { include_once('404.php'); }
foreach ($aoIrpgNews as $oIrpgNew)  { 
?><div class="title">&#8250; <?php echo $oIrpgNew->title; ?><span class="date"><?php $date = date_create($oIrpgNew->date_creation); echo date_format($date, 'j F Y'); ?></span></div>
<div class="contents"><?php echo $oIrpgNew->contents; ?></div><br />
<?php }
echo "<div class='pagination'><strong>&#8250; P</strong>age".((ceil($iIrpgNews / $iNb) > 1) ? 's' : '').": ";
foreach ($aPagination as $iPagination) {
if ($iPagination == $iPage) echo '<strong>'.$iPage.'</strong> ';
else echo '<a href="?p='.$iPagination.'">'.$iPagination.'</a> ';
}
?><strong>&#8249;</strong></div>