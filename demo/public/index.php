<?php declare(strict_types=1);

use FileBaseRouter\Dispatcher;

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$dispatcher = new Dispatcher(dirname(__DIR__) . '/pages');
$template = $dispatcher->dispatch($request_uri);

if (false !== $template && file_exists($template)) {
    include $template;
} else {
    http_response_code(404);
}
