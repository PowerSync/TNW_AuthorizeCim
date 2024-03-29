<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace TNW\AuthorizeCim\Gateway\Validator;

use net\authorize\api\contract\v1\CreateTransactionResponse;
use net\authorize\api\contract\v1\TransactionResponseType\MessagesAType\MessageAType;
use net\authorize\api\contract\v1\TransactionResponseType\ErrorsAType\ErrorAType;
use TNW\AuthorizeCim\Gateway\Response\TransactionIdHandler;

/**
 * Validate response data
 */
class TransactionResponseValidator extends GeneralResponseValidator
{
    /**
     * @inheritdoc
     */
    protected function getResponseValidators()
    {
        return array_merge(parent::getResponseValidators(), [
            function (CreateTransactionResponse $response) {
                $transactionResponse = $response->getTransactionResponse();
                $result = [true, []];
                if (!$transactionResponse) {
                    return $result;
                }

                if ($transactionResponse->getResponseCode() == TransactionIdHandler::RESPONSE_DECLINED_TRANSACTION) {
                    return $result;
                }

                $messages = $transactionResponse->getMessages();
                $errorMessages = [];
                if ($messages) {
                    $errorMessages = array_map([$this, 'map'], array_filter($messages, [$this, 'filter']));
                }
                if ($errorMessages || $transactionResponse->getErrors()) {
                    $messages = $transactionResponse->getErrors() ? : $transactionResponse->getMessages();
                    $errorMessages = array_map([$this, 'errorMap'], array_filter($messages, [$this, 'errorFilter']));
                } else {
                    return $result;
                }

                return [
                    !count($errorMessages),
                    $errorMessages
                ];
            }
        ]);
    }

    /**
     * @param MessageAType $message
     * @return bool
     */
    private function filter(MessageAType $message)
    {
        return $message->getCode() != 1;
    }

    /**
     * @param MessageAType $message
     * @return string
     */
    private function map(MessageAType $message)
    {
        return __($message->getDescription());
    }

    /**
     * @param ErrorAType|MessageAType $message
     * @return bool
     */
    private function errorFilter($message)
    {
        $errorCode = method_exists($message, 'getErrorCode')
            ? $message->getErrorCode()
            : $message->getCode();
        return  $errorCode != 1;
    }

    /**
     * @param ErrorAType|MessageAType $message
     * @return \Magento\Framework\Phrase
     */
    private function errorMap($message)
    {
        $messageText = method_exists($message, 'getErrorText')
            ? $message->getErrorText()
            : $message->getDescription();
        return __($messageText);
    }
}
