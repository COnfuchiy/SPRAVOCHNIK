<?php


class ApiRoutesHelper
{

    public const AUTHORIZATION = API_HOST_PATH . "/auth";
    public const REGISTRATION = API_HOST_PATH . "/registry";

    public const USERS_BASE_TEMPLATE = API_HOST_PATH . "/users";
    public const USER_BY_ID = self::USERS_BASE_TEMPLATE . "/{Id:[0-9]+}";

    public const NODES_BASE_TEMPLATE = API_HOST_PATH . "/nodes";
    public const NODE_BY_ID = self::NODES_BASE_TEMPLATE . "/{Id:[0-9]+}";
    public const NODES_PUBLIC = self::NODES_BASE_TEMPLATE . "/public";
    public const ALL_NODE_ADDRESSES = self::NODES_BASE_TEMPLATE . "/{nodeId:[0-9]+}/addresses";

    public const ADDRESSES_BASE_TEMPLATE = API_HOST_PATH . "/addresses";
    public const ADDRESSES_CREATE = self::ADDRESSES_BASE_TEMPLATE . "/nodes/{nodeId:[0-9]+}";
    public const ADDRESS_BY_ID = self::ADDRESSES_BASE_TEMPLATE . "/{Id:[0-9]+}";

    public static function getUserUrl($userId)
    {
        return ((!empty($_SERVER['HTTPS'])) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . self::USERS_BASE_TEMPLATE . "/" . $userId;
    }

    public static function getNodeUrl($nodeId)
    {
        return ((!empty($_SERVER['HTTPS'])) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . self::NODES_BASE_TEMPLATE . "/" . $nodeId;
    }

    public static function getAddressUrl($addressId)
    {
        return ((!empty($_SERVER['HTTPS'])) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . self::ADDRESSES_BASE_TEMPLATE . "/" . $addressId;
    }
}