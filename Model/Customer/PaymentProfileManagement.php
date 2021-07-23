<?php
/**
 * Copyright © 2021 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
namespace TNW\AuthorizeCim\Model\Customer;

use Exception;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Payment\Gateway\Command\CommandException;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectFactoryInterface;
use Magento\Payment\Model\InfoInterface;
use Magento\Vault\Api\Data\PaymentTokenFactoryInterface;
use Magento\Vault\Api\PaymentTokenManagementInterface;
use Magento\Vault\Api\PaymentTokenRepositoryInterface;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Psr\Log\LoggerInterface;
use TNW\AuthorizeCim\Gateway\Config\Config;
use TNW\AuthorizeCim\Model\Customer\PaymentProfileManagementInterface;
use TNW\AuthorizeCim\Model\Ui\ConfigProvider;
use Zend_Json;

/**
 * Class PaymentProfileManagement
 */
class PaymentProfileManagement implements PaymentProfileManagementInterface
{
    /**
     * @var PaymentTokenFactoryInterface
     */
    private $paymentTokenFactory;

    /**
     * @var PaymentTokenManagementInterface
     */
    private $paymentTokenManagement;

    /**
     * @var PaymentTokenRepositoryInterface
     */
    private $paymentTokenRepository;

    /**
     * @var PaymentDataObjectFactoryInterface
     */
    private $paymentDataObjectFactory;

    /**
     * @var CommandInterface
     */
    private $customerGetProfileCommand;

    /**
     * @var CommandInterface
     */
    private $customerCreateProfileCommand;

    /**
     * @var CommandInterface
     */
    private $customerPaymentCreateProfileCommand;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * PaymentProfileManagement constructor.
     * @param PaymentTokenFactoryInterface $paymentTokenFactory
     * @param PaymentTokenManagementInterface $paymentTokenManagement
     * @param PaymentTokenRepositoryInterface $paymentTokenRepository
     * @param PaymentDataObjectFactoryInterface $paymentDataObjectFactory
     * @param CommandInterface $customerGetProfileCommand
     * @param CommandInterface $customerCreateProfileCommand
     * @param CommandInterface $customerPaymentCreateProfileCommand
     * @param Config $config
     * @param LoggerInterface $logger
     * @param EncryptorInterface $encryptor
     */
    public function __construct(
        PaymentTokenFactoryInterface $paymentTokenFactory,
        PaymentTokenManagementInterface $paymentTokenManagement,
        PaymentTokenRepositoryInterface $paymentTokenRepository,
        PaymentDataObjectFactoryInterface $paymentDataObjectFactory,
        CommandInterface $customerGetProfileCommand,
        CommandInterface $customerCreateProfileCommand,
        CommandInterface $customerPaymentCreateProfileCommand,
        Config $config,
        LoggerInterface $logger,
        EncryptorInterface $encryptor
    ) {
        $this->paymentTokenFactory = $paymentTokenFactory;
        $this->paymentTokenManagement = $paymentTokenManagement;
        $this->paymentTokenRepository = $paymentTokenRepository;
        $this->paymentDataObjectFactory = $paymentDataObjectFactory;
        $this->customerGetProfileCommand = $customerGetProfileCommand;
        $this->customerCreateProfileCommand = $customerCreateProfileCommand;
        $this->customerPaymentCreateProfileCommand = $customerPaymentCreateProfileCommand;
        $this->config = $config;
        $this->logger = $logger;
        $this->encryptor = $encryptor;
    }

    /**
     * @inheritdoc
     */
    public function getByCustomerId(int $customerId)
    {
        $paymentProfiles = [];
        foreach ($this->paymentTokenManagement->getVisibleAvailableTokens($customerId) as $token) {
            if ($token->getPaymentMethodCode() === ConfigProvider::CODE) {
                $paymentProfiles[] = $token;
            }
        }
        return $paymentProfiles;
    }

