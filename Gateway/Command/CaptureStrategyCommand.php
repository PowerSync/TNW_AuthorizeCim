<?php
/**
 * Copyright © 2017 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace TNW\AuthorizeCim\Gateway\Command;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Gateway\Helper\ContextHelper;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Api\Data\TransactionInterface;
use Magento\Sales\Api\TransactionRepositoryInterface;
use TNW\AuthorizeCim\Gateway\Helper\SubjectReader;
use Psr\Log\LoggerInterface;
use TNW\AuthorizeCim\Gateway\Config\Config;
use Magento\Framework\App\Config\ScopeConfigInterface as ScopeConfig;

/**
 * Class CaptureStrategyCommand - capture/sale strategy command
 */
class CaptureStrategyCommand implements CommandInterface
{
    /** @var string */
    const SALE = 'sale';

    /** @var string */
    const CAPTURE = 'settlement';

    /** @var string */
    const VAULT_SALE = 'vault_sale';

    /** @var string */
    const CUSTOMER = 'customer';

    /**
     * Stripe customer command
     */
    const CUSTOMER_CREATE = 'customer_create';

    const CUSTOMER_PAYMENT_CREATE = 'customer_payment_profile_create';

    const CUSTOMER_SHIPPING_CREATE = 'customer_shipping_profile_create';

    const SALE_CUSTOMER = 'sale_customer';

    const CUSTOMER_GET = 'customer_get';

    const CUSTOMER_UPDATE = 'customer_update';

    /** @var SearchCriteriaBuilder */
    private $searchCriteriaBuilder;

    /** @var TransactionRepositoryInterface */
    private $transactionRepository;

    /** @var SubjectReader */
    private $subjectReader;

    /** @var CommandPoolInterface */
    private $commandPool;

    /** @var \Psr\Log\LoggerInterface */
    private $logger;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var ScopeConfig
     */
    private $scopeConfig;

    /**
     * CaptureStrategyCommand constructor.
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param TransactionRepositoryInterface $transactionRepository
     * @param SubjectReader $subjectReader
     * @param CommandPoolInterface $commandPool
     * @param LoggerInterface $logger
     * @param Config $config
     * @param ScopeConfig $scopeConfig
     */
    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        TransactionRepositoryInterface $transactionRepository,
        SubjectReader $subjectReader,
        CommandPoolInterface $commandPool,
        LoggerInterface $logger,
        Config $config,
        ScopeConfig $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->transactionRepository = $transactionRepository;
        $this->subjectReader = $subjectReader;
        $this->commandPool = $commandPool;
        $this->logger = $logger;
        $this->config = $config;
    }

    /**
     * @param array $commandSubject
     * @return void
     * @throws \Magento\Framework\Exception\NotFoundException
     * @throws \Magento\Payment\Gateway\Command\CommandException
     */
    public function execute(array $commandSubject)
    {
        $paymentDataObject = $this->subjectReader->readPayment($commandSubject);

        /** @var \Magento\Sales\Model\Order\Payment $paymentInfo */
        $paymentInfo = $paymentDataObject->getPayment();
        ContextHelper::assertOrderPayment($paymentInfo);

        /** @var \Magento\Payment\Gateway\Data\PaymentDataObjectInterface $paymentDO */
        $paymentDO = $this->subjectReader->readPayment($commandSubject);
        $payment = $paymentDO->getPayment();
        ContextHelper::assertOrderPayment($payment);
        $order = $paymentDO->getOrder();
        $shippingAddress = $order->getShippingAddress();
        $customerId = $order->getCustomerId();
        $command = $this->getCommand($paymentInfo);
        if ($command == self::SALE_CUSTOMER) {
            try {
                $this->commandPool->get(self::CUSTOMER_GET)->execute($commandSubject);
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
            }
            if ($customerId && $payment->getAdditionalInformation('profile_id')) {
                $this->commandPool->get(self::CUSTOMER_UPDATE)->execute($commandSubject);
            }
            if (!$payment->getAdditionalInformation('profile_id')) {
                $this->commandPool->get(self::CUSTOMER_CREATE)->execute($commandSubject);
            }
            $this->commandPool->get(self::CUSTOMER_PAYMENT_CREATE)->execute($commandSubject);
            if ($shippingAddress
                && (bool) $this->scopeConfig->getValue('payment/tnw_authorize_cim/shipping_profile')
            ) {
                $this->commandPool->get(self::CUSTOMER_SHIPPING_CREATE)->execute($commandSubject);
            }
        }

        $this->commandPool->get($command)->execute($commandSubject);
    }

    /**
     * @param OrderPaymentInterface $payment
     * @return string
     */
    private function getCommand(OrderPaymentInterface $payment): string
    {
        $existsCapture = $this->isExistsCaptureTransaction($payment);
        if (!$payment->getAuthorizationTransaction() && !$existsCapture) {
            if ($this->config->isCIMEnabled()) {
                return self::SALE_CUSTOMER;
            }
            return self::SALE;
        }

        if (!$existsCapture) {
            return self::CAPTURE;
        }

        return self::VAULT_SALE;
    }

    /**
     * @param OrderPaymentInterface $payment
     * @return bool
     */
    private function isExistsCaptureTransaction(OrderPaymentInterface $payment): bool
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('payment_id', $payment->getId())
            ->addFilter('txn_type', TransactionInterface::TYPE_CAPTURE)
            ->create();

        return (boolean)$this->transactionRepository
            ->getList($searchCriteria)
            ->getTotalCount();
    }
}
