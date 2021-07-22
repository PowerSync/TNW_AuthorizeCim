<?php
/**
 * Copyright © 2017 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
namespace TNW\AuthorizeCim\Model\Config\Backend;

use Magento\Framework\App\Config\Value;

/**
 * Class Vault - used to resolve dependency on cim_active setting for vault active status
 */
class Vault extends Value
{
    /**
     * @return Value|void
     */
    public function beforeSave()
    {
        if (!$this->_data['fieldset_data']['cim_active']) {
            $this->_dataSaveAllowed = true;
            $this->setValue(0);
        }
    }
}
