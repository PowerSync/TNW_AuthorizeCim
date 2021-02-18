<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace TNW\AuthorizeCim\Observer;

use Magento\Framework\Event\Observer;
use Magento\Payment\Observer\AbstractDataAssignObserver;

/**
 * Observer for set additional data
 *
 * @package TNW\AuthorizeCim\Observer
 */
class DataAssignObserver extends AbstractDataAssignObserver
{
    /**
     * Additional information key
     *
     * @var string
     */
    const KEY_ADDITIONAL_DATA = 'additional_data';

    /**
     * Set additional information from additional data
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $data = $this->readDataArgument($observer);

        $additionalData = $data->getData(self::KEY_ADDITIONAL_DATA);

        if (is_array($additionalData)) {
            $paymentInfo = $this->readPaymentModelArgument($observer);

            foreach ($additionalData as $key => $value) {
                $paymentInfo->setAdditionalInformation($key, $value);
            }
        }
    }
}
