<?php


class ApiRoutesHelper
{

    const AUTHORIZATION = API_HOST_PATH."/auth";
    const REGISTRATION = API_HOST_PATH."/registry";

    const USERS_BASE_TEMPLATE = API_HOST_PATH."/users";
    const ALL_USERS_WITH_PAGINATION = self::USERS_BASE_TEMPLATE."?page[size]={pageSize:[0-9]+}&page[num]={pageNum:[0-9]+}";
    const USER_BY_ID = self::USERS_BASE_TEMPLATE."/{id:[0-9]+}";

    const NODES_BASE_TEMPLATE = API_HOST_PATH."/nodes";
    const ALL_USER_NODES_WITH_PAGINATION = self::NODES_BASE_TEMPLATE."?page[size]={pageSize:[0-9]+}&page[num]={pageNum:[0-9]+}";
    const NODE_BY_ID = self::NODES_BASE_TEMPLATE."/{id:[0-9]+}";
    const NODES_PUBLIC = self::NODES_BASE_TEMPLATE."/public";
    const NODES_PUBLIC_WITH_PAGINATION = self::NODES_BASE_TEMPLATE."/public?page[size]={pageSize:[0-9]+}&page[num]={pageNum:[0-9]+}";

    const ADDRESSES_BASE_TEMPLATE = API_HOST_PATH."/addresses";
    const ALL_NODE_ADDRESSES_WITH_PAGINATION = self::ADDRESSES_BASE_TEMPLATE."?page[size]={pageSize:[0-9]+}&page[num]={pageNum:[0-9]+}";
    const ADDRESS_BY_ID = self::ADDRESSES_BASE_TEMPLATE."/{id:[0-9]+}";

    public static function getUserUrl($userId){
        return ((!empty($_SERVER['HTTPS'])) ? 'https' : 'http') .'://' .$_SERVER['HTTP_HOST'].self::USERS_BASE_TEMPLATE."/".$userId;
    }
    public static function getNodeUrl($nodeId){
        return ((!empty($_SERVER['HTTPS'])) ? 'https' : 'http') .'://' .$_SERVER['HTTP_HOST'].self::NODES_BASE_TEMPLATE."/".$nodeId;
    }
    public static function getAddressUrl($addressId){
        return ((!empty($_SERVER['HTTPS'])) ? 'https' : 'http') .'://' .$_SERVER['HTTP_HOST'].self::ADDRESSES_BASE_TEMPLATE."/".$addressId;
    }
}