<?php
if (!isset($_GET['q'])) { header("Location: ../../index.php?q=faq"); }
?>
<div class="title">&#8250; What is idle-RPG ?</div>
<div class="contents">Idle RPG is the name of a role-playing game for IRC (Internet Relay Chat). The goal is to do nothing as long as possible on the IRC channel to avoid any action that could cost you penalty points, such as talking, change nickname or leaving / quit the channel.<br /><br />
More you spend time on the channel, more you can gain levels and thus maybe acquire rare items. You can also win time between levels by fighting other players online, and finishing quests! Everything is done automatically by the intervention of a robot.</div><br />
<div class="title">&#8250; How to play?</div>
<div class="contents">First, you must get an IRC client. For Linux users, you have <a href="http://xchat.org/" onclick="target='_blank';">Xchat</a> and for Windows users, <a href="http://www.mirc.com/" onclick="target='_blank';">mIRC</a>. Then, you must connect to the server <strong>irc.quakenet.org</strong> and join channel <strong>#idle-rpg</strong>. However, you can connect via <a href="http://webchat.quakenet.org/" onclick="target='_blank';">java applet from Quakenet.org official website</a>.<br /><br />
Once done, you must create an account with this single command:
<blockquote class="bold"><p>/msg idle-rpg register &#8249;nickname&#8250; &#8249;password&#8250; &#8249;e-mail&#8250; &#8249;short description of your player&#8250;</p></blockquote>
And the game begins! After each disconnection, you must re-login into the bot, with this command:
<blockquote class="bold"><p>/msg idle-rpg login &#8249;nickname&#8250; &#8249;password&#8250;</p></blockquote></div>
<div class="title">&#8250; The others commands list:</div>
<div class="contents"><blockquote><p>
<strong>/msg idle-rpg logout</strong> - Disconnect you from the bot.<br />
<strong>/msg idle-rpg whoami</strong> - Displays your account information, level, time to next level, time idled.</p></blockquote></div>
<div class="title">&#8250; Penalties</div>
<div class="contents">As I already said, the only rule of the game is to do nothing, then any action on your part will trigger a penalty. Penalties are expressed in time, in seconds, added to your next time to level, by the following formulae.
<blockquote><p><strong>- Being kicked:</strong> 200 * (1.16 ^ your_level)<br />
<strong>- Change nickname:</strong> 50 * (1.16 ^ your_level)<br />
<strong>- Logout (from the bot):</strong> 40 * (1.16 ^ your_level)<br />
<strong>- Part:</strong> (100 + message_lenght) * (1.16 ^ your_level)<br />
<strong>- Quit:</strong> 50 * (1.16 ^ your_level)<br />
<strong>- Channel message:</strong> (50 + message_lenght) * (1.16 ^ your_level)<br />
<strong>- Channel action:</strong> (50 + message_lenght) * (1.16 ^ your_level)<br />
<strong>- Channel ctcp:</strong> (100 + message_lenght) * (1.16 ^ your_level)<br />
<strong>- Channel notice:</strong> (100 + message_lenght) * (1.16 ^ your_level)</p></blockquote>
For example, you are level 42, and you say "Hi, i love catch fireflies." on the channel. This phrase contains 27 characters including spaces and punctuation. So, (50 + 27) * (1.16 ^ 42) = <?php echo Utils::number(77*(pow(1.16, 42))); ?> seconds -> approximately <?php echo Utils::number(77*(pow(1.16, 42))/60/60); ?> hours, added to your clock.</div><br />
<div class="title">&#8250; Map</div>
<div class="contents">En dev.</div><br />
<div class="title">&#8250; Events</div>
<div class="contents">En dev.</div><br />
<div class="title">&#8250; Rare Items</div>
<div class="contents">En dev.</div><br />
<div class="title">&#8250; Have a question? a problem?</div>
<div class="contents">Please contact an admin on IRC private message, or by e-mail. 
<blockquote class="bold"><p>win: <a href="mailto:&#119;&#105;&#110;&#064;&#119;&#097;&#114;&#114;&#105;&#111;&#114;&#104;&#111;&#117;&#115;&#101;&#046;&#110;&#101;&#116;">&#119;&#105;&#110;&#064;&#119;&#097;&#114;&#114;&#105;&#111;&#114;&#104;&#111;&#117;&#115;&#101;&#046;&#110;&#101;&#116;</a> / Shiwang: <a href="mailto:&#115;&#104;&#105;&#119;&#097;&#110;&#103;&#064;&#111;&#114;&#097;&#110;&#103;&#101;&#046;&#102;&#114;">&#115;&#104;&#105;&#119;&#097;&#110;&#103;&#064;&#111;&#114;&#097;&#110;&#103;&#101;&#046;&#102;&#114;</a></p></blockquote></div>