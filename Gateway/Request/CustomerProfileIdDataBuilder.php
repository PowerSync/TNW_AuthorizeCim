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
 * Class CustomerProfileIdDataBuilder - builds customer profile id data
 */
class CustomerProfileIdDataBuilder implements BuilderInterface
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
     * CustomerProfileIdDataBuilder constructor.
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

        $profileIdData = [
            'customer_profile_id' => $payment->getAdditionalInformation('profile_id')
        ];
        if ($this->parentRequestFieldId) {
            return [
                $this->parentRequestFieldId => $profileIdData
            ];
        }
        return $profileIdData;
    }
}
