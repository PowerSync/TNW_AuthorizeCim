<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace TNW\AuthorizeCim\Gateway\Request;

use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Request\BuilderInterface;
use TNW\AuthorizeCim\Gateway\Helper\SubjectReader;

/**
 * Class for build request payment data
 *
 * @package TNW\AuthorizeCim\Gateway\Request
 */
class CaptureDataBuilder implements BuilderInterface
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
     * Build capture data
     *
     * @param array $subject
     * @return array
     * @throws LocalizedException
     */
    public function build(array $subject)
    {
        $paymentDO = $this->subjectReader->readPayment($subject);

        /** @var \Magento\Sales\Model\Order\Payment $payment */
        $payment = $paymentDO->getPayment();

        $transactionId = $payment->getCcTransId();
        if (!$transactionId) {
            throw new LocalizedException(__('No authorization transaction to proceed capture.'));
        }

        return [
            'transaction_request' => [
                'ref_trans_id' => $transactionId
            ]
        ];
    }
}
