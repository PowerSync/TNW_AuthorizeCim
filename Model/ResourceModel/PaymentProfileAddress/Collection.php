<?php

namespace TNW\AuthorizeCim\Model\ResourceModel\PaymentProfileAddress;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use TNW\AuthorizeCim\Model\PaymentProfileAddress;
use TNW\AuthorizeCim\Model\ResourceModel\PaymentProfileAddress as PaymentProfileAddressResource;

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
