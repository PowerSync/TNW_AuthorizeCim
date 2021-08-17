<?php
/**
 * Copyright © 2021 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
namespace TNW\AuthorizeCim\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Payment\Model\InfoInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Psr\Log\LoggerInterface;
use Exception;

/**
 * Class PaymentProfileAddressManagement - payment profile address manager model
 */
class PaymentProfileAddressManagement
{
    const ADDRESS_FIELDS = [
        AddressInterface::FIRSTNAME,
        AddressInterface::LASTNAME,
        AddressInterface::COMPANY,
        AddressInterface::STREET,
        AddressInterface::CITY,
        AddressInterface::REGION,
        AddressInterface::POSTCODE,
        AddressInterface::COUNTRY_ID,
        AddressInterface::TELEPHONE
    ];

    /**
     * @var PaymentProfileAddressFactory
     */
    private $paymentProfileFactory;

    /**
     * @var PaymentProfileAddressRepository
     */
    private $paymentProfileRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param PaymentProfileAddressFactory $paymentProfileFactory
     * @param PaymentProfileAddressRepository $paymentProfileRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        PaymentProfileAddressFactory $paymentProfileFactory,
        PaymentProfileAddressRepository $paymentProfileRepository,
        LoggerInterface $logger
    ) {
        $this->paymentProfileFactory = $paymentProfileFactory;
        $this->paymentProfileRepository = $paymentProfileRepository;
        $this->logger = $logger;
    }

    /**
     * @param InfoInterface $payment
     */
    public function paymentProfileAddressSave(InfoInterface $payment)
    {
        try {
            $gatewayToken = $this->getGatewayToken($payment);
            try {
                $paymentProfile = $this->paymentProfileRepository->getByGatewayToken($gatewayToken);
            } catch (NoSuchEntityException $exception) {
                $paymentProfile = $this->paymentProfileFactory->create();
                $paymentProfile->setGatewayToken($gatewayToken);
            }
            $paymentProfile->setAddress($this->getAddress($payment));
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
    public function isAddressesNotEqual(array $address1, array $address2)
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
    private function populateAddress(array $address)
    {
        $result = [];
        foreach (self::ADDRESS_FIELDS as $addressField) {
            $result[$addressField] = $address[$addressField] ?? '';
        }
        return $result;
    }

    /**
     * @param InfoInterface $payment
     * @return string
     */
    private function getGatewayToken(InfoInterface $payment)
    {
        $customerProfileId = $payment->getAdditionalInformation('profile_id');
        $paymentProfileId = $payment->getAdditionalInformation('payment_profile_id');
        return sprintf('%s/%s', $customerProfileId, $paymentProfileId);
    }

    /**
     * @param InfoInterface $payment
     * @return array|mixed
     */
    private function getAddress(InfoInterface $payment)
    {
        $address = [];
        if ($payment->getOrder()) {
            $address = $payment->getOrder()->getBillingAddress()->getData();
        } elseif ($payment->getQuote()) {
            $address = $payment->getQuote()->getBillingAddress()->getData();
        }
        return $address;
    }

    /**
     * @param object $address
     * @return array
     */
    public function getAddressFromObject(object $address)
    {
        return [
            AddressInterface::FIRSTNAME => $address->getFirstname(),
            AddressInterface::LASTNAME => $address->getLastname(),
            AddressInterface::COMPANY => $address->getCompany(),
            AddressInterface::STREET => $address->getStreetLine1(),
            AddressInterface::CITY => $address->getCity(),
            AddressInterface::REGION => $address->getRegionCode(),
            AddressInterface::POSTCODE => $address->getPostcode(),
            AddressInterface::COUNTRY_ID => $address->getCountryId(),
            AddressInterface::TELEPHONE => $address->getTelephone()
        ];
    }
}
