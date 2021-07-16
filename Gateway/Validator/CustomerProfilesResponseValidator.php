<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace TNW\AuthorizeCim\Gateway\Validator;

use TNW\AuthorizeCim\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;
use net\authorize\api\contract\v1\ANetApiResponseType;
use net\authorize\api\contract\v1\MessagesType\MessageAType;

/**
 * Validate profile requests response data
 */
class CustomerProfilesResponseValidator extends AbstractValidator
{
    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var string
     */
    protected $validDataField = '';

    /**
     * CustomerProfilesResponseValidator constructor.
     * @param ResultInterfaceFactory $resultFactory
     * @param SubjectReader $subjectReader
     * @param string $validDataField
     */
    public function __construct(
        ResultInterfaceFactory $resultFactory,
        SubjectReader $subjectReader,
        $validDataField = ''
    ) {
        parent::__construct($resultFactory);
        $this->subjectReader = $subjectReader;
        $this->validDataField = $validDataField;
    }

    /**
     * @inheritdoc
     */
    public function validate(array $validationSubject)
    {
        /** @var \net\authorize\api\contract\v1\CreateTransactionResponse $response */
        $response = $this->subjectReader->readResponseObject($validationSubject);

        $isValid = true;
        $errorMessages = [];

        // @codingStandardsIgnoreStart
        foreach ($this->getResponseValidators() as $validator) {
            $validationResult = $validator($response);

            if (!$validationResult[0]) {
                $isValid = $validationResult[0];
                $errorMessages = array_merge($errorMessages, $validationResult[1]);
            }
        }
        if (!$isValid && $this->validDataField) {
            $methodName = 'get' . ucfirst($this->validDataField);
            if (method_exists($response, $methodName) && $response->$methodName()) {
                $isValid = true;
            }
        }

        return $this->createResult($isValid, $errorMessages);
    }

    /**
     * @return array
     */
    protected function getResponseValidators()
    {
        return [
            function (ANetApiResponseType $response) {
                $messages = $response->getMessages();

                if (strcasecmp($messages->getResultCode(), 'Ok')  !== 0) {
                    $errorMessages = array_map(function (MessageAType $message) {
                        return __('%1: %2', $message->getCode(), $message->getText());
                    }, $messages->getMessage());

                    return [false, $errorMessages];
                }

                return [true, []];
            }
        ];
    }
}
