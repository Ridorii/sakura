<?php
// Flashii Configuration
$sakuraConf = array(); // Define configuration array

// PDO Database Connection
$sakuraConf['db']               = array();
$sakuraConf['db']['driver']     = 'mysql'; // SQL Driver contained in the components/database folder
$sakuraConf['db']['unixsocket'] = false; // Use internal UNIX system sockets (would not work on Windows)
$sakuraConf['db']['host']       = 'localhost'; // SQL Hosts (or path to socket in the case that you're using them)
$sakuraConf['db']['port']       = 3306; // SQL Port (does nothing when UNIX sockets are used)
$sakuraConf['db']['username']   = 'flashii'; // Database authentication username
$sakuraConf['db']['password']   = 'password'; // Database authentication password
$sakuraConf['db']['database']   = 'flashii'; // Database name
$sakuraConf['db']['prefix']     = 'fii_'; // Table Prefix

// URLs (for modularity)
$sakuraConf['urls']['main']     = 'flashii.net'; // Main site url
$sakuraConf['urls']['api']      = 'api.flashii.net'; // API url
$sakuraConf['urls']['content']  = 'cdn.flashii.net'; // Content directory url
$sakuraConf['urls']['chat']     = 'chat.flashii.net'; // Chat url
$sakuraConf['urls']['manage']   = 'manage.flashii.net'; // Moderator panel url

// Errata
$sakuraConf['etc']['localPath']     = '/var/www/flashii.net/'; // Local path
$sakuraConf['etc']['templatesPath'] = $sakuraConf['etc']['localPath'] .'_sakura/templates/'; // Path to templates directory
$sakuraConf['etc']['design']        = 'yuuno'; // Style name
$sakuraConf['etc']['cfhosts']       = $sakuraConf['etc']['localPath'] .'_sakura/config/cloudflare.hosts'; // Cloudflare IP subnets file
