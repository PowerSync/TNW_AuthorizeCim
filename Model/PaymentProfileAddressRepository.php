<?php

namespace TNW\AuthorizeCim\Model;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\NoSuchEntityException;
use TNW\AuthorizeCim\Api\Data\PaymentProfileAddressInterface;
use TNW\AuthorizeCim\Api\PaymentProfileAddressRepositoryInterface;
use TNW\AuthorizeCim\Model\ResourceModel\PaymentProfileAddress as PaymentProfileAddressResource;
use TNW\AuthorizeCim\Model\ResourceModel\PaymentProfileAddress\CollectionFactory;

class PaymentProfileAddressRepository implements PaymentProfileAddressRepositoryInterface
{
    /**
     * @var PaymentProfileAddressResource
     */
    private $resource;

    /**
     * @var \TNW\AuthorizeCim\Model\PaymentProfileAddressFactory
     */
    private $paymentProfileFactory;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * PaymentProfileRepository constructor.
     * @param PaymentProfileAddressResource $resource
     * @param \TNW\AuthorizeCim\Model\PaymentProfileAddressFactory $paymentProfileFactory
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        PaymentProfileAddressResource $resource,
        PaymentProfileAddressFactory $paymentProfileFactory,
        CollectionFactory $collectionFactory
    ) {
        $this->resource = $resource;
        $this->paymentProfileFactory = $paymentProfileFactory;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @param int $id
     * @return \TNW\AuthorizeCim\Model\PaymentProfileAddress
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
     * @return \Magento\Framework\DataObject
     */
    public function getByGatewayToken($token)
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(PaymentProfileAddressInterface::GATEWAY_TOKEN, $token);
        $collection->setPageSize(1);
        return $collection->getFirstItem();
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
