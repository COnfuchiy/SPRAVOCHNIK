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

    public static function findAddressById(int $addressId)
    {
        try {
            $result = Addresses::findFirstByAddressId($addressId);
            if ($result) {
                return $result;
            }
        } catch (Exception $exception) {
        }
        return false;
    }

    public static function findAddressesByNodeId(int $nodeId, int $pageSize = 0, int $pageNum = 0)
    {
        try {
            if ($pageSize && $pageNum) {
                $results = Addresses::findByNodeId($nodeId, [
                        'limit' => $pageSize,
                        'offset' => ($pageNum - 1) * $pageSize]
                );
            } else {
                $results = Nodes::findByNodeId($nodeId);
            }
            if ($results) {
                return $results;
            }
        } catch (Exception $exception) {
        }
        return false;
    }


}