<?php
/**
 * Copyright © 2021 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace TNW\AuthorizeCim\Block\Customer\CreditCard;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Magento\Payment\Model\CcConfigProvider;
use TNW\AuthorizeCim\Block\Customer\CardRenderer;
use TNW\AuthorizeCim\Model\PaymentProfileAddressRepository;
use Magento\Customer\Model\Address\Config;
use Exception;

/**
 * Class Renderer - render card
 */
class Renderer extends CardRenderer
{
    /**
     * @var PaymentProfileAddressRepository
     */
    private $paymentProfileRepository;

    /**
     * @var Config
     */
    private $addressConfig;

    /**
     * @param Template\Context $context
     * @param CcConfigProvider $iconsProvider
     * @param PaymentProfileAddressRepository $paymentProfileRepository
     * @param Config $addressConfig
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        CcConfigProvider $iconsProvider,
        PaymentProfileAddressRepository $paymentProfileRepository,
        Config $addressConfig,
        array $data = []
    ) {
        $this->paymentProfileRepository = $paymentProfileRepository;
        $this->addressConfig = $addressConfig;
        parent::__construct($context, $iconsProvider, $data);
    }

    /**
     * @return array
     */
    public function getAddress()
    {
        $token = $this->getToken();
        $gatewayToken = $token->getGatewayToken();
        try {
            $paymentProfileAddress = $this->paymentProfileRepository->getByGatewayToken($gatewayToken);
            return $paymentProfileAddress->getAddress();
        } catch (NoSuchEntityException $exception) {
            return [];
        }
    }

    /**
     * @param array $address
     * @return string
     */
    public function getFormattedCardAddress(array $address)
    {
        if (empty($address)) {
            return '';
        }
        try {
            $renderer = $this->addressConfig->getFormatByCode('html')->getRenderer();
            return $renderer->renderArray($address);
        } catch (Exception $exception) {
            return '';
        }
    }
}
