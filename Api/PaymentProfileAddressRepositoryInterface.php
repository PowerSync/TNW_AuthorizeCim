<?php
/**
 * Copyright © 2021 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
namespace TNW\AuthorizeCim\Api;

use TNW\AuthorizeCim\Api\Data\PaymentProfileAddressInterface;

/**
 * Interface PaymentProfileAddressRepositoryInterface - payment profile address repository interface
 */
interface PaymentProfileAddressRepositoryInterface
{
    /**
     * @param int $id
     * @return PaymentProfileAddressInterface
     */
    public function getById(int $id);

    /**
     * @param $gatewayToken
     * @return PaymentProfileAddressInterface
     */
    public function getByGatewayToken($gatewayToken);

    /**
     * @param PaymentProfileAddressInterface $paymentProfile
     * @return PaymentProfileAddressInterface
     */
    public function save(PaymentProfileAddressInterface $paymentProfile);

    /**
     * @param PaymentProfileAddressInterface $paymentProfile
     * @return bool
     */
    public function delete(PaymentProfileAddressInterface $paymentProfile);

    /**
     * @param int $id
     * @return bool
     */
    public function deleteById(int $id);
}
