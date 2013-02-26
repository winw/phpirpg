<?php $aoDatas = $oPdo->query('SELECT * FROM `irpg_news` ORDER BY id DESC')->fetchAll(PDO::FETCH_OBJ); foreach ($aoDatas as $oData) { ?>
<div class="title">&#8250; <?php echo $oData->title; ?><span class="menuright"><?php echo date('j F Y',$oData->datetime); ?></span></div>
<div class="contents"><?php echo $oData->contents; ?></div>
<br /><?php } ?>
