<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace TNW\AuthorizeCim\Gateway\Request;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Payment\Gateway\Request\BuilderInterface;
use TNW\AuthorizeCim\Gateway\Helper\SubjectReader;

/**
 * Class CustomerProfileGetDataBuilder - builds required data to get profile
 */
class CustomerProfileGetDataBuilder implements BuilderInterface
{
    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @param SubjectReader $subjectReader
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        SubjectReader $subjectReader,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->subjectReader = $subjectReader;
        $this->customerRepository = $customerRepository;
    }

    /**
     * Build customer data
     *
     * @param array $subject
     * @return array
     */
    public function build(array $subject)
    {
        $customerDataObject = $this->subjectReader->readCustomerData($subject);
        $email = $customerDataObject->getEmail();
        //$customerId = $customerDataObject->getCustomerId();

        if (!$email) {
            $paymentDO = $this->subjectReader->readPayment($subject);
            $order = $paymentDO->getOrder();
            $email = $order->getBillingAddress()->getEmail();
        }

        try {
            $customer = $this->customerRepository->get($email);
            $customerProfileIdAttr = $customer->getCustomAttribute('customer_profile_id');
            if (!$customerProfileIdAttr || !$customerProfileIdAttr->getValue()) {
                $customerId = $this->generateEmailHash($email);
            } else {
                $customerProfileId = $customerProfileIdAttr->getValue();
            }
        } catch (NoSuchEntityException $e) {
            $customerId = $this->generateEmailHash($email);
        }

        if (!empty($customerProfileId)) {
            return [
                'customer_profile_id' => $customerProfileId
            ];
        } else {
            return [
                'email' => $email,
                'merchant_customer_id' => $customerId
            ];
        }
    }

    /**
     * Generates email hash for use as merchant customer id for guest customers
     *
     * @param string $email
     * @return false|string
     */
    private function generateEmailHash(string $email)
    {
        return hash('crc32', $email);
    }
}
