<?php
require_once API_PATH . "/modules/helpers/JwtHelper.php";
require_once API_PATH . "/modules/helpers/ApiHelper.php";
use Phalcon\Http\Response;
class LoginComponent
{
    private static function CheckUserData($callback):Response
    {
        $data = json_decode(file_get_contents('php://input'));
        if ($data) {
            if (isset($data->userLogin) && isset($data->userPassword) &&
                $data->userLogin != '' && $data->userPassword != '') {
                return $callback($data);
            }
            return ApiHelper::createRequestErrorResponse("Incorrect user data");
        }
        return ApiHelper::createRequestErrorResponse("No user data transferred");
    }

    public static function Authorization():Response
    {
        return self::CheckUserData(function ($data) {
            $user = Users::findUserByLogin($data->userLogin);
            if ($user && password_verify($data->userPassword, $user->user_passwd)) {
                $newUserLastDate = time();
                $user->user_last_date = $newUserLastDate;
                if ($user->update()) {
                    $currentTimestamp = new DateTimeImmutable();
                    $jwt = JwtHelper::CreateJwtToken($currentTimestamp->getTimestamp(),$user->user_id, $user->user_login, $newUserLastDate);
                    return ApiHelper::createSuccessResponse(
                        [
                            "token"=>$jwt,
                            "userId"=>$user->user_id,
                            "setIn"=>date('c',$currentTimestamp->getTimestamp()),
                            "expiresIn"=>date('c',$currentTimestamp->getTimestamp()+ConfigJwt::$exp)
                        ],
                        ApiHelper::ACCEPTED
                    );
                }
                $messages = $user->getMessages();
                return ApiHelper::createErrorResponse(
                    ApiHelper::INTERNAL_SERVER_ERROR, $messages);
            }
            return ApiHelper::createRequestErrorResponse("Incorrect username or password");
        });
    }

    public static function Registration():Response
    {
        return self::CheckUserData(function ($data) {
            $user = Users::findUserByLogin($data->userLogin);
            if (!$user) {
                $newUser = new Users();
                $timestamp =  time();
                $newUser->user_login = $data->userLogin;
                $newUser->user_passwd = password_hash($data->userPassword, PASSWORD_BCRYPT);
                $newUser->user_reg_date = $timestamp;
                $newUser->user_last_date = $timestamp;
                if ($newUser->create()) {
                    return ApiHelper::createSuccessResponse([],ApiHelper::CREATED);
                }
                $messages = $user->getMessages();
                return ApiHelper::createErrorResponse(
                    ApiHelper::INTERNAL_SERVER_ERROR, $messages);
            }
            return ApiHelper::createRequestErrorResponse("User already exist");
        });
    }

    public static function CheckAuth($callback):Response{
        $jwtMatches = JwtHelper::GetJwtTokenMatchesForHeaders();
        if (sizeof($jwtMatches) != 0) {
            if ($jwtMatches[1]) {
                $token = JwtHelper::ValidateToken($jwtMatches[1]);
                if ($token) {
                    $now = new DateTimeImmutable();
                    if ($token->iss === ConfigJwt::$iss ||
                        $token->nbf < $now->getTimestamp() ||
                        $token->exp > $now->getTimestamp()) {
                        if ($token->data->login)
                            return $callback($token->data->id);
                    }
                }
                return ApiHelper::createErrorResponse(ApiHelper::UNAUTHORIZED,["Authorization failed"]);
            }
            return ApiHelper::createRequestErrorResponse("Authorization bearer header is empty");
        }
        return ApiHelper::createRequestErrorResponse("Authorization bearer header is unset");
    }
}