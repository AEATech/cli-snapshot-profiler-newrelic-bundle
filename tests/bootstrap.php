<?php
declare(strict_types=1);

use Symfony\Component\ErrorHandler\ErrorHandler;

define('ROOT_PATH', realpath(__DIR__ . '/../'));

require_once ROOT_PATH . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

error_reporting(-1);

ErrorHandler::register(null, false);
