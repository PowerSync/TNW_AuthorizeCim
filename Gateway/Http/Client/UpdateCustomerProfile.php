<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace TNW\AuthorizeCim\Gateway\Http\Client;

class UpdateCustomerProfile extends AbstractTransaction
{
    /**
     * @inheritdoc
     */
    protected function process(array $data)
    {
        $storeId = $data['store_id'] ?? null;
        unset($data['store_id']);
        return $this->adapterFactory->create($storeId)
            ->updateCustomerProfile($data);
    }
}
