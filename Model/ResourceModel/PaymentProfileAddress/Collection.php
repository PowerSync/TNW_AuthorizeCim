<?php
/**
 * Copyright © 2021 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
namespace TNW\AuthorizeCim\Model\ResourceModel\PaymentProfileAddress;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use TNW\AuthorizeCim\Model\PaymentProfileAddress;
use TNW\AuthorizeCim\Model\ResourceModel\PaymentProfileAddress as PaymentProfileAddressResource;

/**
 * Class Collection - payment profile address collection
 */
class Collection extends AbstractCollection
{
    /**
     * {@inheritDoc}
     */
    protected function _construct()
    {
        $this->_init(PaymentProfileAddress::class, PaymentProfileAddressResource::class);
    }
}
