<?php
/**
 * Copyright © 2017 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace TNW\AuthorizeCim\Gateway\Command;

use TNW\AuthorizeCim\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Command;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Gateway\Helper\ContextHelper;
use Psr\Log\LoggerInterface;
use TNW\AuthorizeCim\Gateway\Config\Config;
use Magento\Framework\App\Config\ScopeConfigInterface as ScopeConfig;

/**
 * Class AuthorizeStrategyCommand - authorize strategy command
 */
class AuthorizeStrategyCommand implements CommandInterface
{
    /**
     * Stripe authorize command
     */
    const AUTHORIZE = 'authorize';

    /**
     * Stripe customer command
     */
    const CUSTOMER = 'customer';

    /**
     * Stripe customer command
     */
    const CUSTOMER_CREATE = 'customer_create';

    const CUSTOMER_PAYMENT_CREATE = 'customer_payment_profile_create';

    const CUSTOMER_SHIPPING_CREATE = 'customer_shipping_profile_create';

    const AUTHORIZE_CUSTOMER = 'authorize_customer';

    const CUSTOMER_GET = 'customer_get';

    const CUSTOMER_UPDATE = 'customer_update';

    /**
     * @var CommandPoolInterface
     */
    private $commandPool;

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var LoggerInterface
     */
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
     * AuthorizeStrategyCommand constructor.
     * @param CommandPoolInterface $commandPool
     * @param SubjectReader $subjectReader
     * @param LoggerInterface $logger
     * @param Config $config
     * @param ScopeConfig $scopeConfig
     */
    public function __construct(
        CommandPoolInterface $commandPool,
        SubjectReader $subjectReader,
        LoggerInterface $logger,
        Config $config,
        ScopeConfig $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->config = $config;
        $this->commandPool = $commandPool;
        $this->subjectReader = $subjectReader;
        $this->logger = $logger;
    }

    /**
     * @param array $commandSubject
     * @return Command\ResultInterface|null|void
     * @throws Command\CommandException
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute(array $commandSubject)
    {

        /** @var \Magento\Payment\Gateway\Data\PaymentDataObjectInterface $paymentDO */
        $paymentDO = $this->subjectReader->readPayment($commandSubject);
        $payment = $paymentDO->getPayment();
        ContextHelper::assertOrderPayment($payment);
        $order = $paymentDO->getOrder();
        $shippingAddress = $order->getShippingAddress();
        $customerId = $order->getCustomerId();
        if ($this->config->isCIMEnabled()) {
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
            $this->commandPool->get(self::AUTHORIZE_CUSTOMER)->execute($commandSubject);
        } else {
            $this->commandPool->get(self::AUTHORIZE)->execute($commandSubject);
        }
    }
}
