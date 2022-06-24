<?php
/**
 * Copyright © 2022 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace TNW\AuthorizeCim\Gateway\Command;

use Magento\Framework\App\Config\ScopeConfigInterface as ScopeConfig;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Payment\Gateway\Command\CommandException;
use Magento\Payment\Gateway\Command\ResultInterface;
use Magento\Sales\Model\Order\Payment;
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
     * @return ResultInterface|void|null
     * @throws LocalizedException
     * @throws NotFoundException
     * @throws CommandException
     */
    public function execute(array $commandSubject)
    {
        $paymentDO = $this->subjectReader->readPayment($commandSubject);
        /** @var Payment $payment */
        $payment = $paymentDO->getPayment();
        ContextHelper::assertOrderPayment($payment);
        $order = $paymentDO->getOrder();
        $shippingAddress = $order->getShippingAddress();
        if (
            ($profileId = $payment->getAdditionalInformation('profile_id'))
            && ($paymentProfileId = $payment->getAdditionalInformation('payment_profile_id'))
        ) {
            $gatewayToken = sprintf('%s/%s', $profileId, $paymentProfileId);
        } else {
            $extensionAttributes = $payment->getExtensionAttributes();
            $gatewayToken = $extensionAttributes->getVaultPaymentToken()->getGatewayToken();
            list($profileId, $paymentProfileId) = explode('/', $gatewayToken, 2);
            $payment->setAdditionalInformation('profile_id', $profileId);
            $payment->setAdditionalInformation('payment_profile_id', $paymentProfileId);
        }

        $this->commandPool->get(self::CUSTOMER_PAYMENT_GET)->execute($commandSubject);
        //TODO: handle if no profile in on auth net

        $orderAddress = $this->paymentProfileAddressManagement->getAddressFromObject($order->getBillingAddress());
        try {
            $paymentProfileAddress = $this->paymentProfileAddressRepository->getByGatewayToken($gatewayToken);
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
