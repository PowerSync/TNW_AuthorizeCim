<?php
/**
 * Copyright © 2017 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
namespace TNW\AuthorizeCim\Model\InstantPurchase;

use TNW\AuthorizeCim\Gateway\Config\Config;
use Magento\InstantPurchase\PaymentMethodIntegration\AvailabilityCheckerInterface;

/**
 * Class AvailabilityChecker - Check availability of AuthorizeCim vaulted cards for Instant Purchase
 */
class AvailabilityChecker implements AvailabilityCheckerInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * AvailabilityChecker constructor.
     *
     * @param Config $config
     */
    public function __construct(
        Config $config
    ) {
        $this->config = $config;
    }

    /**
     * @inheritdoc
     */
    public function isAvailable(): bool
    {
        if (!$this->config->isActive()) {
            return false;
        }

        return true;
    }
}
