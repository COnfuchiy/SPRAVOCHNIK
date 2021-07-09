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

    /**
     * @param int $code
     * @param array $data
     * @param array $errors
     * @param array $metaData
     * @return Response
     */
    private static function setBaseJsonApiResponse(int $code, array $data, array $errors, array $metaData = []): Response
    {
        $response = new Response();
        $timestamp = date('c');
        $jsonApi = [
            'jsonapi' => [
                'version' => '1.0',
            ],
        ];
        if ($data!==[]) {
            $hash = sha1($timestamp . json_encode($data));
            $content = $data;
        } else {
            $hash = sha1($timestamp . json_encode($errors));
            $content = $errors;
        }
        $meta = [
            'meta' => [
                'timestamp' => $timestamp,
                'hash' => $hash,
            ]
        ];
        if($metaData!==[]){
            $meta['meta'] = array_merge_recursive($meta['meta'],$metaData);
        }
        $response->setStatusCode($code);
        $response->setContentType('application/json', 'utf-8');
        $response->setContent(
            json_encode(array_merge_recursive($jsonApi + $content + $meta))
        );
        return $response;
    }

    /**
     * @param Users $userData
     * @return array
     */
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

    /**
     * @param Nodes $nodeData
     * @return array
     */
    public static function nodeApiJsonSerialize(Nodes $nodeData): array
    {
        return array(
            "type" => "nodes",
            "id" => (int)$nodeData->node_id,
            "attributes" => [
                "node_id" => (int)$nodeData->node_id,
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

    /**
     * @param Addresses $addressData
     * @return array
     */
    public static function addressApiJsonSerialize(Addresses $addressData):array{
        return array(
            "type" => "addresses",
            "id" => (int)$addressData->address_id,
            "attributes" => [
                "address_id" => (int)$addressData->address_id,
                "node_id" => (int)$addressData->node_id,
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

    /**
     * @param int $code
     * @param array $errors
     * @return Response
     */
    public static function createErrorResponse(int $code, array $errors): Response
    {
        return self::setBaseJsonApiResponse(
            $code, [], ["errors" => $errors]
        );
    }

    /**
     * @param string $error
     * @return Response
     */
    public static function createRequestErrorResponse(string $error): Response
    {
        return self::setBaseJsonApiResponse(
            400, [], ["errors" => [$error]]
        );
    }

    /**
     * @param array $data
     * @param int $code
     * @param array $meta
     * @return Response
     */
    public static function createSuccessResponse(array $data = [], int $code = ApiHelper::OK, array $meta=[]): Response
    {
        return self::setBaseJsonApiResponse(
            $code, ["data" =>
            array_keys($data) !== range(0, count($data) - 1) ? [$data] : $data  //TODO hmmm
        ], [],$meta
        );
    }

    /**
     * @param $dataFindMethod
     * @param $type
     * @param $meta
     * @return Response
     */
    public static function getAllRecords($dataFindMethod, $type, $meta): Response{
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
            $outputDataRecords,ApiHelper::OK,$meta
        );
    }

    /**
     * @param string $type
     * @param int $id
     * @param bool $is_public
     * @return array
     */
    public static function getRecordsCount(string $type, int $id = 0, bool $is_public = false):array {
        $metaCount=[
            'count'=>0
        ];
        switch ($type){
            case self::USERS:{
                $metaCount['count'] = Users::getAllCount();
                break;
            }
            case self::NODES:{
                if ($is_public){
                    $metaCount['count'] = Nodes::getAllPublicCount();
                }
                $metaCount['count'] = Nodes::getAllUserNodesCount($id);
                break;
            }
            case self::ADDRESSES:{
                $metaCount['count'] = Addresses::getAllNodeAddressesCount($id);
                break;
            }
        }
        return $metaCount;
    }

}