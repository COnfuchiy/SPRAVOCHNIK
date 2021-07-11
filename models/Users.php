<?php

use Phalcon\Mvc\Model;

class Users extends Model
{
    public int $user_id;
    public string $user_login;
    public string $user_passwd;
    public int $user_reg_date;
    public int $user_last_date;

    /**
     * @param string $userLogin
     * @return bool|Users
     */
    public static function findUserByLogin(string $userLogin)
    {
        $result = Users::findFirstByUserLogin($userLogin);
        if ($result) {
            return $result;
        }
        return false;
    }

    /**
     * @param string $userId
     * @return bool|array
     */
    public static function findUserById(string $userId)
    {
        $result = Users::findFirstByUserId($userId);
        if ($result) {
            return $result;
        }
        return false;
    }

    /**
     * @return bool|array
     */
    public static function getAll()
    {
        if (isset($_GET['pageSize']) && isset($_GET['pageNum'])) {
            $pageSize = (int)$_GET['pageSize'];
            $pageNum = (int)$_GET['pageNum'];
            $results = Users::find(
                [
                    'limit' => $pageSize,
                    'offset' => ($pageNum - 1) * $pageSize
                ]
            );
        } else {
            $results = Users::find();
        }
        if ($results) {
            return $results;
        }
        return false;
    }

    public static function getAllCount(): int
    {
        $results = Users::find();
        if ($results) {
            return count($results);
        }
        return 0;
    }

}
