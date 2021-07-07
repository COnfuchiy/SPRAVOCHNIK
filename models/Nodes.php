<?php

use Phalcon\Mvc\Model;

class Nodes extends Model
{
    public $node_id;
    public $user_id;
    public $node_name;
    public $node_last_name;
    public $node_patronymic;
    public $node_company;
    public $node_phone;
    public $node_email;
    public $node_create_date;
    public $node_update_date;
    public $is_public;

    public static function findNodeById(int $nodeId)
    {
        $result = Nodes::findFirstByNodeId($nodeId);
        if ($result) {
            return $result;
        }
        return false;
    }


    public static function findNodesByUserId(int $userId, int $pageSize = 0, int $pageNum = 0)
    {
        if ($pageSize && $pageNum) {
            $results = Nodes::findByUserId($userId, [
                    'limit' => $pageSize,
                    'offset' => ($pageNum - 1) * $pageSize]
            );
        } else {
            $results = Nodes::findByUserId($userId);
        }
        if ($results) {
            return $results;
        }
        return false;
    }

    public static function findPublicNodes(int $pageSize = 0, int $pageNum = 0)
    {
        if ($pageSize && $pageNum) {
            $results = Nodes::findByIsPublic(true, [
                    'limit' => $pageSize,
                    'offset' => ($pageNum - 1) * $pageSize]
            );
        } else {
            $results = Nodes::findByIsPublic(true);
        }
        if ($results) {
            return $results;
        }
        return false;
    }

}