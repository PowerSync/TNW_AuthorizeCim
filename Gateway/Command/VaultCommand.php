<?php
/**
 * Copyright © 2017 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace TNW\AuthorizeCim\Gateway\Command;

use Magento\Framework\App\Config\ScopeConfigInterface as ScopeConfig;
use Magento\Framework\Exception\NoSuchEntityException;
use TNW\AuthorizeCim\Gateway\Helper\SubjectReader;
use TNW\AuthorizeCim\Model\PaymentProfileAddressManagement;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Gateway\Helper\ContextHelper;
use Psr\Log\LoggerInterface;
use TNW\AuthorizeCim\Model\PaymentProfileAddressRepository;

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
     * @var ScopeConfig
     */
    private $scopeConfig;

    /**
     * @var PaymentProfileAddressManagement
     */
    private $paymentProfileAddressManagement;

    /**
     * @var PaymentProfileAddressRepository
     */
    private $paymentProfileAddressRepository;

    /**
     * VaultCommand constructor.
     * @param CommandPoolInterface $commandPool
     * @param SubjectReader $subjectReader
     * @param LoggerInterface $logger
     * @param ScopeConfig $scopeConfig
     * @param PaymentProfileAddressManagement $paymentProfileAddressManagement
     * @param PaymentProfileAddressRepository $paymentProfileAddressRepository
     * @param string $commandName
     */
    public function __construct(
        CommandPoolInterface $commandPool,
        SubjectReader $subjectReader,
        LoggerInterface $logger,
        ScopeConfig $scopeConfig,
        PaymentProfileAddressManagement $paymentProfileAddressManagement,
        PaymentProfileAddressRepository $paymentProfileAddressRepository,
        $commandName = 'authorize_customer'
    ) {
        $this->commandName = $commandName;
        $this->commandPool = $commandPool;
        $this->subjectReader = $subjectReader;
        $this->logger = $logger;
        $this->scopeConfig = $scopeConfig;
        $this->paymentProfileAddressManagement = $paymentProfileAddressManagement;
        $this->paymentProfileAddressRepository = $paymentProfileAddressRepository;
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

        $orderAddress = $order->getBillingAddress()->getData();
        try {
            $paymentProfileAddress = $this->paymentProfileAddressRepository->getByGatewayToken(
                $paymentToken->getGatewayToken()
            );
            $isAddressesDifferent = $this->paymentProfileAddressManagement->isAddressesNotEqual(
                $orderAddress,
                $paymentProfileAddress->getAddress()
            );
        } catch (NoSuchEntityException $exception) {
            $isAddressesDifferent = true;
        }

        if ($isAddressesDifferent) {
            $this->commandPool->get(self::CUSTOMER_PAYMENT_UPDATE)->execute($commandSubject);
        }

        if ($shippingAddress
            && (bool) $this->scopeConfig->getValue('payment/tnw_authorize_cim/shipping_profile')
        ) {
            $this->commandPool->get(self::CUSTOMER_SHIPPING_CREATE)->execute($commandSubject);
        }

        $this->commandPool->get($this->commandName)->execute($commandSubject);
    }
}
