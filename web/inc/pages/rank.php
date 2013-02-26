<div class="table">
	<div class="tbody">
        <div class="tr_menu">
        <div class="id"><strong>#</strong></div>
        <div class="player"><strong>Player</strong></div>
        <div class="lvl"><strong>Level</strong></div>
        <div class="time"><strong>Time to level</strong></div>
        <div class="item"><strong>Itemsum</strong></div>
        </div>
		<?php $aoDatas = $oPdo->query('SELECT * FROM `irpg_users` ORDER BY level DESC, time_to_level ASC')->fetchAll(PDO::FETCH_OBJ); foreach ($aoDatas as $oData) { ?><div class="tr">
        <div class="id"><span class="num"></span></div>
        <div class="player"><?php echo $oData->login; ?></div>
        <div class="lvl"><?php echo $oData->level; ?></div>
        <div class="time"><?php echo Utils::duration($oData->time_to_level); ?></div>
        <div class="item"><?php echo $oData->options; ?></div>
		</div><?php } ?>
	</div>
</div>
