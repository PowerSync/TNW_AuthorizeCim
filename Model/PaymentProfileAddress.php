<?php
/**
 * Copyright © 2021 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
namespace TNW\AuthorizeCim\Model;

use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Sales\Model\AbstractModel;
use Magento\Framework\Serialize\SerializerInterface;
use TNW\AuthorizeCim\Api\Data\PaymentProfileAddressInterface;
use TNW\AuthorizeCim\Model\ResourceModel\PaymentProfileAddress as PaymentProfileAddressResource;

/**
 * Class PaymentProfileAddress - payment profile address model
 */
class PaymentProfileAddress extends AbstractModel implements PaymentProfileAddressInterface
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param SerializerInterface $serializer
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        SerializerInterface $serializer,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->serializer = $serializer;
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function _construct()
    {
        $this->_init(PaymentProfileAddressResource::class);
    }

    /**
     * {@inheritDoc}
     */
    public function getAddress()
    {
        return $this->serializer->unserialize($this->getData(self::ADDRESS) ?? '{}');
    }

    /**
     * {@inheritDoc}
     */
    public function setAddress($address)
    {
        return $this->setData(self::ADDRESS, $this->serializer->serialize($address));
    }

    /**
     * {@inheritDoc}
     */
    public function getGatewayToken()
    {
        return $this->getData(self::GATEWAY_TOKEN);
    }

    /**
     * {@inheritDoc}
     */
    public function setGatewayToken($token)
    {
        return $this->setData(self::GATEWAY_TOKEN, $token);
    }
}
