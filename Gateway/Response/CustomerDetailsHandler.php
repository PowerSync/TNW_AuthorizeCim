<?php
/**
 * Copyright © 2017 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace TNW\AuthorizeCim\Gateway\Response;

use Magento\Payment\Gateway\Response\HandlerInterface;
use TNW\AuthorizeCim\Gateway\Helper\SubjectReader;

/**
 * Class CustomerDetailsHandler - handles customer profile details
 */
class CustomerDetailsHandler implements HandlerInterface
{
    /**
     * @var string
     */
    const CARD_NUMBER = 'cc_number';

    /**
     * @var string
     */
    const CARD_LAST4 = 'cc_last4';

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * CardDetailsHandler constructor.
     * @param SubjectReader $subjectReader
     */
    public function __construct(
        SubjectReader $subjectReader
    ) {
        $this->subjectReader = $subjectReader;
    }

    /**
     * @param array $subject
     * @param array $response
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function handle(array $subject, array $response)
    {
        /** @var \net\authorize\api\contract\v1\CreateTransactionResponse $transaction */
        $transaction = $this->subjectReader->readTransaction($response);
        $paymentObject = $this->subjectReader->readPayment($subject);
        if (method_exists($transaction, 'getCustomerProfileId')) {
            $paymentObject->getPayment()->setAdditionalInformation('profile_id', $transaction->getCustomerProfileId());
        } elseif ($transaction->getProfile()) {
            $paymentObject->getPayment()->setAdditionalInformation(
                'profile_id',
                $transaction->getProfile()->getCustomerProfileId()
            );
        }
    }
}
