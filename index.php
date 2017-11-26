<?php
// Including composer autoloader
#require __DIR__ . '/vendor/autoload.php';
// Then registering autoloader for Requests library
require_once 'lib/Requests.php';
Requests::register_autoloader();
// This is required for including configs and functions. Sorry, bad code :D
define("_KATE_MAIN", true);
require_once(__DIR__ . '/config/config.php');
require_once(__DIR__ . '/include/function.php');
// We don't want errors to block our app from displaying info
error_reporting(1);

$requestParams = request_compose();
$url = $requestParams['httpScheme'] . '://' . $requestParams['httpHost'] . $requestParams['requestPath'];
switch ($requestParams['requestPath']) {
    case '/':
        header('Location: /admin.php');
        break;

    case '/openapi-gateway-app/live/radios':
        require(__DIR__ . '/include/api/radiosearch.php');
        break;

    case '/openapi-gateway-app/search/radios':
        require(__DIR__ . '/include/api/radiosearch.php');
        break;

    case '/openapi-gateway-app/live/get_radios_by_ids':
        require(__DIR__ . '/include/api/get_radios_by_ids.php');
        break;

    default:
        http_response_code(404);
}
