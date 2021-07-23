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
 * Class CustomerPaymentProfileIdDataBuilder - builder for customer payment id
 */
class CustomerPaymentProfileIdDataBuilder implements BuilderInterface
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
        $parentRequestFieldId = ''
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
            $paymentProfileId =  $payment->getAdditionalInformation('payment_profile_id');
        } catch (\Exception $e) {
            $extensionAttributes = $payment->getExtensionAttributes();
            $paymentToken = $extensionAttributes->getVaultPaymentToken();
            list($profileId, $paymentProfileId)
                = explode('/', $paymentToken->getGatewayToken(), 2);
        }
        $profileIdData = [
            'customer_payment_profile_id' => $paymentProfileId
        ];
        if ($this->parentRequestFieldId) {
            return [
                $this->parentRequestFieldId => $profileIdData
            ];
        }
        return $profileIdData;
    }
}
