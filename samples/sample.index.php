<?php

namespace Roto;

error_reporting(E_ALL);
date_default_timezone_set('Europe/London');

$root = dirname(__FILE__);

include_once($root.'/vendor/autoload.php');

require_once($root .'/lib/general.php');
require_once($root.'/lib/services.php');

Widget::$root = $root.'/layout/widgets/';

Service::Router()->route($_SERVER['REQUEST_URI']);