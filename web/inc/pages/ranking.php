<?php
if (!isset($_GET['q'])) { header("Location: ../../index.php?q=ranking"); }
?>
<div class="table"><div class="tbody">
<div class="tr_menu">
    <div class="id">#</div>
    <div class="player">Idler</div>
    <div class="lvl">Level</div>
    <div class="time">Time to level</div>
    <div class="item">Itemsum</div>
</div><?php $aoDatas = $oPdo->query('SELECT * FROM `irpg_users` ORDER BY level DESC, time_to_level ASC')->fetchAll(PDO::FETCH_OBJ); foreach ($aoDatas as $oData) { ?><div class="tr">
    <div class="id"><span class="num"></span></div>
    <div class="player"><?php echo utils::hte($oData->login); ?></div>
    <div class="lvl"><?php echo $oData->level; ?></div>
    <div class="time"><?php echo Utils::duration($oData->time_to_level); ?></div>
    <div class="item"><?php echo $oData->options; ?></div>
</div><?php } ?>
</div></div>