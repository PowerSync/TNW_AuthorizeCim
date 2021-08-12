<?php

namespace TNW\AuthorizeCim\Model;

use TNW\AuthorizeCim\Gateway\Helper\SubjectReader;
use Psr\Log\LoggerInterface;
use Exception;

class PaymentProfileAddressManagement
{
    const ADDRESS_FIELDS = [
        'firstname',
        'lastname',
        'company',
        'street',
        'city',
        'region',
        'postcode',
        'country_id',
        'telephone'
    ];

    /**
     * @var \TNW\AuthorizeCim\Model\PaymentProfileAddressFactory
     */
    private $paymentProfileFactory;

    /**
     * @var \TNW\AuthorizeCim\Model\PaymentProfileAddressRepository
     */
    private $paymentProfileRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @param \TNW\AuthorizeCim\Model\PaymentProfileAddressFactory $paymentProfileFactory
     * @param \TNW\AuthorizeCim\Model\PaymentProfileAddressRepository $paymentProfileRepository
     * @param LoggerInterface $logger
     * @param SubjectReader $subjectReader
     */
    public function __construct(
        PaymentProfileAddressFactory $paymentProfileFactory,
        PaymentProfileAddressRepository $paymentProfileRepository,
        LoggerInterface $logger,
        SubjectReader $subjectReader
    ) {
        $this->paymentProfileFactory = $paymentProfileFactory;
        $this->paymentProfileRepository = $paymentProfileRepository;
        $this->logger = $logger;
        $this->subjectReader = $subjectReader;
    }

    /**
     * @param array $subject
     */
    public function paymentProfileAddressSave(array $subject)
    {
        try {
            $paymentObject = $this->subjectReader->readPayment($subject);
            $customerProfileId = $paymentObject->getPayment()->getAdditionalInformation('profile_id');
            $paymentProfileId = $paymentObject->getPayment()->getAdditionalInformation('payment_profile_id');
            $payment = $paymentObject->getPayment();
            $address = [];
            if ($payment->getOrder()) {
                $address = $paymentObject->getPayment()->getOrder()->getBillingAddress()->getData();
            } elseif ($payment->getQuote()) {
                $address = $paymentObject->getPayment()->getQuote()->getBillingAddress()->getData();
            }
            $paymentProfile = $this->paymentProfileFactory->create();
            $paymentProfile->setAddress($address);
            $paymentProfile->setGatewayToken(sprintf('%s/%s', $customerProfileId, $paymentProfileId));
            $this->paymentProfileRepository->save($paymentProfile);
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage());
        }
    }

    /**
     * @param array $subject
     */
    public function paymentProfileAddressUpdate(array $subject)
    {
        try {
            $paymentObject = $this->subjectReader->readPayment($subject);
            $customerProfileId = $paymentObject->getPayment()->getAdditionalInformation('profile_id');
            $paymentProfileId = $paymentObject->getPayment()->getAdditionalInformation('payment_profile_id');
            $payment = $paymentObject->getPayment();
            $address = [];
            if ($payment->getOrder()) {
                $address = $paymentObject->getPayment()->getOrder()->getBillingAddress()->getData();
            } elseif ($payment->getQuote()) {
                $address = $paymentObject->getPayment()->getQuote()->getBillingAddress()->getData();
            }
            $gatewayToken = sprintf('%s/%s', $customerProfileId, $paymentProfileId);
            $paymentProfile = $this->paymentProfileRepository->getByGatewayToken($gatewayToken);
            if (!$paymentProfile->getId()) {
                $paymentProfile = $this->paymentProfileFactory->create();
                $paymentProfile->setGatewayToken(sprintf('%s/%s', $customerProfileId, $paymentProfileId));
            }
            $paymentProfile->setAddress($address);
            $this->paymentProfileRepository->save($paymentProfile);
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage());
        }
    }

    /**
     * @param array $address1
     * @param array $address2
     * @return bool
     */
    public function addressCompare(array $address1, array $address2)
    {
        return (bool) array_diff(
            $this->populateAddress($address1),
            $this->populateAddress($address2)
        );
    }

    /**
     * @param array $address
     * @return array
     */
    public function populateAddress(array $address)
    {
        $result = [];
        foreach (self::ADDRESS_FIELDS as $addressField) {
            $result[$addressField] = $address[$addressField] ?? '';
        }
        return $result;
    }
}
