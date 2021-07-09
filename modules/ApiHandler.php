<?php
require_once API_PATH . "/modules/components/LoginComponent.php";
require_once API_PATH . "/modules/helpers/ApiHelper.php";

use Phalcon\Http\Response;

/**
 * Class ApiHandler
 */
class ApiHandler
{
    /**
     * Authorization action
     * @return Response
     */
    public static function authorization(): Response
    {
        return LoginComponent::Authorization();
    }

    /**
     * Registration action
     * @return Response
     */
    public static function registration(): Response
    {
        return LoginComponent::Registration();
    }

    /**
     * @param int $userId
     * @return Response
     */
    public static function getUserById(int $userId): Response
    {
        return LoginComponent::CheckAuth(function ($tokenUserId) use ($userId): Response {
            // 0 refers to the current user (from the authorization token)
            if ($userId == 0) {
                $userId = $tokenUserId;
            }
            try {
                $userFullData = Users::findUserById($userId);
            } catch (Exception $exception) {
                return ApiHelper::createErrorResponse(
                    ApiHelper::INTERNAL_SERVER_ERROR, [$exception->getMessage()]);
            }
            if ($userFullData) {
                return ApiHelper::createSuccessResponse(
                    ApiHelper::userApiJsonSerialize($userFullData)
                );
            }
            return ApiHelper::createErrorResponse(
                ApiHelper::NOT_FOUND, ["No find user in database"]);

        });
    }

    /**
     * @return Response
     */
    public static function getAllUsers(): Response
    {
        return LoginComponent::CheckAuth(function (): Response {
            return ApiHelper::getAllRecords(function () {
                return Users::getAll();
            }, ApiHelper::USERS,ApiHelper::getRecordsCount(ApiHelper::USERS));
        });
    }

    /**
     * @param int $userId
     * @return Response
     */
    public static function updateUser(int $userId): Response
    {
        return LoginComponent::CheckAuth(function ($tokenUserId) use ($userId): Response {
            // 0 refers to the current user (from the authorization token)
            if ($userId == 0) {

                /** @var int $tokenUserId is a userId from LoginComponent::CheckAuth() but renamed */
                $userId = $tokenUserId;
                try {
                    $data = json_decode(file_get_contents('php://input'));
                    if ($data && $data->userPassword) {
                        $user = Users::findUserById($userId);
                        if ($user) {
                            $user->user_passwd = password_hash($data->userPassword, PASSWORD_BCRYPT);
                            if ($user->update()) {
                                return ApiHelper::createSuccessResponse();
                            }
                            $messages = $user->getMessages();
                            return ApiHelper::createErrorResponse(
                                ApiHelper::INTERNAL_SERVER_ERROR, $messages);
                        }
                        return ApiHelper::createErrorResponse(
                            ApiHelper::NOT_FOUND, ["No find user in database"]);
                    }
                } catch (Exception $exception) {
                    return ApiHelper::createErrorResponse(
                        ApiHelper::INTERNAL_SERVER_ERROR, [$exception->getMessage()]);
                }
                return ApiHelper::createRequestErrorResponse("No user data transferred");
            } else {
                return ApiHelper::createErrorResponse(
                    ApiHelper::FORBIDDEN, ["Insufficient rights"]);
            }

        });
    }

    /**
     * @return Response
     */
    public static function getAllUserNodes(): Response
    {
        return LoginComponent::CheckAuth(function ($userId): Response {
            return ApiHelper::getAllRecords(function () use($userId) {
                return Nodes::findNodesByUserId($userId);
            }, ApiHelper::NODES,ApiHelper::getRecordsCount(ApiHelper::NODES,$userId,false));
        });
    }

    /**
     * @param int $nodeId
     * @return Response
     */
    public static function getNode(int $nodeId)
    {
        return LoginComponent::CheckAuth(function ($userId) use ($nodeId): Response {
            try {
                $node = Nodes::findNodeById($nodeId);
            } catch (Exception $exception) {
                return ApiHelper::createErrorResponse(
                    ApiHelper::INTERNAL_SERVER_ERROR, [$exception->getMessage()]);
            }
            if ($node) {
                if ($node->user_id === $userId || $node->is_public) {
                    return ApiHelper::createSuccessResponse(
                        ApiHelper::nodeApiJsonSerialize($node)
                    );
                }
                return ApiHelper::createErrorResponse(
                    ApiHelper::FORBIDDEN, ["Insufficient rights"]);
            }
            return ApiHelper::createErrorResponse(
                ApiHelper::NOT_FOUND, ["No find node in database"]);
        });
    }

