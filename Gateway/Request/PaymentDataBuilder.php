<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace TNW\AuthorizeCim\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;
use TNW\AuthorizeCim\Gateway\Helper\SubjectReader;
use TNW\AuthorizeCim\Helper\Payment\Formatter;

/**
 * Class for build request payment data
 *
 * @package TNW\AuthorizeCim\Gateway\Request
 */
class PaymentDataBuilder implements BuilderInterface
{
    use Formatter;

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
        $order = $paymentDO->getOrder();

        return [
            'transaction_request' => [
                'amount' => $this->formatPrice($this->subjectReader->readAmount($subject)),
                'currency_code' => $order->getCurrencyCode(),
                'po_number' => $order->getOrderIncrementId(),
            ]
        ];
    }
}
