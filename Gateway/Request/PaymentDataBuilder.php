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
use Magento\Framework\App\Config\ScopeConfigInterface;

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
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param SubjectReader $subjectReader
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        SubjectReader $subjectReader,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig
    )
    {
        $this->subjectReader = $subjectReader;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
    }


    /**
     * Build payment data
     *
     * @param array $subject
     * @return array|array[]
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function build(array $subject)
    {
        $paymentDO = $this->subjectReader->readPayment($subject);
        $order = $paymentDO->getOrder();
        $storeUrl = $this->storeManager->getStore($paymentDO->getOrder()->getStoreId())->getBaseUrl();
        $result = [
            'transaction_request' => [
                'currency_code' => $order->getCurrencyCode(),
                'amount' => $this->formatPrice($this->subjectReader->readAmount($subject)),
                'order' => [
                    'description' => $storeUrl . ' Order: #' . $paymentDO->getOrder()->getOrderIncrementId(),
                ],
            ],
        ];

        $invoiceNumberConfigValue = $this->scopeConfig->getValue(
            'payment/tnw_authorize_cim/order_invoice_number',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $poNumberConfigValue = $this->scopeConfig->getValue(
            'payment/tnw_authorize_cim/order_po_number',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        if ($invoiceNumberConfigValue == 1) {
            $result['transaction_request']['order']['invoice_number'] = $order->getOrderIncrementId();
        }
        if ($poNumberConfigValue == 1) {
            $result['transaction_request']['po_number'] = $order->getOrderIncrementId();
        }

        return $result;
    }
}