    public static function createNode()
    {
        return LoginComponent::CheckAuth(function (int $userId): Response {
            //get json data from request body
            $data = json_decode(file_get_contents('php://input'));
            if ($data) {
                try {
                    $newNode = new Nodes();
                    $timestamp = time();
                    $newNode->assign([
                        'user_id' => $userId,
                        'node_name' => isset($data->nodeName) ? $data->nodeName : null,
                        'node_last_name' => isset($data->nodeLastName) ? $data->nodeLastName : null,
                        'node_patronymic' => isset($data->nodePatronymic) ? $data->nodePatronymic : null,
                        'node_company' => isset($data->nodeCompany) ? $data->nodeCompany : null,
                        'node_phone' => $data->nodePhone,
                        'node_email' => isset($data->nodeEmail) ? $data->nodeEmail : null,
                        'node_create_date' => $timestamp,
                        'node_update_date' => $timestamp,
                        'is_public' => $data->isPublic,
                    ]);
                } catch (Exception $exception) {
                    return ApiHelper::createErrorResponse(
                        ApiHelper::INTERNAL_SERVER_ERROR, [$exception->getMessage()]);
                }
                if ($newNode->create()) {
                    return ApiHelper::createSuccessResponse(
                        ApiHelper::nodeApiJsonSerialize($newNode),ApiHelper::CREATED
                    );
                }
                $messages = $newNode->getMessages();
                return ApiHelper::createErrorResponse(
                    ApiHelper::INTERNAL_SERVER_ERROR, $messages);

            }
            return ApiHelper::createRequestErrorResponse("No node data transferred");
        });
    }

    /**
     * @param int $nodeId
     * @return Response
     */
    public static function updateNode(int $nodeId): Response
    {
        return LoginComponent::CheckAuth(function ($userId) use ($nodeId): Response {
            //get json data from request body
            $data = json_decode(file_get_contents('php://input'));
            if ($data) {
                try {
                    $node = Nodes::findNodeById($nodeId);
                    if ($node) {
                        if ($node->user_id === $userId) {
                            $node->assign([
                                'node_name' => isset($data->nodeName) ? $data->nodeName : $node->node_name,
                                'node_last_name' => isset($data->nodeLastName) ? $data->nodeLastName : $node->node_last_name,
                                'node_patronymic' => isset($data->nodePatronymic) ? $data->nodePatronymic : $node->node_patronymic,
                                'node_company' => isset($data->nodeCompany) ? $data->nodeCompany : $node->node_company,
                                'node_phone' => isset($data->nodePhone) ? $data->nodePhone : $node->node_phone,
                                'node_email' => isset($data->nodeEmail) ? $data->nodeEmail : $node->node_email,
                                'is_public' => isset($data->isPublic) ? $data->isPublic : $node->is_public,
                                'node_update_date' => time(),
                            ]);
                            if ($node->update()) {
                                return ApiHelper::createSuccessResponse();
                            }
                            $messages = $node->getMessages();
                            return ApiHelper::createErrorResponse(
                                ApiHelper::INTERNAL_SERVER_ERROR, $messages);
                        }
                        return ApiHelper::createErrorResponse(
                            ApiHelper::FORBIDDEN, ["Insufficient rights"]);
                    }
                    return ApiHelper::createErrorResponse(
                        ApiHelper::INTERNAL_SERVER_ERROR, ["No find node in database"]);
                } catch (Exception $exception) {
                    return ApiHelper::createErrorResponse(
                        ApiHelper::INTERNAL_SERVER_ERROR, [$exception->getMessage()]);
                }
            }
            return ApiHelper::createRequestErrorResponse("No node data transferred");
        });
    }

