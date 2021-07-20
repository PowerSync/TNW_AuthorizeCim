<?php
/**
 * Copyright © 2017 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace TNW\AuthorizeCim\Gateway\Command;

use TNW\AuthorizeCim\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Gateway\Helper\ContextHelper;
use Psr\Log\LoggerInterface;

/**
 * Class VaultCommand - vault commands strategy command
 */
class VaultCommand implements CommandInterface
{
    const AUTHORIZE = 'vault_authorize';

    const CUSTOMER_PAYMENT_UPDATE = 'customer_payment_profile_update';

    const CUSTOMER_PAYMENT_GET = 'customer_payment_profile_get';

    const CUSTOMER_SHIPPING_CREATE = 'customer_shipping_profile_create';

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
     * @var string
     */
    private $commandName;

    /**
     * VaultCommand constructor.
     * @param CommandPoolInterface $commandPool
     * @param SubjectReader $subjectReader
     * @param LoggerInterface $logger
     * @param string $commandName
     */
    public function __construct(
        CommandPoolInterface $commandPool,
        SubjectReader $subjectReader,
        LoggerInterface $logger,
        $commandName = 'authorize_customer'
    ) {
        $this->commandName = $commandName;
        $this->commandPool = $commandPool;
        $this->subjectReader = $subjectReader;
        $this->logger = $logger;
    }

    /**
     * @param array $commandSubject
     * @return \Magento\Payment\Gateway\Command\ResultInterface|void|null
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NotFoundException
     * @throws \Magento\Payment\Gateway\Command\CommandException
     */
    public function execute(array $commandSubject)
    {
        /** @var \Magento\Payment\Gateway\Data\PaymentDataObjectInterface $paymentDO */
        $paymentDO = $this->subjectReader->readPayment($commandSubject);
        $payment = $paymentDO->getPayment();
        ContextHelper::assertOrderPayment($payment);
        $order = $paymentDO->getOrder();
        $shippingAddress = $order->getShippingAddress();
        /** @var \Magento\Sales\Model\Order\Payment $payment */
        $extensionAttributes = $payment->getExtensionAttributes();
        $paymentToken = $extensionAttributes->getVaultPaymentToken();
        list($profileId, $paymentProfileId)
            = explode('/', $paymentToken->getGatewayToken(), 2);
        $payment->setAdditionalInformation('profile_id', $profileId);
        $payment->setAdditionalInformation('payment_profile_id', $paymentProfileId);

        $this->commandPool->get(self::CUSTOMER_PAYMENT_GET)->execute($commandSubject);
        //TODO: handle if no profile in on auth net

        if ($shippingAddress) {
            $this->commandPool->get(self::CUSTOMER_SHIPPING_CREATE)->execute($commandSubject);
        }

        $this->commandPool->get($this->commandName)->execute($commandSubject);
    }
}
