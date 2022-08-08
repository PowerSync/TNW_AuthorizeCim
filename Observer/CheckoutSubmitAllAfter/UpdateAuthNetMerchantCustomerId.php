<?php
/**
 * Copyright © TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */

namespace TNW\AuthorizeCim\Observer\CheckoutSubmitAllAfter;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;
use TNW\AuthorizeCim\Gateway\Config\Config;
use TNW\AuthorizeCim\Gateway\Http\Client\UpdateCustomerProfile;
use TNW\AuthorizeCim\Gateway\Http\TransferFactory;

/**
 * Updates merchant_customer_id in Authorize.net customer profile for customers, created after payment placement.
 */
class UpdateAuthNetMerchantCustomerId implements ObserverInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var UpdateCustomerProfile
     */
    private $updateCustomerProfileClient;

    /**
     * @var TransferFactory
     */
    private $transferFactory;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * UpdateAuthNetMerchantCustomerId constructor
     *
     * @param Config $config
     * @param UpdateCustomerProfile $updateCustomerProfileClient
     * @param TransferFactory $transferFactory
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        Config $config,
        UpdateCustomerProfile $updateCustomerProfileClient,
        TransferFactory $transferFactory,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->config = $config;
        $this->updateCustomerProfileClient = $updateCustomerProfileClient;
        $this->transferFactory = $transferFactory;
        $this->customerRepository = $customerRepository;
    }

    /**
     * @param Observer $observer
     * @return void
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\State\InputMismatchException
     */
    public function execute(Observer $observer)
    {
        if (!$this->config->isCIMEnabled()) {
            return;
        }
        $quote = $observer->getData('quote');
        if (!$quote instanceof Quote) {
            return;
        }
        $order = $observer->getData('order');
        if (!$order instanceof Order || !$order->getEntityId() || !$order->getCustomerId()) {
            return;
        }

        $payment = $order->getPayment();
        if (!$payment || $payment->getMethod() !== 'tnw_authorize_cim' ||
            !$payment->getAdditionalInformation('profile_id')) {
            return;
        }

        $customer = $this->customerRepository->getById($order->getCustomerId());
        if (!$customer->getCustomAttribute('customer_profile_id') ||
            !$customer->getCustomAttribute('customer_profile_id')->getValue()) {
            $customer->setCustomAttribute('customer_profile_id', $payment->getAdditionalInformation('profile_id'));
            $this->customerRepository->save($customer);
            $this->updateAuthNetMerchantCustomerId($customer);
        }
    }

    /**
     * Updates merchant_customer_id on Authorize.net side
     *
     * @param CustomerInterface $customer
     * @return void
     * @throws \Magento\Payment\Gateway\Http\ClientException
     */
    private function updateAuthNetMerchantCustomerId(CustomerInterface $customer)
    {
        $transferObject = $this->transferFactory->create([
            'profile' => [
                'customer_profile_id' => $customer->getCustomAttribute('customer_profile_id')->getValue(),
                'email' => $customer->getEmail(),
                'merchant_customer_id' => $customer->getId()
            ]
        ]);
        $this->updateCustomerProfileClient->placeRequest($transferObject);
    }
}
