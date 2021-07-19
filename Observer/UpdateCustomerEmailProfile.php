<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace TNW\AuthorizeCim\Observer;

use Magento\Framework\Event\Observer;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use TNW\AuthorizeCim\Gateway\Command\ProfileUpdateCommand;

/**
 * Observer to update customer profile
 */
class UpdateCustomerEmailProfile extends AbstractDataAssignObserver
{
    /**
     * @var ProfileUpdateCommand
     */
    protected $profileUpdateCommand;

    /**
     * UpdateCustomerEmailProfile constructor.
     * @param ProfileUpdateCommand $profileUpdateCommand
     */
    public function __construct(
        ProfileUpdateCommand $profileUpdateCommand
    ) {
        $this->profileUpdateCommand = $profileUpdateCommand;
    }

    /**
     * Set additional information from additional data
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $customer = $observer->getCustomerDataObject();
        if ($customer && $customer->getId()) {
            $this->profileUpdateCommand->execute(['customer' => $customer]);
        }
    }
}
