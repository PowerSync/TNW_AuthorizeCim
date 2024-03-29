<?php
/**
 * Copyright © 2017 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace TNW\AuthorizeCim\Gateway\Response;

use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order\Payment;
use TNW\AuthorizeCim\Gateway\Helper\SubjectReader;

/**
 * Class TransactionIdHandler - handles transaction response
 */
class TransactionIdHandler implements HandlerInterface
{
    const RESPONSE_DECLINED_TRANSACTION = 4;

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * TransactionIdHandler constructor.
     * @param SubjectReader $subjectReader
     */
    public function __construct(
        SubjectReader $subjectReader
    ) {
        $this->subjectReader = $subjectReader;
    }

    /**
     * @inheritdoc
     */
    public function handle(array $handlingSubject, array $response)
    {
        $paymentDO = $this->subjectReader->readPayment($handlingSubject);
        $orderPayment = $paymentDO->getPayment();
        /** @var \net\authorize\api\contract\v1\CreateTransactionResponse $transaction */
        $transaction = $this->subjectReader->readTransaction($response);

        if ($orderPayment instanceof Payment) {
            $transaction = $transaction->getTransactionResponse();

            $this->setTransactionId(
                $orderPayment,
                $transaction
            );

            $orderPayment->setIsTransactionClosed($this->shouldCloseTransaction());
            $closed = $this->shouldCloseParentTransaction($orderPayment);
            $orderPayment->setShouldCloseParentTransaction($closed);

            if ($transaction->getResponseCode() == self::RESPONSE_DECLINED_TRANSACTION) {
                $orderPayment->setIsTransactionPending(true);
                $orderPayment->setIsFraudDetected(true);
                $orderPayment->setTransactionAdditionalInfo('is_transaction_fraud', true);
            }
        }
    }

    /**
     * @inheritdoc
     */
    protected function setTransactionId(Payment $orderPayment, $transaction)
    {
        if (!$orderPayment->getTransactionId()
            || strpos($orderPayment->getTransactionId(), $transaction->getTransId()) === false
        ) {
            $orderPayment->setTransactionId($transaction->getTransId());
        }
    }

    /**
     * @inheritdoc
     */
    protected function shouldCloseTransaction()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    protected function shouldCloseParentTransaction(Payment $orderPayment)
    {
        return false;
    }
}
