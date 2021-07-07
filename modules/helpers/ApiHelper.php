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
    const IM_A_TEAPOT = 418;
    const INTERNAL_SERVER_ERROR = 500;
    const NOT_IMPLEMENTED = 501;
    const BAD_GATEWAY = 502;

    const USERS = Users::class;
    const NODES = Nodes::class;
    const ADDRESSES = Addresses::class;

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

    public static function userApiJsonSerialize(Users $userData): array
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
    public static function nodeApiJsonSerialize(Nodes $nodeData): array
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
                "node_email" => $nodeData->node_email,
                "node_create_date" => date('c', $nodeData->node_create_date),
                "node_update_date" => date('c', $nodeData->node_update_date),
                "is_public" => $nodeData->is_public,
            ],
            "links" => [
                "self" => ApiRoutesHelper::getNodeUrl($nodeData->node_id)
            ]
        );
    }

    public static function addressApiJsonSerialize(Addresses $addressData):array{
        return array(
            "type" => "addresses",
            "id" => $addressData->address_id,
            "attributes" => [
                "address_id" => $addressData->address_id,
                "node_id" => $addressData->node_id,
                "address_name" => $addressData->address_name,
                "address_country" => $addressData->address_country,
                "address_region" => $addressData->address_region,
                "address_city" => $addressData->address_city,
                "address_street" => $addressData->address_street,
                "address_house" => $addressData->address_house,
                "address_entrance" => $addressData->address_entrance,
                "address_apartment" => $addressData->address_apartment,
                "address_create_date" => date('c', $addressData->address_create_date),
                "address_update_date" => date('c', $addressData->address_update_date),
            ],
            "links" => [
                "self" => ApiRoutesHelper::getNodeUrl($addressData->address_id)
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

    public static function getAllRecords($dataFindMethod, $type): Response{
        try {
            $data = $dataFindMethod();
        } catch (Exception $exception) {
            return ApiHelper::createErrorResponse(
                ApiHelper::INTERNAL_SERVER_ERROR, [$exception->getMessage()]);
        }
        $outputDataRecords = [];
        foreach ($data as $record) {
            switch ($type){
                case self::USERS:{
                    $outputDataRecords[] = ApiHelper::userApiJsonSerialize($record);
                    break;
                }
                case self::NODES:{
                    $outputDataRecords[] = ApiHelper::nodeApiJsonSerialize($record);
                    break;
                }
                case self::ADDRESSES:{
                    $outputDataRecords[] = ApiHelper::addressApiJsonSerialize($record);
                    break;
                }
            }
        }
        return ApiHelper::createSuccessResponse(
            $outputDataRecords
        );
    }

}