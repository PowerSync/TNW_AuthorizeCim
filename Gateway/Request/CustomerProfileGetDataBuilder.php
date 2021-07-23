<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace TNW\AuthorizeCim\Gateway\Request;

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
     * @param SubjectReader $subjectReader
     */
    public function __construct(
        SubjectReader $subjectReader
    ) {
        $this->subjectReader = $subjectReader;
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

        if (!$email) {
            $paymentDO = $this->subjectReader->readPayment($subject);
            $order = $paymentDO->getOrder();
            $email = $order->getBillingAddress()->getEmail();
        }

        return [
            'email' => $email,
        ];
    }
}
