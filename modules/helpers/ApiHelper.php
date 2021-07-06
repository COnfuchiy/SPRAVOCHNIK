<?php

use Phalcon\Http\Response;

require_once API_PATH . "/modules/helpers/ApiRoutesHelper.php";


class ApiHelper
{
    const OK = 200;
    const CREATED = 201;
    const ACCEPTED = 202;
    const TEMPORARY_REDIRECT = 307;
    const PERMANENTLY_REDIRECT = 308;
    const BAD_REQUEST = 400;
    const UNAUTHORIZED = 401;
    const FORBIDDEN = 403;
    const NOT_FOUND = 404;
    const METHOD_NOT_ALLOWED = 405;
    const I_M_A_TEAPOT = 418;
    const INTERNAL_SERVER_ERROR = 500;
    const NOT_IMPLEMENTED = 501;
    const BAD_GATEWAY = 502;


    private static function setBaseJsonApiResponse(int $code, string $data, string $errors): Response
    {
        $response = new Response();
        $timestamp = date('c');
        $jsonApi = [
            'jsonapi' => [
                'version' => '1.0',
            ],
        ];
        if ($data) {
            $hash = sha1($timestamp . $data);
            $content = json_decode($data, true);
        } else {
            $hash = sha1($timestamp . $errors);
            $content = json_decode($errors, true);
        }
        $meta = [
            'meta' => [
                'timestamp' => $timestamp,
                'hash' => $hash,
            ]
        ];
        $response->setStatusCode($code);
        $response->setJsonContent(
            array_merge_recursive($jsonApi + $content + $meta)
        );
        return $response;
    }

    public static function userApiJsonSerialize($userData): array
    {
        return array(
            "type" => "users",
            "id" => $userData->user_id,
            "attributes" => [
                "user_id" => $userData->user_id,
                "user_login" => $userData->user_login,
                "user_reg_date" => date('c', $userData->user_reg_date),
                "user_last_date" => date('c', $userData->user_last_date)
            ],
            "links" => [
                "self" => ApiRoutesHelper::getUserUrl($userData->user_id)
            ]
        );
    }
    public static function nodeApiJsonSerialize($nodeData): array
    {
        return array(
            "type" => "nodes",
            "id" => $nodeData->user_id,
            "attributes" => [
                "node_id" => $nodeData->node_id,
                "user_id" => $nodeData->user_id,
                "node_name" => $nodeData->node_name,
                "node_last_name" => $nodeData->node_last_name,
                "node_patronymic" => $nodeData->node_patronymic,
                "node_company" => $nodeData->node_company,
                "node_phone" => $nodeData->node_phone,
                "node_create_date" => date('c', $nodeData->node_create_date),
                "node_update_date" => date('c', $nodeData->node_update_date),
                "is_public" => $nodeData->is_public,
            ],
            "links" => [
                "self" => ApiRoutesHelper::getNodeUrl($nodeData->node_id)
            ]
        );
    }

    public static function createErrorResponse(int $code, array $errors): Response
    {
        return self::setBaseJsonApiResponse(
            $code, "", json_encode(["errors" => $errors])
        );
    }

    public static function createRequestErrorResponse(string $error): Response
    {
        return self::setBaseJsonApiResponse(
            400, "", json_encode(["errors" => [$error]])
        );
    }

    public static function createSuccessResponse(array $data = [], int $code = 200): Response
    {
        return self::setBaseJsonApiResponse(
            $code, json_encode(["data" =>
            array_keys($data) !== range(0, count($data) - 1) ? [$data] : $data  //TODO hmmm
        ]), ""
        );
    }


}