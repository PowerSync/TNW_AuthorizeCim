<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
namespace TNW\AuthorizeCim\Gateway\Http\Client;

/**
 * Transaction Void
 */
class HeldApprove extends AbstractTransaction
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
            ->heldTransaction(array_merge_recursive($data, [
                'held_transaction_request' => ['action' => 'approve'],
            ]));
    }
}
