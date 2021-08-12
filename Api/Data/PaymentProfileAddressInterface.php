<?php

namespace TNW\AuthorizeCim\Api\Data;

interface PaymentProfileAddressInterface
{
    const ENTITY_ID = 'entity_id';
    const ADDRESS = 'address';
    const GATEWAY_TOKEN = 'gateway_token';

    /**
     * @return mixed
     */
    public function getId();

    /**
     * @param $id
     * @return mixed
     */
    public function setId($id);

    /**
     * @return mixed
     */
    public function getAddress();

    /**
     * @param $address
     * @return mixed
     */
    public function setAddress($address);

    /**
     * @return mixed
     */
    public function getGatewayToken();

    /**
     * @param $token
     * @return mixed
     */
    public function setGatewayToken($token);
}
