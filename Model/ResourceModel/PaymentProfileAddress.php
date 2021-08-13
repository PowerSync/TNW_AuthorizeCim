<?php
/**
 * Copyright © 2021 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
namespace TNW\AuthorizeCim\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class PaymentProfileAddress - resource model
 */
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
