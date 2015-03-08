<?php
// Flashii Configuration
$fiiConf = array(); // Define configuration array

// PDO Database Connection
$fiiConf['db']                      = array();
$fiiConf['db']['driver']            = 'mysql';
$fiiConf['db']['unixsocket']        = false;
$fiiConf['db']['host']              = 'localhost';
$fiiConf['db']['port']              = 3306;
$fiiConf['db']['username']          = 'flashii';
$fiiConf['db']['password']          = 'Ky2YQMr4vu4zcZE&yLZT!gQ&Wdf-CxrQLej+^PS6jS5AgAQh52yf6Br&mq-C8J=F3Yw$3wnMU7?ebA9r+Abe4J_kzzs57C8U22&#wytuf-veF9WEuHfP-GRHQ^?5pXbx';
$fiiConf['db']['database']          = 'flashii';
$fiiConf['db']['prefix']            = 'flashii_';

// URLs (for modularity)
$fiiConf['urls']['main']            = 'iihsalf.net';
$fiiConf['urls']['api']             = 'api.iihsalf.net';
$fiiConf['urls']['content']         = 'cdn.flashii.net';
$fiiConf['urls']['chat']            = 'chat.iihsalf.net';
$fiiConf['urls']['manage']          = 'manage.iihsalf.net';
$fiiConf['urls']['system']          = 'sys.iihsalf.net';

// Errata
$fiiConf['etc']['localPath']        = '/var/www/flashii.net/';
$fiiConf['etc']['templatesPath']    = $fiiConf['etc']['localPath'] .'_sakura/templates/';
$fiiConf['etc']['design']           = 'yuuno';
