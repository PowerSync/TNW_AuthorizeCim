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
 * Profile Data Builder
 */
class ProfileTransactionDataBuilder implements BuilderInterface
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
     * Build payment data
     *
     * @param array $subject
     * @return array
     */
    public function build(array $subject)
    {
        $paymentDO = $this->subjectReader->readPayment($subject);

        /** @var \Magento\Sales\Model\Order\Payment $payment */
        $payment = $paymentDO->getPayment();

        return [
            'transaction_request' => [
                'profile' => [
                    'customer_profile_id' => $payment->getAdditionalInformation('profile_id'),
                    'payment_profile' => [
                        'payment_profile_id' => $payment->getAdditionalInformation('payment_profile_id')
                    ],
                    'shipping_profile_id' => $payment->getAdditionalInformation('shipping_profile_id'),
                ]
            ]
        ];
    }
}
