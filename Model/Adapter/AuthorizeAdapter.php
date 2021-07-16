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
use net\authorize\api\contract\v1\UpdateCustomerProfileRequest;
use net\authorize\api\controller\UpdateCustomerProfileController;
use net\authorize\api\controller\CreateTransactionController;
use net\authorize\api\controller\CreateCustomerProfileFromTransactionController;
use net\authorize\api\controller\CreateCustomerProfileController;
use TNW\AuthorizeCim\Gateway\Helper\DataObject;
use net\authorize\api\contract\v1\CreateCustomerPaymentProfileRequest;
use net\authorize\api\controller\CreateCustomerPaymentProfileController;
use net\authorize\api\controller\CreateCustomerShippingAddressController;
use net\authorize\api\contract\v1\CreateCustomerShippingAddressRequest;
use net\authorize\api\contract\v1\GetCustomerProfileRequest;
use net\authorize\api\controller\GetCustomerProfileController;
use net\authorize\api\contract\v1\AnetApiResponseType;

/**
 * Class AuthorizeAdapter - uses as bridge for authorizenet-sdk
 */
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
     * @param DataObject $dataObjectHelper
     */
    public function __construct(
        $apiLoginId,
        $transactionKey,
        $sandboxMode,
        DataObject $dataObjectHelper
    ) {
        $this->apiLoginId = $apiLoginId;
        $this->transactionKey = $transactionKey;
        $this->sandboxMode = $sandboxMode;
        $this->dataObjectHelper = $dataObjectHelper;
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
     * @return AnetApiResponseType
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
     * @return AnetApiResponseType
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
     * @return AnetApiResponseType
     */
    public function updateCustomerProfile(array $attributes)
    {
        $transactionRequest = new UpdateCustomerProfileRequest();

        // Filling the object
        $this->dataObjectHelper->populateWithArray($transactionRequest, array_merge($attributes, [
            'merchant_authentication' => [
                'name' => $this->apiLoginId,
                'transaction_key' => $this->transactionKey
            ]
        ]));

        $controller = new UpdateCustomerProfileController($transactionRequest);
        return $controller->executeWithApiResponse($this->endPoint());
    }

    /**
     * @param array $attributes
     * @return AnetApiResponseType
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

    /**
     * @param array $attributes
     * @return AnetApiResponseType
     */
    public function createCustomerPaymentProfile(array $attributes)
    {
        $transactionRequest = new CreateCustomerPaymentProfileRequest();

        // Filling the object
        $this->dataObjectHelper->populateWithArray($transactionRequest, array_merge($attributes, [
            'merchant_authentication' => [
                'name' => $this->apiLoginId,
                'transaction_key' => $this->transactionKey
            ]
        ]));

        $controller = new CreateCustomerPaymentProfileController($transactionRequest);
        return $controller->executeWithApiResponse($this->endPoint());
    }

    /**
     * @param array $attributes
     * @return AnetApiResponseType
     */
    public function createCustomerShippingProfile(array $attributes)
    {
        $transactionRequest = new CreateCustomerShippingAddressRequest();

        // Filling the object
        $this->dataObjectHelper->populateWithArray($transactionRequest, array_merge($attributes, [
            'merchant_authentication' => [
                'name' => $this->apiLoginId,
                'transaction_key' => $this->transactionKey
            ]
        ]));

        $controller = new CreateCustomerShippingAddressController($transactionRequest);
        return $controller->executeWithApiResponse($this->endPoint());
    }

    /**
     * @param array $attributes
     * @return AnetApiResponseType
     */
    public function getCustomerProfile(array $attributes)
    {
        $transactionRequest = new GetCustomerProfileRequest();

        // Filling the object
        $this->dataObjectHelper->populateWithArray($transactionRequest, array_merge($attributes, [
            'merchant_authentication' => [
                'name' => $this->apiLoginId,
                'transaction_key' => $this->transactionKey
            ]
        ]));

        $controller = new GetCustomerProfileController($transactionRequest);
        return $controller->executeWithApiResponse($this->endPoint());
    }
}
