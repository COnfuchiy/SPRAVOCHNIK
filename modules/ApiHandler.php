<?php
require_once API_PATH . "/modules/components/LoginComponent.php";
require_once API_PATH . "/modules/helpers/ApiHelper.php";

use Phalcon\Http\Response;

class ApiHandler
{
    public static function authorization(): Response
    {
        return LoginComponent::Authorization();
    }

    public static function registration(): Response
    {
        return LoginComponent::Registration();
    }

    public static function getUserDataById($userId): Response
    {
        return LoginComponent::CheckAuth(function ($tokenUserId) use ($userId): Response {
            // 0 refers to the current user (from the authorization token)
            if ($userId == 0) {
                $userId = $tokenUserId;
            }
            $userFullData = Users::findUserById($userId);
            if ($userFullData) {
                return ApiHelper::createSuccessResponse(
                    ApiHelper::userApiJsonSerialize($userFullData)
                );
            }
            return ApiHelper::createErrorResponse(
                ApiHelper::NOT_FOUND, ["No finds user in database"]);

        });
    }

    public static function getAllUsers(int $pageSize= 0, int $pageNum = 0): Response{
        return LoginComponent::CheckAuth(function () use($pageSize, $pageNum): Response {
            $users= Users::getAll($pageSize, $pageNum);
            if ($users){
                $outputUserData = [];
                foreach ($users as $user){
                    $outputUserData[] = ApiHelper::userApiJsonSerialize($user);
                }
                return ApiHelper::createSuccessResponse(
                    $outputUserData
                );
            }
            return ApiHelper::createErrorResponse(
                ApiHelper::I_M_A_TEAPOT, ["Nothing there..."]);

        });
    }

    public static function updateUserById($userId): Response
    {
        return LoginComponent::CheckAuth(function ($tokenUserId) use ($userId): Response {
            // 0 refers to the current user (from the authorization token)
            if ($userId == 0) {
                $userId = $tokenUserId;
                $user = Users::findUserById($userId);
                if ($user) {
                    try{
                        $data = json_decode(file_get_contents('php://input'));
                        $user->user_passwd = password_hash($data->userPassword,PASSWORD_BCRYPT);
                        if($user->update()){
                            return ApiHelper::createSuccessResponse();
                        }
                        $messages = $user->getMessages();
                        return ApiHelper::createErrorResponse(
                            ApiHelper::INTERNAL_SERVER_ERROR, $messages);
                    }catch (Exception $exception){
                        return ApiHelper::createRequestErrorResponse($exception->getMessage());
                    }
                }
                return ApiHelper::createErrorResponse(
                    ApiHelper::NOT_FOUND, ["No finds user in database"]);
            } else {
                return ApiHelper::createErrorResponse(
                    ApiHelper::FORBIDDEN, ["Insufficient rights"]);
            }

        });
    }

    public static function getAllUserNodes(int $pageSize= 0, int $pageNum = 0): Response{
        return LoginComponent::CheckAuth(function ($userId) use($pageSize, $pageNum): Response {
            $nodes = Nodes::findNodesByUserId($userId,$pageSize, $pageNum);
            $outputNodesData = [];
            foreach ($nodes as $node){
                $outputUserData[] = ApiHelper::nodeApiJsonSerialize($node);
            }
            return ApiHelper::createSuccessResponse(
                $outputNodesData
            );
        });
    }

    public static function getNodeDataById($nodeId){
        return LoginComponent::CheckAuth(function ($userId) use($nodeId): Response {
            $node = Nodes::findNodeById($nodeId);
            if($node){
                if ($node->user_id === $userId || $node->is_public){
                    return ApiHelper::createSuccessResponse(
                        ApiHelper::nodeApiJsonSerialize($node)
                    );
                }
                return ApiHelper::createErrorResponse(
                    ApiHelper::FORBIDDEN, ["Insufficient rights"]);
            }
            return ApiHelper::createErrorResponse(
                ApiHelper::NOT_FOUND, ["No finds node in database"]);

        });
    }

    public static function createNode(){
        return LoginComponent::CheckAuth(function ($userId): Response {
            $data = json_decode(file_get_contents('php://input'));
            if($data){

            }
            return ApiHelper::createRequestErrorResponse("No user data transferred");

        });
    }
}