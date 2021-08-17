<?php
/**
 * Copyright © 2021 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
namespace TNW\AuthorizeCim\Api\Data;

/**
 * Interface PaymentProfileAddressInterface - payment profile address interface
 */
interface PaymentProfileAddressInterface
{
    const ENTITY_ID = 'entity_id';
    const ADDRESS = 'address';
    const GATEWAY_TOKEN = 'gateway_token';

    /**
     * @return int
     */
    public function getId();

    /**
     * @param int $id
     * @return $this
     */
    public function setId(int $id);

    /**
     * @return array
     */
    public function getAddress();

    /**
     * @param array $address
     * @return $this
     */
    public function setAddress(array $address);

    /**
     * @return string
     */
    public function getGatewayToken();

    /**
     * @param string $token
     * @return $this
     */
    public function setGatewayToken(string $token);
}
