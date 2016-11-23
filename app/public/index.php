<?php

// Kickstart the framework

/**
 * @var \Base $f3
 */
$f3 = require('../../lib/base.php');

$f3->set('AUTOLOAD', __DIR__ . '/../;' . __DIR__ . '/../../lib/');
$f3->set('UI', __DIR__ . '/../view/');

// Load configuration
$f3->config('../config/config.ini');
$f3->config('../config/mysql.ini');
$f3->config('../config/routes.ini');
$f3->config('../config/paths.ini');

$f3->set('ONERROR', '\Controller\Root::error');

if ((float)PCRE_VERSION<7.9)
	trigger_error('PCRE version is out of date');

$f3->run();
