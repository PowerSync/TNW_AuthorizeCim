<?php

namespace TNW\AuthorizeCim\Api;

use TNW\AuthorizeCim\Api\Data\PaymentProfileAddressInterface;

interface PaymentProfileAddressRepositoryInterface
{
    /**
     * @param int $id
     * @return mixed
     */
    public function getById(int $id);

    /**
     * @param $gatewayToken
     * @return mixed
     */
    public function getByGatewayToken($gatewayToken);

    /**
     * @param PaymentProfileAddressInterface $paymentProfile
     * @return mixed
     */
    public function save(PaymentProfileAddressInterface $paymentProfile);

    /**
     * @param PaymentProfileAddressInterface $paymentProfile
     * @return mixed
     */
    public function delete(PaymentProfileAddressInterface $paymentProfile);

    /**
     * @param int $id
     * @return mixed
     */
    public function deleteById(int $id);
}
