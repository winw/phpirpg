<?php
if (!isset($_GET['q'])) { header("Location: ../../index.php?q=stats"); }
$oChannelUsers = new dbChannelUsers();
$oIrpgUsers = new dbIrpgUsers();
?>
<div class="title">&#8250; Statistics of the game for the current year:</div>
<div class="contents"><blockquote><p>
- Idlers currently online: <strong><?php $iCo = $oChannelUsers->select('COUNT(1) as nb')->where('id_irpg_user IS NOT NULL')->fetch()->nb; { echo $iCo; } ?></strong><br />
- Idlers registered to the game: <strong><?php $iIr = $oIrpgUsers->select('COUNT(1) as nb')->fetch()->nb; { echo $iIr; } ?></strong><br />
- Last idler registered: <strong><?php $iLir = $oIrpgUsers->select('login as nb')->order('id DESC')->fetch()->nb; { echo utils::hte($iLir); } ?></strong><br />
- Highest level: <strong><?php $iHl = $oIrpgUsers->select('level as nb')->order('level DESC')->fetch()->nb; { echo $iHl; } ?> (<?php $iHln = $oIrpgUsers->select('login as nb')->order('level DESC')->order('time_to_level ASC')->fetch()->nb; { echo utils::hte($iHln); } ?>)</strong><br />
- Highest itemsum: <strong><?php $iHi = $oIrpgUsers->select('options as nb')->order('options DESC')->fetch()->nb; { echo $iHi; } ?> (<?php $iHin = $oIrpgUsers->select('login as nb')->order('options DESC')->fetch()->nb; { echo utils::hte($iHin); } ?>)</strong><br />
- Highest time idled: <strong><?php $iHti = $oIrpgUsers->select('time_idled as nb')->order('time_idled DESC')->fetch()->nb; { echo Utils::duration($iHti); } ?> (<?php $iHtin = $oIrpgUsers->select('login as nb')->order('time_idled DESC')->fetch()->nb; { echo utils::hte($iHtin); } ?>)</strong><br />
- Time until next restart game: <strong><?php function days_until($new_year) {
$c = strtotime($new_year." ".date("y"));
$t = time();
if ($c < $t) {$c = strtotime($new_year." ".date("y",strtotime("+1 year")));
}
	return ($c - $t);
}
echo Utils::duration(days_until("1 January")); ?></strong><br />
</p></blockquote></div>
<div class="title">&#8250; Statistics of the channel:</div>
<div class="contents"><blockquote><p>
- People actually on the channel: <strong><?php $oChannelUsers = new dbChannelUsers(); $iNb = $oChannelUsers->select('COUNT(1) as nb')->fetch()->nb; { echo $iNb; } ?></strong><br />
- Current Topic: <strong>New Idle RPG coming soon. (Progress : 60%)</strong><br />
- Number of message the bot has written since its launch: <strong>0</strong><br />
</p></blockquote></div>
<div class="title">&#8250; Statistics of the website:</div>
<div class="contents"><blockquote><p>
- Total pages viewed: <strong><?php require_once('inc/pages/count.txt'); ?></strong>
</p></blockquote></div>