<?php

require_once API_PATH."/config/configJwt.php";
require_once $_SERVER['DOCUMENT_ROOT'].'/vendor/autoload.php';
use Firebase\JWT\JWT;

class JwtHelper
{

    public static function CreateJwtToken(int $currentTimestamp, int $userId, string $userLogin, string $userLastDate):string {
        $token = array(
            "iss" => ConfigJwt::$iss,
            "aud" => ConfigJwt::$aud,
            "iat" => $currentTimestamp,
            "nbf" => $currentTimestamp,
            "exp"=> $currentTimestamp + ConfigJwt::$exp,
            "data" => array(
                "id" => $userId,
                "login"=>$userLogin,
                "lastDate"=>$userLastDate
            )
        );
        try {
            return JWT::encode($token, ConfigJwt::$key, 'HS512');
        }
        catch (Exception $exception){
            return "";
        }
    }
    public static function ValidateToken(string $jwtToken){
        try {
            return JWT::decode($jwtToken, ConfigJwt::$key, array('HS512'));
        } catch (Exception $exception){
            return array();
        }
    }
    public static function GetJwtTokenMatchesForHeaders():array {
        if (isset($_SERVER['HTTP_AUTHORIZATION']) && preg_match('/Bearer\s(\S+)/', $_SERVER['HTTP_AUTHORIZATION'], $matches)) {
            return $matches;
        }
        else{
            return array();
        }
    }
}