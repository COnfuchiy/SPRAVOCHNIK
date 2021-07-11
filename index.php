<?php

use Phalcon\Loader;
use Phalcon\Mvc\Micro;
use Phalcon\Di\FactoryDefault;
use Phalcon\Db\Adapter\Pdo\Postgresql as Postgresql;

define('BASE_PATH', dirname(__DIR__));
define('API_VERSION', "1.1");
define('API_PATH', BASE_PATH . "/" . API_VERSION);
define('API_HOST_PATH', "/api/" . API_VERSION);

require_once API_PATH . "/modules/ApiHandler.php";
require_once API_PATH . "/modules/helpers/ApiRoutesHelper.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/predis/autoload.php";


$loader = new Loader();

$loader->registerDirs(
    [
        API_PATH . '/models/',
    ]
);

$loader->register();

$container = new FactoryDefault();
$container->set(
    'db',
    function () {
        return new Postgresql(
            [
                'host' => 'localhost',
                'username' => 'postgres',
                'password' => 'postgres',
                'dbname' => 'postgres',
//                'charset' => 'utf8',
            ]
        );
    }
);

$container->set(
    'redis',
    function () {
        return new Predis\Client();
    }
);

$app = new Micro($container);


// POST authorization
$app->post(
    ApiRoutesHelper::AUTHORIZATION,
    'LoginComponent::authorization'
);

// POST registration
$app->post(
    ApiRoutesHelper::REGISTRATION,
    'LoginComponent::registration'
);

// GET user by id (0 refer by to the current user)
$app->get(
    ApiRoutesHelper::USER_BY_ID,
    'ApiHandler::getUserById'
);

// GET all users
$app->get(
    ApiRoutesHelper::USERS_BASE_TEMPLATE,
    'getAllUsers'
);

// POST not allowed
$app->post(
    ApiRoutesHelper::USER_BY_ID,
    function () use ($app) {
        ApiHelper::createErrorResponse(
            ApiHelper::METHOD_NOT_ALLOWED,
            [
                "Use " . ApiRoutesHelper::REGISTRATION
            ]
        );
    }
);

// PATCH update current user data (password)
$app->patch(
    ApiRoutesHelper::USER_BY_ID,
    'ApiHandler::updateUser'
);

// GET all current user nodes
$app->get(
    ApiRoutesHelper::NODES_BASE_TEMPLATE,
    'ApiHandler::getAllUserNodes'
);


// GET node (if node belong current user or public)
$app->get(
    ApiRoutesHelper::NODE_BY_ID,
    'ApiHandler::getNode'
);

// POST create new node
$app->post(
    ApiRoutesHelper::NODES_BASE_TEMPLATE,
    'ApiHandler::createNode'
);

// PATCH update node (if node belong current user)
$app->patch(
    ApiRoutesHelper::NODE_BY_ID,
    'ApiHandler::updateNode'
);

// DELETE update node (if node belong current user)
$app->delete(
    ApiRoutesHelper::NODE_BY_ID,
    'ApiHandler::deleteNode'
);

// GET all public nodes
$app->get(
    ApiRoutesHelper::NODES_PUBLIC,
    'ApiHandler::getAllPublicNodes'
);

// GET all node addresses (if node belong current user or public)
$app->get(
    ApiRoutesHelper::ALL_NODE_ADDRESSES,
    'ApiHandler::getAllNodeAddresses'
);

// GET address (if node belong current user or public)
$app->get(
    ApiRoutesHelper::ADDRESS_BY_ID,
    'ApiHandler::getAddress'
);

// POST create new address
$app->post(
    ApiRoutesHelper::ADDRESSES_CREATE,
    'ApiHandler::createAddress'
);

// PATCH update address (if node belong current user)
$app->patch(
    ApiRoutesHelper::ADDRESS_BY_ID,
    'ApiHandler::updateAddress'
);

// DELETE address (if node belong current user)
$app->delete(
    ApiRoutesHelper::ADDRESS_BY_ID,
    'ApiHandler::deleteAddress'
);

// NOT FOUND handler
$app->notFound(
    function () use ($app) {
        return ApiHelper::createErrorResponse(
            ApiHelper::NOT_FOUND,
            ["No handler found for this request. See https://github.com/COnfuchiy/SPRAVOCHNIK"]
        );
    }
);

// Unhandled error
$app->error(
    function () use ($app) {
        return ApiHelper::createErrorResponse(ApiHelper::INTERNAL_SERVER_ERROR, ["Server error"]);
    }
);

LoginComponent::$app = $app;

$app->handle(
    $_SERVER["REQUEST_URI"]
);