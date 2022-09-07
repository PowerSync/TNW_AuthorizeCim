<?php
/**
 * Copyright © 2021 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
namespace TNW\AuthorizeCim\Block\Adminhtml;

use Magento\Backend\Block\Template\Context;
use Magento\Customer\Controller\RegistryConstants;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Magento\Ui\Component\Layout\Tabs\TabWrapper;
use TNW\AuthorizeCim\Gateway\Config\Config;

/**
 * Class CustomerPaymentInfoTab
 */
class CustomerPaymentInfoTab extends TabWrapper
{
    /**
     * Core registry
     *
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @var bool
     */
    protected $isAjaxLoaded = true;

    /**
     * @var Config
     */
    private $config;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Registry $registry
     * @param Config $config
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Config $config,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        $this->config = $config;
        parent::__construct($context, $data);
    }

    /**
     * @inheritdoc
     */
    public function canShowTab()
    {
        return $this->coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID)
            && $this->config->isCIMEnabled();
    }

    /**
     * @inheritdoc
     */
    public function getTabLabel()
    {
        return __('Payment Profiles');
    }

    /**
     * @inheritdoc
     */
    public function getTabUrl()
    {
        return $this->getUrl('customer/paymentProfiles/index', ['_current' => true]);
    }
}
