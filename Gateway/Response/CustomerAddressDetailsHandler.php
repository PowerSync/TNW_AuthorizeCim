<?php
/**
 * Copyright © 2017 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace TNW\AuthorizeCim\Gateway\Response;

use Magento\Payment\Gateway\Helper\ContextHelper;
use Magento\Payment\Gateway\Response\HandlerInterface;
use TNW\AuthorizeCim\Gateway\Helper\SubjectReader;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use TNW\AuthorizeCim\Model\PaymentProfileAddressManagement;

/**
 * Class CustomerAddressDetailsHandler - handles address/payment responses
 */
class CustomerAddressDetailsHandler implements HandlerInterface
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
     * @var PaymentProfileAddressManagement
     */
    private $paymentProfileAddressManagement;

    /**
     * CardDetailsHandler constructor.
     * @param SubjectReader $subjectReader
     * @param PaymentProfileAddressManagement $paymentProfileAddressManagement
     */
    public function __construct(
        SubjectReader $subjectReader,
        PaymentProfileAddressManagement $paymentProfileAddressManagement
    ) {
        $this->subjectReader = $subjectReader;
        $this->paymentProfileAddressManagement = $paymentProfileAddressManagement;
    }

    /**
     * @param array $subject
     * @param array $response
     */
    public function handle(array $subject, array $response)
    {
        $transaction = $this->subjectReader->readTransaction($response);
        $paymentObject = $this->subjectReader->readPayment($subject);
        if (method_exists($transaction, 'getCustomerProfileId')
            && $transaction->getCustomerProfileId()
        ) {
            $paymentObject->getPayment()->setAdditionalInformation('profile_id', $transaction->getCustomerProfileId());
        }
        if (method_exists($transaction, 'getCustomerPaymentProfileId')
            && $transaction->getCustomerPaymentProfileId()
        ) {
            $paymentObject->getPayment()->setAdditionalInformation(
                'payment_profile_id',
                $transaction->getCustomerPaymentProfileId()
            );
            $this->paymentProfileAddressManagement->paymentProfileAddressSave($subject);
        } elseif (method_exists($transaction, 'getPaymentProfile')
            && method_exists($transaction->getPaymentProfile(), 'getPayment')
            && method_exists($transaction->getPaymentProfile()->getPayment(), 'getCreditCard')
        ) {
            $creditCard = $transaction->getPaymentProfile()->getPayment()->getCreditCard();
            $paymentObject->getPayment()->setAdditionalInformation(
                'payment_profile_card_number',
                $creditCard->getCardNumber()
            );
            $paymentObject->getPayment()->setAdditionalInformation(
                'payment_profile_card_expire',
                $creditCard->getExpirationDate()
            );
        } else {
            $test1 = 1;
            $this->paymentProfileAddressManagement->paymentProfileAddressUpdate($subject);
        }
        if (method_exists($transaction, 'getCustomerAddressId')
            && $transaction->getCustomerAddressId()
        ) {
            $paymentObject->getPayment()->setAdditionalInformation(
                'shipping_profile_id',
                $transaction->getCustomerAddressId()
            );
        }
    }
}
