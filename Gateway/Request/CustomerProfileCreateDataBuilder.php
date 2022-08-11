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
 * Class CustomerProfileCreateDataBuilder - build required data for customer create command
 */
class CustomerProfileCreateDataBuilder implements BuilderInterface
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
     * Build payment data
     *
     * @param array $subject
     * @return array
     */
    public function build(array $subject)
    {
        $customerDataObject = $this->subjectReader->readCustomerData($subject);
        $email = $customerDataObject->getEmail();
        $customerId = $customerDataObject->getCustomerId();

        if (!$customerId) {
            try {
                $customer = $this->customerRepository->get($email);
                $customerId = $customer->getId();
            } catch (NoSuchEntityException $e) {
                $customerId = $this->generateEmailHash($email);
            }
        }

        return [
            'profile' => [
                "merchant_customer_id" => $customerId,
                "email" => $email
            ]
        ];
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
