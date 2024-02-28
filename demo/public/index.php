<?php declare(strict_types=1);

use Kreme\FileBaseRouter\Dispatcher;

define('APP_PATH', dirname(__DIR__));
define('COMPONENT_PATH', APP_PATH . '/components');

require_once dirname(APP_PATH) . '/vendor/autoload.php';

$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$dispatcher = new Dispatcher(dirname(__DIR__) . '/pages');
$template = $dispatcher->dispatch($request_uri);

if (false !== $template && file_exists($template)) {
    include $template;
} else {
    http_response_code(404);
}
