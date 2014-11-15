<?php
 class Configuration {
  const SERVER_IP = '83.140.172.210';
  const SERVER_PORT = 6667;
  
  const BOT_NICK = 'phpirpgbot';
  const BOT_USER = 'phpirpgbot';
  const BOT_DESCRIPTION = 'phpirpg';
  const BOT_CHANNEL = '#idle-rpg';
  
  const BOT_MODULES = 'ModChannelUsers ModCore ModLevel ModItem ModMap ModBattle ModCalamities ModDebug';
  
  const DB_DSN = 'mysql:dbname=phpirpg;host=localhost;charset=utf8;socket=/var/run/mysqld/mysqld.sock';
  const DB_LOGIN = 'root';
  const DB_PASSWORD = 'toor';

  const BASE_MULTIPLICATOR = M_LNPI;
 }