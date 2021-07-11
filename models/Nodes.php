<?php

use Phalcon\Mvc\Model;

/**
 * Class Nodes
 */
class Nodes extends Model
{
    public int $node_id;
    public int $user_id;
    public string $node_name;
    public string $node_last_name;
    public string $node_patronymic;
    public string $node_company;
    public string $node_phone;
    public string $node_email;
    public int $node_create_date;
    public int $node_update_date;
    public bool $is_public;

    /**
     * @param int $nodeId
     * @return bool|Nodes
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
            $pageSize = (int)$_GET['pageSize'];
            $pageNum = (int)$_GET['pageNum'];
            $results = Nodes::find(
                [
                    'conditions' => 'user_id = :user_id:',
                    'bind' => [
                        'user_id' => $userId,
                    ],
                    'limit' => $pageSize,
                    'offset' => ($pageNum - 1) * $pageSize
                ]
            );
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
            $pageSize = (int)$_GET['pageSize'];
            $pageNum = (int)$_GET['pageNum'];
            $results = Nodes::find(
                [
                    'conditions' => 'is_public = true',
                    'limit' => $pageSize,
                    'offset' => ($pageNum - 1) * $pageSize
                ]
            );
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
    public static function getAllPublicCount(): int
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
    public static function getAllUserNodesCount(int $userId): int
    {
        $results = Nodes::findByUserId($userId);
        if ($results) {
            return count($results);
        }
        return 0;
    }

}