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

    /**
     * @var CommandPoolInterface
     */
    private $commandPool;

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    private $logger;

    public function __construct(
        CommandPoolInterface $commandPool,
        SubjectReader $subjectReader,
        LoggerInterface $logger
    ) {
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
        if ($customerId) {

        }

        try {
            $this->commandPool->get(self::CUSTOMER_CREATE)->execute($commandSubject);
        } catch (\Exception $e) {
            //there is already customer with provided email.
            $this->logger->error($e->getMessage());
        }

        try {
            $this->commandPool->get(self::CUSTOMER_PAYMENT_CREATE)->execute($commandSubject);
        } catch (\Exception $e) {
            //there is already customer with provided email.
            $this->logger->error($e->getMessage());
        }

        try {
            $this->commandPool->get(self::CUSTOMER_SHIPPING_CREATE)->execute($commandSubject);
        } catch (\Exception $e) {
            //there is already customer with provided email.
            $this->logger->error($e->getMessage());
        }



        $this->commandPool->get(self::AUTHORIZE_CUSTOMER)->execute($commandSubject);

        if ($payment->getAdditionalInformation('is_active_payment_token_enabler')
            && true
        ) {
            try {
               // $this->commandPool->get(self::CUSTOMER)->execute($commandSubject);
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
            }
        }
    }
}
