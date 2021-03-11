<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace TNW\AuthorizeCim\Helper\Payment;

/**
 * Trait Formatter
 */
trait Formatter
{
    /**
     * Format Price
     *
     * @param string|int|float $price
     * @return float
     */
    public function formatPrice($price)
    {
        return sprintf('%.2F', $price);
    }
}
