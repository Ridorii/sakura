<?php
// Flashii Configuration
$sakuraConf = array(); // Define configuration array

// PDO Database Connection
$sakuraConf['db']               = array();
$sakuraConf['db']['driver']     = 'mysql';
$sakuraConf['db']['unixsocket'] = false;
$sakuraConf['db']['host']       = 'localhost';
$sakuraConf['db']['port']       = 3306;
$sakuraConf['db']['username']   = 'flashii';
$sakuraConf['db']['password']   = 'password';
$sakuraConf['db']['database']   = 'flashii';
$sakuraConf['db']['prefix']     = 'fii_';

// URLs (for modularity)
$sakuraConf['urls']['main']     = 'flashii.net';
$sakuraConf['urls']['api']      = 'api.flashii.net';
$sakuraConf['urls']['content']  = 'cdn.flashii.net';
$sakuraConf['urls']['chat']     = 'chat.flashii.net';
$sakuraConf['urls']['manage']   = 'manage.flashii.net';

// Errata
$sakuraConf['etc']['localPath']     = '/var/www/flashii.net/';
$sakuraConf['etc']['templatesPath'] = $sakuraConf['etc']['localPath'] .'_sakura/templates/';
$sakuraConf['etc']['design']        = 'yuuno';
