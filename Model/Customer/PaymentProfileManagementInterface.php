<?php
/**
 * Copyright © 2021 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
namespace TNW\AuthorizeCim\Model\Customer;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Payment\Model\InfoInterface;
use Magento\Vault\Api\Data\PaymentTokenInterface;

/**
 * Interface PaymentProfileManagementInterface
 */
interface PaymentProfileManagementInterface
{
    /**
     * @param int $customerId
     * @return PaymentTokenInterface[]
     */
    public function getByCustomerId(int $customerId);

    /**
     * @param InfoInterface $payment
     * @param CustomerInterface $customer
     * @param array $arguments
     * @return bool
     * @throws CouldNotSaveException
     */
    public function save(InfoInterface $payment, CustomerInterface $customer, array $arguments = []);

    /**
     * @param string $hash
     * @param int $customerId
     * @return bool
     * @throws NoSuchEntityException
     */
    public function delete(string $hash, int $customerId);
}
