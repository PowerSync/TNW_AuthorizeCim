<?php

namespace TNW\AuthorizeCim\Model;

use Magento\Framework\Api\AttributeValueFactory;
use Magento\Sales\Model\AbstractModel;
use Magento\Framework\Serialize\SerializerInterface;
use TNW\AuthorizeCim\Api\Data\PaymentProfileAddressInterface;
use TNW\AuthorizeCim\Model\ResourceModel\PaymentProfileAddress as PaymentProfileAddressResource;

class PaymentProfileAddress extends AbstractModel implements PaymentProfileAddressInterface
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param SerializerInterface $serializer
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        SerializerInterface $serializer,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
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
