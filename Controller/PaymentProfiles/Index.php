<?php
/**
 * Copyright © 2021 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
namespace TNW\AuthorizeCim\Controller\PaymentProfiles;

use Magento\Customer\Controller\AccountInterface;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Index - list payment controller
 */
class Index implements AccountInterface
{
    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @param PageFactory $pageFactory
     */
    public function __construct(
        PageFactory $pageFactory
    ) {
        $this->resultPageFactory = $pageFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('My Payment Data'));
        return $resultPage;
    }
}
