<?php
/**
 * Copyright © 2021 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
namespace TNW\AuthorizeCim\Controller\Adminhtml\PaymentProfiles;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Customer\Controller\RegistryConstants;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\LayoutFactory;

/**
 * Class Index - Index controller
 */
class Index extends Action
{
    /**
     * ACL value
     */
    const ADMIN_RESOURCE = 'TNW_AuthorizeCim::payment_info';

    /**
     * @var Registry
     */
    private $coreRegistry;

    /**
     * @var LayoutFactory
     */
    private $resultLayoutFactory;

    /**
     * Index constructor.
     * @param Context $context
     * @param Registry $coreRegistry
     * @param LayoutFactory $resultLayoutFactory
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        LayoutFactory $resultLayoutFactory
    ) {
        $this->coreRegistry = $coreRegistry;
        $this->resultLayoutFactory = $resultLayoutFactory;
        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|ResultInterface|void
     */
    public function execute()
    {
        $customerId = (int) $this->getRequest()->getParam('id');
        if ($customerId) {
            $this->coreRegistry->register(RegistryConstants::CURRENT_CUSTOMER_ID, $customerId);
        }
        return $this->resultLayoutFactory->create();
    }
}
