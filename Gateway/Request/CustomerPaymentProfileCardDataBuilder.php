<?php
/**
 * Copyright © 2021 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace TNW\AuthorizeCim\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;
use TNW\AuthorizeCim\Gateway\Helper\SubjectReader;
use Exception;

/**
 * Class CustomerPaymentProfileCardDataBuilder - builder for customer payment card
 */
class CustomerPaymentProfileCardDataBuilder implements BuilderInterface
{
    /**
     * @var SubjectReader
     */
    protected $subjectReader;

    /**
     * @var string
     */
    protected $parentRequestFieldId;

    /**
     * CustomerPaymentProfileIdDataBuilder constructor.
     * @param SubjectReader $subjectReader
     * @param string $parentRequestFieldId
     */
    public function __construct(
        SubjectReader $subjectReader,
        $parentRequestFieldId = 'paymentProfile'
    ) {
        $this->parentRequestFieldId = $parentRequestFieldId;
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
        $payment = $paymentDO->getPayment();
        try {
            $paymentProfileCard = [
                'card_number' => $payment->getAdditionalInformation('payment_profile_card_number'),
                'expiration_date' => $payment->getAdditionalInformation('payment_profile_card_expire')
            ];
        } catch (Exception $e) {
            return [];
        }

        $paymentProfileCardData['payment'] = ['credit_card' => $paymentProfileCard];
        if ($this->parentRequestFieldId) {
            return [$this->parentRequestFieldId => $paymentProfileCardData];
        }
        return $paymentProfileCardData;
    }
}