    /**
     * @param int $nodeId
     * @return Response
     */
    public static function deleteNode(int $nodeId): Response
    {
        return LoginComponent::CheckAuth(function ($userId) use ($nodeId): Response {
            try {
                $node = Nodes::findNodeById($nodeId);
            } catch (Exception $exception) {
                return ApiHelper::createErrorResponse(
                    ApiHelper::INTERNAL_SERVER_ERROR, [$exception->getMessage()]);
            }
            if ($node) {
                if ($node->user_id === $userId) {
                    $addresses = Addresses::findAddressesByNodeId($nodeId);
                    $addressesErrors = [];
                    foreach ($addresses as $address) {
                        if (!$address->delete()) {
                            $addressesErrors[] = $address->getMessages();
                        }
                    }
                    if ($node->delete()) {
                        if (count($addressesErrors)) {
                            // its error, but is 200 answer. So magic...
                            return ApiHelper::createErrorResponse(ApiHelper::OK, $addressesErrors);
                        }
                        return ApiHelper::createSuccessResponse();
                    }
                    $messages = $node->getMessages();
                    return ApiHelper::createErrorResponse(
                        ApiHelper::INTERNAL_SERVER_ERROR, $messages);
                }
                return ApiHelper::createErrorResponse(
                    ApiHelper::FORBIDDEN, ["Insufficient rights"]);
            }
            return ApiHelper::createErrorResponse(
                ApiHelper::INTERNAL_SERVER_ERROR, ["No find node in database"]);
        });
    }

    /**
     * @return Response
     */
    public static function getAllPublicNodes(): Response
    {
        return LoginComponent::CheckAuth(function ($userId): Response {
            return ApiHelper::getAllRecords(function () use ($userId) {
                return Nodes::findPublicNodes();
            }, ApiHelper::NODES, ApiHelper::getRecordsCount(ApiHelper::NODES,true));
        });
    }

    /**
     * @param int $nodeId
     * @return Response
     */
    public static function getAllNodeAddresses(int $nodeId): Response
    {
        return LoginComponent::CheckAuth(function ($userId) use ($nodeId): Response {
            try {
                $node = Nodes::findNodeById($nodeId);
            } catch (Exception $exception) {
                return ApiHelper::createErrorResponse(
                    ApiHelper::INTERNAL_SERVER_ERROR, [$exception->getMessage()]);
            }
            if ($node) {
                if ($node->user_id === $userId || $node->is_public) {
                    return ApiHelper::getAllRecords(function () use ($nodeId) {
                        return Addresses::findAddressesByNodeId($nodeId);
                    }, ApiHelper::ADDRESSES, ApiHelper::getRecordsCount(ApiHelper::ADDRESSES));
                }
                return ApiHelper::createErrorResponse(
                    ApiHelper::FORBIDDEN, ["Insufficient rights"]);
            }
            return ApiHelper::createErrorResponse(
                ApiHelper::NOT_FOUND, ["No find node in database"]);
        });
    }

    /**
     * @param int $addressId
     * @return Response
     */
    public static function getAddress(int $addressId): Response
    {
        return LoginComponent::CheckAuth(function ($userId) use ($addressId): Response {
            try {
                $address = Addresses::findAddressById($addressId);
                if ($address) {
                    $node = Nodes::findNodeById($address->node_id);
                    if ($node) {
                        if ($node->user_id === $userId || $node->is_public) {
                            return ApiHelper::createSuccessResponse(
                                ApiHelper::addressApiJsonSerialize($address)
                            );
                        }
                        return ApiHelper::createErrorResponse(
                            ApiHelper::FORBIDDEN, ["Insufficient rights"]);
                    }
                    return ApiHelper::createErrorResponse(
                        ApiHelper::NOT_FOUND, ["No find bound node in database"]);
                }
                return ApiHelper::createErrorResponse(
                    ApiHelper::NOT_FOUND, ["No find address in database"]);
            } catch (Exception $exception) {
                return ApiHelper::createErrorResponse(
                    ApiHelper::INTERNAL_SERVER_ERROR, [$exception->getMessage()]);
            }
        });
    }

