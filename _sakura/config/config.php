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
$sakuraConf['db']['password']   = 'Ky2YQMr4vu4zcZE&yLZT!gQ&Wdf-CxrQLej+^PS6jS5AgAQh52yf6Br&mq-C8J=F3Yw$3wnMU7?ebA9r+Abe4J_kzzs57C8U22&#wytuf-veF9WEuHfP-GRHQ^?5pXbx';
$sakuraConf['db']['database']   = 'flashii';
$sakuraConf['db']['prefix']     = 'fii_';

// URLs (for modularity)
$sakuraConf['urls']['main']     = 'iihsalf.net';
$sakuraConf['urls']['api']      = 'api.iihsalf.net';
$sakuraConf['urls']['content']  = 'cdn.iihsalf.net';
$sakuraConf['urls']['chat']     = 'chat.iihsalf.net';
$sakuraConf['urls']['manage']   = 'manage.iihsalf.net';

// Errata
$sakuraConf['etc']['localPath']     = '/var/www/flashii.net/';
$sakuraConf['etc']['templatesPath'] = $sakuraConf['etc']['localPath'] .'_sakura/templates/';
$sakuraConf['etc']['design']        = 'yuuno';
