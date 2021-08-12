<?php
/**
 * Copyright © 2021 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace TNW\AuthorizeCim\Block\Adminhtml\Customer\CreditCard;

use Magento\Framework\View\Element\Template;
use Magento\Payment\Model\CcConfigProvider;
use TNW\AuthorizeCim\Block\Customer\CardRenderer;
use TNW\AuthorizeCim\Model\PaymentProfileAddressRepository;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Model\Address\Config;

/**
 * Class Renderer - render card
 */
class Renderer extends CardRenderer
{
    private $paymentProfileRepository;

//    private $addressFactory;

    private $addressConfig;

    public function __construct(
        Template\Context $context,
        CcConfigProvider $iconsProvider,
        PaymentProfileAddressRepository $paymentProfileRepository,
//        AddressInterfaceFactory $addressFactory,
        Config $addressConfig,
        array $data = []
    ) {
        $this->paymentProfileRepository = $paymentProfileRepository;
//        $this->addressFactory = $addressFactory;
        $this->addressConfig = $addressConfig;
        parent::__construct($context, $iconsProvider, $data);
    }

    public function getAddress()
    {
        $token = $this->getToken();
        $gatewayToken = $token->getGatewayToken();
        $paymentProfileAddress = $this->paymentProfileRepository->getByGatewayToken($gatewayToken);
//        return $this->convertAddressFromJSON($paymentProfileAddress->getAddress());
        return $paymentProfileAddress->getAddress();
    }

    public function getFormattedCardAddress($address)
    {
        if (empty($address)) {
            return '';
        }
        try {
            $renderer = $this->addressConfig->getFormatByCode('html')->getRenderer();
            return $renderer->renderArray($address);
        } catch (\Exception $exception) {
            return '';
        }
    }

//    private function convertAddressFromJSON($address)
//    {
//        try {
//            return \Zend_Json::decode($address);
//        } catch (\Exception $exception) {
//            return [];
//        }
//    }
}
