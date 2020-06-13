<?php

use Dotenv\Dotenv;
use App\Router;
use App\Http\Responses\OkResponse;
use App\Http\Responses\ErrorResponse;

$dotenv = Dotenv::createImmutable('./');
$dotenv->load();

try {
    $response = Router::init();
    echo new OkResponse($response);
} catch (Exception $e) {
    echo new ErrorResponse($e->getCode(), $e->getMessage());
}
