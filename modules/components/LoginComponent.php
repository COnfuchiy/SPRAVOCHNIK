<?php

require_once API_PATH . "/modules/helpers/JwtHelper.php";
require_once API_PATH . "/modules/helpers/ApiHelper.php";

use Phalcon\Http\Response;
use Phalcon\Http\Request;

/**
 * Class LoginComponent
 */
class LoginComponent
{

    public static Phalcon\Mvc\Micro $app;

    /**
     * @param callable $callback
     * @return Response
     */
    private static function checkUserData(callable $callback): Response
    {
        $request = new Request();
        //get json user data from request body
        $data = $request->getJsonRawBody();
        if ($data) {
            if (isset($data->userLogin) && isset($data->userPassword) &&
                $data->userLogin != '' && $data->userPassword != '') {
                return $callback($data);
            }
            return ApiHelper::createRequestErrorResponse("Incorrect user data");
        }
        return ApiHelper::createRequestErrorResponse("No user data transferred");
    }

    /**
     * @return Response
     */
    public static function authorization(): Response
    {
        return self::checkUserData(
            function ($data) {
                try {
                    $user = Users::findUserByLogin($data->userLogin);
                    if ($user) {
                        if (password_verify($data->userPassword, $user->user_passwd)) {
                            $newUserLastDate = time();
                            $user->user_last_date = $newUserLastDate;
                            if ($user->update()) {
                                $currentTimestamp = new DateTimeImmutable();
                                $jwt = JwtHelper::CreateJwtToken(
                                    $currentTimestamp->getTimestamp(),
                                    $user->user_id,
                                    $user->user_login,
                                    $newUserLastDate
                                );
                                // set redis value
                                self::$app['redis']->set($user->user_id, $jwt);
                                return ApiHelper::createSuccessResponse(
                                    [
                                        "token" => $jwt,
                                        "userId" => $user->user_id,
                                        "setIn" => date('c', $currentTimestamp->getTimestamp()),
                                        "expiresIn" => date('c', $currentTimestamp->getTimestamp() + ConfigJwt::$exp)
                                    ],
                                    ApiHelper::ACCEPTED
                                );
                            }
                            $messages = $user->getMessages();
                            return ApiHelper::createErrorResponse(
                                ApiHelper::INTERNAL_SERVER_ERROR,
                                $messages
                            );
                        }
                        return ApiHelper::createRequestErrorResponse("Incorrect username or password");
                    }
                    return ApiHelper::createErrorResponse(
                        ApiHelper::NOT_FOUND,
                        ["No find user in database"]
                    );
                } catch (Exception $exception) {
                    return ApiHelper::createErrorResponse(
                        ApiHelper::INTERNAL_SERVER_ERROR,
                        [$exception->getMessage()]
                    );
                }
            }
        );
    }

    /**
     * @return Response
     */
    public static function registration(): Response
    {
        return self::checkUserData(
            function ($data) {
                try {
                    $user = Users::findUserByLogin($data->userLogin);
                    if (!$user) {
                        $newUser = new Users();
                        $timestamp = time();
                        $newUser->user_login = $data->userLogin;
                        $newUser->user_passwd = (string)password_hash($data->userPassword, PASSWORD_BCRYPT);
                        $newUser->user_reg_date = $timestamp;
                        $newUser->user_last_date = $timestamp;
                        if ($newUser->create()) {
                            return ApiHelper::createSuccessResponse(
                                ApiHelper::userApiJsonSerialize($newUser),
                                ApiHelper::CREATED
                            );
                        }
                        $messages = $user->getMessages();
                        return ApiHelper::createErrorResponse(
                            ApiHelper::INTERNAL_SERVER_ERROR,
                            $messages
                        );
                    }
                    return ApiHelper::createRequestErrorResponse("User already exist");
                } catch (Exception $exception) {
                    return ApiHelper::createErrorResponse(
                        ApiHelper::INTERNAL_SERVER_ERROR,
                        [$exception->getMessage()]
                    );
                }
            }
        );
    }

    /**
     * @param callable $callback
     * @return Response
     */
    public static function checkAuth(callable $callback): Response
    {
        $jwtMatches = JwtHelper::GetJwtTokenMatchesForHeaders();
        if (sizeof($jwtMatches) != 0) {
            if ($jwtMatches[1]) {
                $token = JwtHelper::ValidateToken($jwtMatches[1]);
                if ($token) {
                    // check unique jwt key
                    if (self::$app['redis']->get($token->data->id) == $jwtMatches[1]) {
                        $now = new DateTimeImmutable();
                        if ($token->iss === ConfigJwt::$iss ||
                            $token->nbf < $now->getTimestamp() ||
                            $token->exp > $now->getTimestamp()) {
                            return $callback($token->data->id);
                        }
                    }
                    return ApiHelper::createErrorResponse(
                        ApiHelper::UNAUTHORIZED,
                        ["Invalid token: a new token has already been generated"]
                    );
                }
                return ApiHelper::createErrorResponse(ApiHelper::UNAUTHORIZED, ["Authorization failed"]);
            }
            return ApiHelper::createRequestErrorResponse("Authorization bearer header is empty");
        }
        return ApiHelper::createRequestErrorResponse("Authorization bearer header is unset");
    }

}