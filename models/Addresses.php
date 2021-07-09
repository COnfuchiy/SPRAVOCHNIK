<?php

use Phalcon\Mvc\Model;

class Addresses extends Model
{
    public $node_id;
    public $address_id;
    public $address_name;
    public $address_country;
    public $address_region;
    public $address_city;
    public $address_street;
    public $address_house;
    public $address_entrance;
    public $address_apartment;
    public $address_create_date;
    public $address_update_date;

    /**
     * @param int $addressId
     * @return bool|array
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
            if (is_numeric($_GET['pageSize']) && is_numeric($_GET['pageNum'])) {
                $pageSize = (int)$_GET['pageSize'];
                $pageNum = (int)$_GET['pageNum'];
                $results = Addresses::find([
                        'conditions' => 'node_id = :node_id:',
                        'bind' => [
                            'node_id' => $nodeId,
                        ],
                        'limit' => $pageSize,
                        'offset' => ($pageNum - 1) * $pageSize]
                );
            } else {
                return false;
            }
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
    public static function getAllNodeAddressesCount(int $Id):int
    {
        $results = Addresses::findByNodeId($Id);
        if ($results) {
            return count($results);
        }
        return 0;
    }


}