    /**
     * @param $nodeId
     * @return Response
     */
    public static function createAddress($nodeId): Response
    {
        return LoginComponent::CheckAuth(function () use ($nodeId): Response {
            $data = json_decode(file_get_contents('php://input'));
            if ($data) {
                try {
                    $newAddress = new Addresses();
                    $timestamp = time();
                    $newAddress->assign([
                        'node_id' => $nodeId,
                        'address_name' => $data->addressName,
                        'address_country' => $data->addressCountry,
                        'address_region' => isset($data->addressRegion) ? $data->addressRegion : null,
                        'address_street' => $data->addressStreet,
                        'address_house' => $data->addressHouse,
                        'address_entrance' => isset($data->addressEntrance) ? $data->addressEntrance : null,
                        'address_apartment' => isset($data->addressApartment) ? $data->addressApartment : null,
                        'address_create_date' => $timestamp,
                        'address_update_date' => $timestamp,
                    ]);
                } catch (Exception $exception) {
                    return ApiHelper::createErrorResponse(
                        ApiHelper::INTERNAL_SERVER_ERROR, [$exception->getMessage()]);
                }
                if ($newAddress->create()) {
                    return ApiHelper::createSuccessResponse(
                        ApiHelper::addressApiJsonSerialize($newAddress),ApiHelper::CREATED
                    );
                }
                $messages = $newAddress->getMessages();
                return ApiHelper::createErrorResponse(
                    ApiHelper::INTERNAL_SERVER_ERROR, $messages);
            }
            return ApiHelper::createRequestErrorResponse("No node data transferred");
        });
    }

    /**
     * @param $addressId
     * @return Response
     */
    public static function updateAddress($addressId): Response
    {
        return LoginComponent::CheckAuth(function ($userId) use ($addressId): Response {
            $data = json_decode(file_get_contents('php://input'));
            if ($data) {
                try {
                    $address = Addresses::findAddressById($addressId);
                    if ($address) {
                        $node = Nodes::findNodeById($address->node_id);
                        if ($node) {
                            if ($node->user_id === $userId) {
                                $address->assign([
                                    'address_name' => isset($data->addressName) ? $data->addressName : $address->address_name,
                                    'address_country' => isset($data->addressCountry) ? $data->addressCountry : $address->address_country,
                                    'address_region' => isset($data->addressRegion) ? $data->addressRegion : $address->address_region,
                                    'address_street' => isset($data->addressStreet) ? $data->addressStreet : $address->address_street,
                                    'address_house' => isset($data->addressHouse) ? $data->addressHouse : $address->address_house,
                                    'address_entrance' => isset($data->addressEntrance) ? $data->addressEntrance : $address->address_entrance,
                                    'address_apartment' => isset($data->addressApartment) ? $data->addressApartment : $address->address_apartment,
                                    'address_update_date' => time(),
                                ]);
                                if ($address->update()) {
                                    return ApiHelper::createSuccessResponse();
                                }
                                $messages = $address->getMessages();
                                return ApiHelper::createErrorResponse(
                                    ApiHelper::INTERNAL_SERVER_ERROR, $messages);
                            }
                            return ApiHelper::createErrorResponse(
                                ApiHelper::FORBIDDEN, ["Insufficient rights"]);
                        }
                        return ApiHelper::createErrorResponse(
                            ApiHelper::NOT_FOUND, ["No find bound node in database"]);
                    }
                    return ApiHelper::createErrorResponse(
                        ApiHelper::INTERNAL_SERVER_ERROR, ["No find address in database"]);
                } catch (Exception $exception) {
                    return ApiHelper::createErrorResponse(
                        ApiHelper::INTERNAL_SERVER_ERROR, [$exception->getMessage()]);
                }
            }
            return ApiHelper::createRequestErrorResponse("No address data transferred");
        });
    }

    /**
     * @param $addressId
     * @return Response
     */
    public static function deleteAddress($addressId): Response
    {
        return LoginComponent::CheckAuth(function ($userId) use ($addressId): Response {
            try {
                $address = Addresses::findAddressById($addressId);
                if ($address) {
                    $node = Nodes::findNodeById($address->node_id);
                    if ($node) {
                        if ($node->user_id === $userId) {
                            if ($address->delete()) {
                                return ApiHelper::createSuccessResponse();
                            }
                            $messages = $address->getMessages();
                            return ApiHelper::createErrorResponse(
                                ApiHelper::INTERNAL_SERVER_ERROR, $messages);
                        }
                        return ApiHelper::createErrorResponse(
                            ApiHelper::FORBIDDEN, ["Insufficient rights"]);
                    }
                    return ApiHelper::createErrorResponse(
                        ApiHelper::NOT_FOUND, ["No find bound node in database"]);
                }
                return ApiHelper::createErrorResponse(
                    ApiHelper::INTERNAL_SERVER_ERROR, ["No find address in database"]);
            } catch (Exception $exception) {
                return ApiHelper::createErrorResponse(
                    ApiHelper::INTERNAL_SERVER_ERROR, [$exception->getMessage()]);
            }
        });
    }
}