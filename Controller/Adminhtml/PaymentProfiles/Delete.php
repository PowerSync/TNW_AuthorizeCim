<?php
/**
 * Copyright © 2021 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
namespace TNW\AuthorizeCim\Controller\Adminhtml\PaymentProfiles;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Customer\Controller\RegistryConstants;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Registry;
use TNW\AuthorizeCim\Model\Customer\PaymentProfileManagementInterface;

/**
 * Class Delete - Index controller
 */
class Delete extends Action implements HttpPostActionInterface
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
     * @var PaymentProfileManagementInterface
     */
    private $paymentProfileManagement;

    /**
     * Delete constructor.
     * @param Context $context
     * @param Registry $coreRegistry
     * @param PaymentProfileManagementInterface $paymentProfileManagement
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        PaymentProfileManagementInterface $paymentProfileManagement
    ) {
        $this->coreRegistry = $coreRegistry;
        $this->paymentProfileManagement = $paymentProfileManagement;
        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|ResultInterface|void
     */
    public function execute()
    {
        $customerId = (int) $this->getRequest()->getParam('id');
        $publicHash = $this->getRequest()->getParam('public_hash');

        $response = ['success' => false, 'message' => '',];
        if (!$customerId || !$publicHash) {
            $response['message'] = __('Invalid Request.');
        }

        $this->coreRegistry->register(RegistryConstants::CURRENT_CUSTOMER_ID, $customerId);
        try {
            if (!$this->paymentProfileManagement->delete($publicHash, $customerId)) {
                $response['message'] = __('Invalid Request.');
            }
            $response['success'] = true;
            $response['message'] = __('Payment Profile deleted.');
        } catch (Exception $exception) {
            $response['message'] = $exception->getMessage();
        }
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        return $result->setData($response);
    }
}
