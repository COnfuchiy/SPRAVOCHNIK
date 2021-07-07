<?php

use Phalcon\Loader;
use Phalcon\Mvc\Micro;
use Phalcon\Di\FactoryDefault;
use Phalcon\Db\Adapter\Pdo\Postgresql as Postgresql;


define('BASE_PATH', dirname(__DIR__));
define('API_VERSION', "1.1");
define('API_PATH', BASE_PATH."/".API_VERSION);
define('API_HOST_PATH', "/api/".API_VERSION);

require_once API_PATH."/modules/ApiHandler.php";
require_once API_PATH."/modules/helpers/ApiRoutesHelper.php";

$loader = new Loader();

$loader->registerDirs(
    [
        API_PATH.'/models/',
    ]
);

$loader->register();

$container = new FactoryDefault();
$container->set(
    'db',
    function () {
        return new Postgresql(
            [
                'host'     => '',
                'username' => '',
                'password' => '',
                'dbname'   => '',
            ]
        );
    }
);

$app = new Micro($container);

// POST authorization
$app->post(
    ApiRoutesHelper::AUTHORIZATION,
    function () use ($app){
        return ApiHandler::authorization();
    }
);

// POST registration
$app->post(
    ApiRoutesHelper::REGISTRATION,
    function () use ($app){
        return ApiHandler::registration();
    }
);

// GET user by id (0 refer by to the current user)
$app->get(
    ApiRoutesHelper::USER_BY_ID,
    function ($id) use ($app){
        return ApiHandler::getUserById($id);
    }
);

// GET all users
$app->get(
    ApiRoutesHelper::USERS_BASE_TEMPLATE,
    function () use ($app){
        return ApiHandler::getAllUsers();
    }
);

// GET all users with pagination
$app->get(
    ApiRoutesHelper::ALL_USERS_WITH_PAGINATION,
    function ($pageSize, $pageNum) use ($app){
        return ApiHandler::getAllUsers($pageSize, $pageNum);
    }
);

// POST not allowed
$app->post(
    ApiRoutesHelper::USER_BY_ID,
    function ($id) use ($app){
        ApiHelper::createErrorResponse(ApiHelper::METHOD_NOT_ALLOWED,[
           "Use ".ApiRoutesHelper::REGISTRATION
        ]);
    }
);

// PATCH update current user data (password)
$app->patch(
    ApiRoutesHelper::USER_BY_ID,
    function ($id) use ($app){
        return ApiHandler::updateUser($id);
    }
);

// GET all current user nodes
$app->get(
    ApiRoutesHelper::NODES_BASE_TEMPLATE,
    function () use ($app){
        return ApiHandler::getAllUserNodes();
    }
);

// GET all current user nodes with pagination
$app->get(
    ApiRoutesHelper::ALL_USER_NODES_WITH_PAGINATION,
    function ($pageSize, $pageNum) use ($app){
        return ApiHandler::getAllUserNodes($pageSize, $pageNum);
    }
);

// GET node (if node belong current user or public)
$app->get(
    ApiRoutesHelper::NODE_BY_ID,
    function ($id) use ($app){
        return ApiHandler::getNode($id);
    }
);

// POST create new node
$app->post(
    ApiRoutesHelper::NODES_BASE_TEMPLATE,
    function () use ($app){
        return ApiHandler::createNode();
    }
);

// PATCH update node (if node belong current user)
$app->patch(
    ApiRoutesHelper::NODE_BY_ID,
    function ($id) use ($app){
        return ApiHandler::updateNode($id);
    }
);

// DELETE update node (if node belong current user)
$app->delete(
    ApiRoutesHelper::NODE_BY_ID,
    function ($id) use ($app){
        return ApiHandler::deleteNode($id);
    }
);

// GET all public nodes
$app->get(
    ApiRoutesHelper::NODES_PUBLIC,
    function () use ($app){
        return ApiHandler::getAllPublicNodes();
    }
);
// GET all public nodes with pagination
$app->get(
    ApiRoutesHelper::NODES_PUBLIC_WITH_PAGINATION,
    function ($pageSize, $pageNum) use ($app){
        return ApiHandler::getAllPublicNodes($pageSize, $pageNum);
    }
);

// GET all node addresses (if node belong current user or public)
$app->get(
    ApiRoutesHelper::ALL_NODE_ADDRESSES,
    function ($nodeId) use ($app){
        return ApiHandler::getAllNodeAddresses($nodeId);
    }
);

// GET all node addresses with pagination (if node belong current user or public)
$app->get(
    ApiRoutesHelper::ALL_NODE_ADDRESSES_WITH_PAGINATION,
    function ($nodeId, $pageSize, $pageNum) use ($app){
        return ApiHandler::getAllNodeAddresses($nodeId,$pageSize, $pageNum);
    }
);

// GET address (if node belong current user or public)
$app->get(
    ApiRoutesHelper::ADDRESS_BY_ID,
    function ($id) use ($app){
        return ApiHandler::getAddress($id);
    }
);

// POST create new address
$app->post(
    ApiRoutesHelper::ADDRESSES_CREATE,
    function ($nodeId) use ($app){
        return ApiHandler::createAddress($nodeId);
    }
);

// PATCH update address (if node belong current user)
$app->patch(
    ApiRoutesHelper::ADDRESS_BY_ID,
    function ($id) use ($app){
        return ApiHandler::updateAddress($id);
    }
);

// DELETE address (if node belong current user)
$app->delete(
    ApiRoutesHelper::ADDRESS_BY_ID,
    function ($id) use ($app){
        return ApiHandler::deleteAddress($id);
    }
);


$app->handle(
    $_SERVER["REQUEST_URI"]
);