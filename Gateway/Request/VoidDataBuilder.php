<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace TNW\AuthorizeCim\Gateway\Request;

use TNW\AuthorizeCim\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;

class VoidDataBuilder implements BuilderInterface
{
    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * VoidDataBuilder constructor.
     * @param SubjectReader $subjectReader
     */
    public function __construct(
        SubjectReader $subjectReader
    ) {
        $this->subjectReader = $subjectReader;
    }

    /**
     * @inheritdoc
     */
    public function build(array $subject)
    {
        $paymentDO = $this->subjectReader->readPayment($subject);

        /** @var \Magento\Sales\Model\Order\Payment $payment */
        $payment = $paymentDO->getPayment();

        return [
            'transaction_request' => [
                'ref_trans_id' => $payment->getParentTransactionId() ?: $payment->getLastTransId()
            ]
        ];
    }
}
