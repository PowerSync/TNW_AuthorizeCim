<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace TNW\AuthorizeCim\Gateway\Http\Client;

class TransactionVoid extends AbstractTransaction
{
    /**
     * @inheritdoc
     */
    protected function process(array $data)
    {
        $storeId = $data['store_id'] ?? null;
        // sending store id and other additional keys are restricted by Authorize API
        unset($data['store_id']);

        return $this->adapterFactory->create($storeId)
            ->transaction(array_merge_recursive($data, [
                'transaction_request' => ['transaction_type' => 'voidTransaction'],
            ]));
    }
}
