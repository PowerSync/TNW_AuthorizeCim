<?php
/**
 * Copyright © 2017 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace TNW\AuthorizeCim\Gateway\Response;

use Magento\Sales\Model\Order\Payment;

class VoidHandler extends TransactionIdHandler
{
    /**
     * @param Payment $orderPayment
     * @param $transaction
     */
    protected function setTransactionId(Payment $orderPayment, $transaction)
    {
        // @codingStandardsIgnoreLine
        return;
    }

    /**
     * @return bool
     */
    protected function shouldCloseTransaction()
    {
        return true;
    }

    /**
     * @param Payment $orderPayment
     * @return bool
     */
    protected function shouldCloseParentTransaction(Payment $orderPayment)
    {
        return true;
    }
}
