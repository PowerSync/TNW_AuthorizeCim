<?php
/**
 * Copyright © 2021 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
namespace TNW\AuthorizeCim\Model;

use Magento\Framework\DataObject;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\NoSuchEntityException;
use TNW\AuthorizeCim\Api\Data\PaymentProfileAddressInterface;
use TNW\AuthorizeCim\Api\PaymentProfileAddressRepositoryInterface;
use TNW\AuthorizeCim\Model\ResourceModel\PaymentProfileAddress as PaymentProfileAddressResource;

/**
 * Class PaymentProfileAddressRepository - payment profile address repository model
 */
class PaymentProfileAddressRepository implements PaymentProfileAddressRepositoryInterface
{
    /**
     * @var PaymentProfileAddressResource
     */
    private $resource;

    /**
     * @var PaymentProfileAddressFactory
     */
    private $paymentProfileFactory;

    /**
     * PaymentProfileRepository constructor.
     * @param PaymentProfileAddressResource $resource
     * @param PaymentProfileAddressFactory $paymentProfileFactory
     */
    public function __construct(
        PaymentProfileAddressResource $resource,
        PaymentProfileAddressFactory $paymentProfileFactory
    ) {
        $this->resource = $resource;
        $this->paymentProfileFactory = $paymentProfileFactory;
    }

    /**
     * @param int $id
     * @return PaymentProfileAddress
     * @throws NoSuchEntityException
     */
    public function getById(int $id)
    {
        $paymentProfile = $this->paymentProfileFactory->create();
        $this->resource->load($paymentProfile, $id);
        if (!$paymentProfile->getId()) {
            throw new NoSuchEntityException(
                __('Payment profile with the "%1" ID doesn\'t exist.', $id)
            );
        }
        return $paymentProfile;
    }

    /**
     * @param $token
     * @return DataObject|PaymentProfileAddressInterface|PaymentProfileAddress
     * @throws NoSuchEntityException
     */
    public function getByGatewayToken($token)
    {
        $paymentProfile = $this->paymentProfileFactory->create();
        $this->resource->load($paymentProfile, $token, PaymentProfileAddressInterface::GATEWAY_TOKEN);
        if (!$paymentProfile->getId()) {
            throw new NoSuchEntityException(
                __('Payment profile with the "%1" gateway token doesn\'t exist.', $token)
            );
        }
        return $paymentProfile;
    }

    /**
     * @param PaymentProfileAddressInterface $paymentProfile
     * @return PaymentProfileAddressInterface
     * @throws CouldNotSaveException
     */
    public function save(PaymentProfileAddressInterface $paymentProfile)
    {
        try {
            $this->resource->save($paymentProfile);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(
                __('Could not save payment profile: %1', $exception->getMessage()),
                $exception
            );
        }
        return $paymentProfile;
    }

    /**
     * @param PaymentProfileAddressInterface $paymentProfile
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(PaymentProfileAddressInterface $paymentProfile)
    {
        try {
            $this->resource->delete($paymentProfile);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(
                __('Could not delete payment profile: %1', $exception->getMessage())
            );
        }
        return true;
    }

    /**
     * @param int $id
     * @return bool
     * @throws NoSuchEntityException|CouldNotDeleteException
     */
    public function deleteById(int $id)
    {
        return $this->delete($this->getById($id));
    }
}
