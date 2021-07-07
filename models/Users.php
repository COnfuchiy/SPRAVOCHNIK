<?php

use Phalcon\Mvc\Model;

class Users extends Model
{
    public $user_id;
    public $user_login;
    public $user_passwd;
    public $user_reg_date;
    public $user_last_date;

    public static function findUserByLogin(string $userLogin)
    {
        $result = Users::findFirstByUserLogin($userLogin);
        if ($result) {
            return $result;
        }
        return false;
    }

    public static function findUserById(string $userId)
    {
        $result = Users::findFirstByUserId($userId);
        if ($result) {
            return $result;
        }
        return false;
    }

    public static function getAll(int $pageSize = 0, int $pageNum = 0)
    {
        if ($pageSize && $pageNum) {
            $results = Users::find([
                'limit' => $pageSize,
                'offset' => ($pageNum - 1) * $pageSize
            ]);
        } else {

            $results = Users::find();
        }
        if ($results) {
            return $results;
        }
        return false;
    }


}
