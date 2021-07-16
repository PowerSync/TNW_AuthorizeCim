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
 * Class for build request payment data
 */
class OpaqueDataBuilder implements BuilderInterface
{
    /**
     * @var SubjectReader
     */
    protected $subjectReader;

    /**
     * @var string
     */
    protected $requestedDataBlockName;

    /**
     * OpaqueDataBuilder constructor.
     * @param SubjectReader $subjectReader
     * @param string $requestedDataBlockName
     */
    public function __construct(
        SubjectReader $subjectReader,
        $requestedDataBlockName = 'transaction_request'
    ) {
        $this->requestedDataBlockName = $requestedDataBlockName;
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

        return [
            $this->requestedDataBlockName => ['payment' => [
                'opaque_data' => [
                    'data_descriptor' => $payment->getAdditionalInformation('opaqueDescriptor'),
                    'data_value' => $payment->getAdditionalInformation('opaqueValue')
                ]
            ]]
        ];
    }
}
