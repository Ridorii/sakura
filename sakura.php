<?php
/*
 * Community Management System
 * (c) 2013-2016 Julian van de Groep <http://flash.moe>
 */

namespace Sakura;

require_once 'vendor/autoload.php';

ExceptionHandler::register();
Config::load(path('config/config.ini'));
DB::connect(config('database'));
