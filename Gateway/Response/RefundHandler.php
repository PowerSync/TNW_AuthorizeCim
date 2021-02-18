<?php
/**
 * Copyright © 2017 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace TNW\AuthorizeCim\Gateway\Response;

use Magento\Sales\Model\Order\Payment;

/**
 * Refund Handler
 *
 * @package TNW\AuthorizeCim\Gateway\Response
 */
class RefundHandler extends VoidHandler
{
    /**
     * @inheritdoc
     */
    protected function shouldCloseParentTransaction(Payment $orderPayment)
    {
        return !(bool)$orderPayment->getCreditmemo()->getInvoice()->canRefund();
    }
}