    /**
     * @inheritdoc
     */
    public function save(InfoInterface $payment, CustomerInterface $customer, array $arguments = [])
    {
        $result = true;
        $commandSubject = $arguments;
        $commandSubject['customer'] = $customer;
        $commandSubject['payment'] = $this->paymentDataObjectFactory->create($payment);

        try {
            try {
                $this->customerGetProfileCommand->execute($commandSubject);
            } catch (Exception $e) {
                $this->logger->error($e->getMessage());
            }
            if (!$payment->getAdditionalInformation('profile_id')) {
                $this->customerCreateProfileCommand->execute($commandSubject);
            }
            $this->customerPaymentCreateProfileCommand->execute($commandSubject);
            $paymentToken = $this->getVaultPaymentToken($payment);
            if (null !== $paymentToken) {
                $paymentTokenStored = $this->paymentTokenManagement->getByPublicHash(
                    $paymentToken->getPublicHash(),
                    $payment->getQuote()->getCustomerId()
                );
                if ($paymentTokenStored
                    && ($paymentTokenStored->getIsActive() || $paymentTokenStored->getIsVisible())
                ) {
                    throw new LocalizedException(__('Payment token with provided data already existed'));
                } elseif ($paymentTokenStored) {
                    $paymentTokenStored->setIsActive(true)
                        ->setIsVisible(true);
                    $paymentToken = $paymentTokenStored;
                    $result = false;
                }
                $this->paymentTokenRepository->save($paymentToken);
            }
        } catch (LocalizedException $exception) {
            $this->logger->error($exception);
            throw new CouldNotSaveException(__(
                'Could not save the PaymentProfile: %1',
                $exception->getMessage()
            ));
        }
        return $result;
    }

    /**
     * @inheritdoc
     */
    public function delete(string $hash, int $customerId)
    {
        $paymentToken = $this->paymentTokenManagement->getByPublicHash($hash, $customerId);
        if (!$paymentToken) {
            throw new NoSuchEntityException(__('Payment Token does not exist.'));
        }
        return $this->paymentTokenRepository->delete($paymentToken);
    }

    /**
     * @param InfoInterface $payment
     * @return PaymentTokenInterface|null
     */
    private function getVaultPaymentToken(InfoInterface $payment)
    {
        $profileId = $payment->getAdditionalInformation('profile_id');
        $paymentProfileId = $payment->getAdditionalInformation('payment_profile_id');
        if (!$profileId || !$paymentProfileId) {
            return null;
        }

        $paymentToken = $this->paymentTokenFactory->create(PaymentTokenFactoryInterface::TOKEN_TYPE_CREDIT_CARD)
            ->setGatewayToken(sprintf('%s/%s', $profileId, $paymentProfileId))
            ->setCustomerId($payment->getQuote()->getCustomerId())
            ->setPaymentMethodCode($payment->getMethod())
            ->setIsActive(true)
            ->setIsVisible(true)
            ->setExpiresAt($this->getPaymentTokenExpirationDate($payment));

        $paymentToken->setTokenDetails($this->convertPaymentTokenDetailsToJSON([
            'type' => $payment->getAdditionalInformation('cc_type'),
            'maskedCC' => $payment->getAdditionalInformation('cc_last4'),
            'expirationDate' => sprintf(
                '%s/%s',
                $payment->getAdditionalInformation('cc_exp_month'),
                $payment->getAdditionalInformation('cc_exp_year')
            )
        ]));

        $paymentToken->setPublicHash($this->generatePaymentTokenPublicHash($paymentToken));
        return $paymentToken;
    }

    /**
     * @param PaymentTokenInterface $paymentToken
     * @return mixed
     */
    private function generatePaymentTokenPublicHash(PaymentTokenInterface $paymentToken)
    {
        $hashKey = $paymentToken->getGatewayToken();
        $hashKey .= $paymentToken->getPaymentMethodCode()
            . $paymentToken->getType()
            . $paymentToken->getTokenDetails();

        return $this->encryptor->getHash($hashKey);
    }

    /**
     * @param InfoInterface $payment
     * @return string
     */
    private function getPaymentTokenExpirationDate(InfoInterface $payment)
    {
        $time = sprintf(
            '%s-%s-01 00:00:00',
            trim($payment->getAdditionalInformation('cc_exp_year')),
            trim($payment->getAdditionalInformation('cc_exp_month'))
        );

        return date_create($time, timezone_open('UTC'))
            ->modify('+1 month')
            ->format('Y-m-d 00:00:00');
    }

    /**
     * @param array $details
     * @return string
     */
    private function convertPaymentTokenDetailsToJSON(array $details)
    {
        $json = Zend_Json::encode($details);
        return $json ? $json : '{}';
    }
}
