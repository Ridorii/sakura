<?php
// Sakura Configuration
$sakuraConf = array(); // Define configuration array

// PDO Database Connection
$sakuraConf['db']               = array();
$sakuraConf['db']['driver']     = 'mysql'; // SQL Driver contained in the components/database folder
$sakuraConf['db']['unixsocket'] = false; // Use internal UNIX system sockets (would not work on Windows)
$sakuraConf['db']['host']       = 'localhost'; // SQL Hosts (or path to socket in the case that you're using them)
$sakuraConf['db']['port']       = 3306; // SQL Port (does nothing when UNIX sockets are used)
$sakuraConf['db']['username']   = 'sakura'; // Database authentication username
$sakuraConf['db']['password']   = 'password'; // Database authentication password
$sakuraConf['db']['database']   = 'sakura'; // Database name
$sakuraConf['db']['prefix']     = 'sakura_'; // Table Prefix

// URLs (for modularity)
$sakuraConf['urls']             = array();
$sakuraConf['urls']['main']     = 'flashii.net'; // Main site url
$sakuraConf['urls']['api']      = 'api.flashii.net'; // API url
$sakuraConf['urls']['content']  = 'cdn.flashii.net'; // Content directory url
$sakuraConf['urls']['chat']     = 'chat.flashii.net'; // Chat url
$sakuraConf['urls']['manage']   = 'manage.flashii.net'; // Moderator panel url
$sakuraConf['urls']['forum']    = 'forum.flashii.net'; // Forum url

// Errata
$sakuraConf['etc']                  = array();
$sakuraConf['etc']['cfipv4']        = ROOT .'_sakura/config/cloudflare.ipv4'; // Cloudflare IPv4 subnets file
$sakuraConf['etc']['cfipv6']        = ROOT .'_sakura/config/cloudflare.ipv6'; // Cloudflare IPv6 subnets file
$sakuraConf['etc']['whoisservers']  = ROOT .'_sakura/config/whois.json'; // JSON with Whois servers
$sakuraConf['etc']['iso3166']       = ROOT .'_sakura/config/iso3166.json'; // JSON with country codes

// Sock Chat extensions
$sakuraConf['sock']             = array();
$sakuraConf['sock']['enable']   = true; // Ability to disable the extension in case you're using Sakura without Sock Chat, mind that this extension only works when using the same database
$sakuraConf['sock']['sqlpref']  = 'sock_'; // Sock Chat table prefixes
