<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace TNW\AuthorizeCim\Model\Adapter;

use net\authorize\api\constants\ANetEnvironment;
use net\authorize\api\contract\v1\CreateTransactionRequest;
use net\authorize\api\contract\v1\CreateCustomerProfileFromTransactionRequest;
use net\authorize\api\contract\v1\CreateCustomerProfileRequest;
use net\authorize\api\controller\CreateTransactionController;
use net\authorize\api\controller\CreateCustomerProfileFromTransactionController;
use net\authorize\api\controller\CreateCustomerProfileController;
use TNW\AuthorizeCim\Gateway\Helper\DataObject;

class AuthorizeAdapter
{
    /**
     * @var string
     */
    private $apiLoginId;

    /**
     * @var string
     */
    private $transactionKey;

    /**
     * @var bool
     */
    private $sandboxMode;

    /**
     * @var DataObject
     */
    private $dataObjectHelper;

    /**
     * AuthorizeAdapter constructor.
     * @param string $apiLoginId
     * @param string $transactionKey
     * @param bool $sandboxMode
     * @param string|null $sdkLogFile
     * @param DataObject $dataObjectHelper
     */
    public function __construct(
        $apiLoginId,
        $transactionKey,
        $sandboxMode,
        $sdkLogFile,
        DataObject $dataObjectHelper
    ) {
        $this->apiLoginId = $apiLoginId;
        $this->transactionKey = $transactionKey;
        $this->sandboxMode = $sandboxMode;
        $this->dataObjectHelper = $dataObjectHelper;

        $this->setupEnvOfAdaptiveClient($sdkLogFile);
    }

    /**
     * Setup after construct
     *
     * @param string|null $sdkLogFile
     */
    private function setupEnvOfAdaptiveClient(?string $sdkLogFile)
    {
        if ($sdkLogFile && !defined('AUTHORIZENET_LOG_FILE')) {
            define('AUTHORIZENET_LOG_FILE', $sdkLogFile);
        }
    }

    /**
     * @return string
     */
    private function endPoint()
    {
        return $this->sandboxMode
            ? ANetEnvironment::SANDBOX
            : ANetEnvironment::PRODUCTION;
    }

    /**
     * @param array $attributes
     * @return \net\authorize\api\contract\v1\CreateTransactionResponse
     */
    public function transaction(array $attributes)
    {
        $transactionRequest = new CreateTransactionRequest();

        // Filling the object
        $this->dataObjectHelper->populateWithArray($transactionRequest, array_merge($attributes, [
            'merchant_authentication' => [
                'name' => $this->apiLoginId,
                'transaction_key' => $this->transactionKey
            ]
        ]));

        $controller = new CreateTransactionController($transactionRequest);
        return $controller->executeWithApiResponse($this->endPoint());
    }

    /**
     * @param array $attributes
     * @return \net\authorize\api\contract\v1\CreateCustomerProfileResponse
     */
    public function createCustomerProfileFromTransaction(array $attributes)
    {
        $transactionRequest = new CreateCustomerProfileFromTransactionRequest();

        // Filling the object
        $this->dataObjectHelper->populateWithArray($transactionRequest, array_merge($attributes, [
            'merchant_authentication' => [
                'name' => $this->apiLoginId,
                'transaction_key' => $this->transactionKey
            ]
        ]));

        $controller = new CreateCustomerProfileFromTransactionController($transactionRequest);
        return $controller->executeWithApiResponse($this->endPoint());
    }

    /**
     * @param array $attributes
     * @return \net\authorize\api\contract\v1\CreateCustomerProfileResponse
     */
    public function createCustomerProfile(array $attributes)
    {
        $transactionRequest = new CreateCustomerProfileRequest();

        // Filling the object
        $this->dataObjectHelper->populateWithArray($transactionRequest, array_merge($attributes, [
            'merchant_authentication' => [
                'name' => $this->apiLoginId,
                'transaction_key' => $this->transactionKey
            ]
        ]));

        $controller = new CreateCustomerProfileController($transactionRequest);
        return $controller->executeWithApiResponse($this->endPoint());
    }
}
