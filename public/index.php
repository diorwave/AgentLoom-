<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

if (file_exists($main = __DIR__.'/../vendor/autoload.php')) {
    require $main;
}

$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle($request = Request::capture())->send();
$kernel->terminate($request, $response);
