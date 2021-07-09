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

    /**
     * @param int $nodeId
     * @return bool|array
     */
    public static function findNodeById(int $nodeId)
    {
        $result = Nodes::findFirstByNodeId($nodeId);
        if ($result) {
            return $result;
        }
        return false;
    }


    /**
     * @param int $userId
     * @return bool|array
     */
    public static function findNodesByUserId(int $userId)
    {
        if (isset($_GET['pageSize']) && isset($_GET['pageNum'])) {
            if (is_numeric($_GET['pageSize']) && is_numeric($_GET['pageNum'])) {
                $pageSize = (int)$_GET['pageSize'];
                $pageNum = (int)$_GET['pageNum'];
                $results = Nodes::find([
                        'conditions' => 'user_id = :user_id:',
                        'bind' => [
                            'user_id' => $userId,
                        ],
                        'limit' => $pageSize,
                        'offset' => ($pageNum - 1) * $pageSize]
                );
            } else {
                return false;
            }
        } else {
            $results = Nodes::findByUserId($userId);
        }
        if ($results) {
            return $results;
        }
        return false;
    }

    /**
     * @return bool|array
     */
    public static function findPublicNodes()
    {
        if (isset($_GET['pageSize']) && isset($_GET['pageNum'])) {
            if (is_numeric($_GET['pageSize']) && is_numeric($_GET['pageNum'])) {
                $pageSize = (int)$_GET['pageSize'];
                $pageNum = (int)$_GET['pageNum'];
                $results = Nodes::find([
                        'conditions' => 'is_public = true',
                        'limit' => $pageSize,
                        'offset' => ($pageNum - 1) * $pageSize]
                );
            } else {
                return false;
            }
        } else {
            $results = Nodes::findByIsPublic(true);
        }
        if ($results) {
            return $results;
        }
        return false;
    }

    /**
     * @return int
     */
    public static function getAllPublicCount():int
    {
        $results = Nodes::findByIsPublic(true);
        if ($results) {
            return count($results);
        }
        return 0;
    }

    /**
     * @param int $userId
     * @return int
     */
    public static function getAllUserNodesCount(int $userId):int
    {
        $results = Nodes::findByUserId($userId);
        if ($results) {
            return count($results);
        }
        return 0;
    }

}