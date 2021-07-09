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
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class for build request payment data
 */
class PaymentDataBuilder implements BuilderInterface
{
    use Formatter;

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param SubjectReader $subjectReader
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        SubjectReader $subjectReader,
        StoreManagerInterface $storeManager
    ) {
        $this->subjectReader = $subjectReader;
        $this->storeManager = $storeManager;
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
        $storeUrl = $this->storeManager->getStore($paymentDO->getOrder()->getStoreId())->getBaseUrl();

        return [
            'transaction_request' => [
                'amount' => $this->formatPrice($this->subjectReader->readAmount($subject)),
                'currency_code' => $order->getCurrencyCode(),
                'po_number' => $order->getOrderIncrementId(),
                'order' => [
                    'description' => $storeUrl . ' Order: #' . $paymentDO->getOrder()->getOrderIncrementId(),
                ],
            ],
        ];
    }
}
