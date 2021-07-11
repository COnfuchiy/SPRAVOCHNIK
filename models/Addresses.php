<?php

use Phalcon\Mvc\Model;
use Phalcon\Http\Request;

/**
 * Class Addresses
 */
class Addresses extends Model
{
    public int $node_id;
    public int $address_id;
    public string $address_name;
    public string $address_country;
    public string $address_region;
    public string $address_city;
    public string $address_street;
    public string $address_house;
    public int $address_entrance;
    public int $address_apartment;
    public int $address_create_date;
    public int $address_update_date;

    /**
     * @param int $addressId
     * @return bool|Addresses
     */
    public static function findAddressById(int $addressId)
    {
        $result = Addresses::findFirstByAddressId($addressId);
        if ($result) {
            return $result;
        }
        return false;
    }

    /**
     * @param int $nodeId
     * @return bool|array
     */
    public static function findAddressesByNodeId(int $nodeId)
    {
        if (isset($_GET['pageSize']) && isset($_GET['pageNum'])) {
            $pageSize = (int)$_GET['pageSize'];
            $pageNum = (int)$_GET['pageNum'];
            $results = Addresses::find(
                [
                    'conditions' => 'node_id = :node_id:',
                    'bind' => [
                        'node_id' => $nodeId,
                    ],
                    'limit' => $pageSize,
                    'offset' => ($pageNum - 1) * $pageSize
                ]
            );
        } else {
            $results = Addresses::findByNodeId($nodeId);
        }
        if ($results) {
            return $results;
        }
        return false;
    }

    /**
     * @param int $Id
     * @return int
     */
    public static function getAllNodeAddressesCount(int $Id): int
    {
        $results = Addresses::findByNodeId($Id);
        if ($results) {
            return count($results);
        }
        return 0;
    }


}