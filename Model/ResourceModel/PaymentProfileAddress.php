<?php

namespace TNW\AuthorizeCim\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class PaymentProfileAddress extends AbstractDb
{
    /**
     * {@inheritDoc}
     */
    protected function _construct()
    {
        $this->_init('tnw_authorizenet_payment_profile', 'entity_id');
    }
}
