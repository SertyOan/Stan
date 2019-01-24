<?php
chdir(dirname(__FILE__).'/../../../..');
require('etc/Configuration.php');
require('code/server/php/ThirdParty/autoload.php');

session_set_cookie_params(900, '/', '');

$path = $_SERVER['REQUEST_URI'];

switch($path) {
    case '/service/':
        if($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit();
        }

        \App\Handlers\Service::load();
        break;
    default:
        http_response_code(404);
        exit();
}
