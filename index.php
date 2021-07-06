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
                'host'     => 'localhost',
                'username' => 'postgres',
                'password' => 'postgres',
                'dbname'   => 'postgres',
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
        return ApiHandler::getUserDataById($id);
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

// PUT update current user data (password)
$app->put(
    ApiRoutesHelper::USER_BY_ID,
    function ($id) use ($app){
        return ApiHandler::updateUserById($id);
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
        return ApiHandler::getNodeDataById($id);
    }
);

// POST create new node
$app->post(
    ApiRoutesHelper::NODES_BASE_TEMPLATE,
    function ($id) use ($app){
        return ApiHandler::updateUserById($id);
    }
);

// PUT update node (if node belong current user)
$app->put(
    ApiRoutesHelper::NODE_BY_ID,
    function ($id) use ($app){
        return ApiHandler::updateUserById($id);
    }
);

// DELETE update node (if node belong current user)
$app->delete(
    ApiRoutesHelper::NODE_BY_ID,
    function ($id) use ($app){
        return ApiHandler::updateUserById($id);
    }
);

// GET all public nodes
$app->get(
    ApiRoutesHelper::NODES_PUBLIC,
    function ($id) use ($app){
        return ApiHandler::updateUserById($id);
    }
);
// GET all public nodes with pagination
$app->get(
    ApiRoutesHelper::NODES_PUBLIC_WITH_PAGINATION,
    function ($id) use ($app){
        return ApiHandler::updateUserById($id);
    }
);

// GET all node addresses
$app->get(
    ApiRoutesHelper::ADDRESSES_BASE_TEMPLATE,
    function ($id) use ($app){
        return ApiHandler::updateUserById($id);
    }
);

// GET all node addresses with pagination
$app->get(
    ApiRoutesHelper::ALL_NODE_ADDRESSES_WITH_PAGINATION,
    function ($id) use ($app){
        return ApiHandler::updateUserById($id);
    }
);

// GET address (if node belong current user )
$app->get(
    ApiRoutesHelper::ADDRESS_BY_ID,
    function ($id) use ($app){
        return ApiHandler::updateUserById($id);
    }
);

// POST address new node
$app->post(
    ApiRoutesHelper::ADDRESS_BY_ID,
    function ($id) use ($app){
        return ApiHandler::updateUserById($id);
    }
);

// PUT address node (if node belong current user)
$app->put(
    ApiRoutesHelper::ADDRESS_BY_ID,
    function ($id) use ($app){
        return ApiHandler::updateUserById($id);
    }
);

// DELETE address node (if node belong current user)
$app->delete(
    ApiRoutesHelper::ADDRESS_BY_ID,
    function ($id) use ($app){
        return ApiHandler::updateUserById($id);
    }
);


$app->handle(
    $_SERVER["REQUEST_URI"]
);