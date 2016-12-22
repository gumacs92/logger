<?php
/**
 * Created by PhpStorm.
 * User: Gumacs
 * Date: 2016-12-08
 * Time: 10:29 PM
 */

use Framework\Components\Autoloader\AutoLoader;

$autoloader = AutoLoader::getAutoloader();

define('SRC', getcwd() . DS . 'src' . DS);

$autoloader->addDirectory(SRC, 'Loggers');
$autoloader->init();
$autoloader->dispatch();
