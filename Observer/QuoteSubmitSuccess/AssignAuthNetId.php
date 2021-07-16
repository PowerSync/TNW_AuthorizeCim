<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */

namespace TNW\AuthorizeCim\Observer\QuoteSubmitSuccess;

use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Event\Observer;

/**
 * Class AssignAuthNetId - used to set the new auth net Profile Id to customer
 */
class AssignAuthNetId implements ObserverInterface
{
    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * AssignAuthNetId constructor.
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->customerRepository = $customerRepository;
    }

    /**
     * @param Observer $observer
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\State\InputMismatchException
     */
    public function execute(Observer $observer)
    {
        $quote = $observer->getData('quote');
        if (!$quote instanceof \Magento\Quote\Model\Quote) {
            return;
        }
        $order = $observer->getData('order');
        if (!$order instanceof \Magento\Sales\Model\Order || !$order->getEntityId()) {
            return;
        }
        $authorizeNetProfileId = $order->getPayment()->getAdditionalInformation('profile_id');
        if (!$order->getCustomerIsGuest() && $authorizeNetProfileId) {
            $customer = $this->customerRepository->getById($order->getCustomerId());
            $customer->setCustomAttribute('customer_profile_id', $authorizeNetProfileId);
            $this->customerRepository->save($customer);
        }
    }
}
