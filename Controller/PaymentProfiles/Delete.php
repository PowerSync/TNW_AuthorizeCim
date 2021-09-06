<?php
/**
 * Copyright © 2021 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
namespace TNW\AuthorizeCim\Controller\PaymentProfiles;

use Exception;
use Magento\Customer\Controller\AccountInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use TNW\AuthorizeCim\Model\Customer\PaymentProfileManagementInterface;

/**
 * Class Delete - delete card controller
 */
class Delete implements AccountInterface, HttpPostActionInterface
{
    /**
     * ACL value
     */
    const ADMIN_RESOURCE = 'TNW_AuthorizeCim::payment_info';

    /**
     * @var PaymentProfileManagementInterface
     */
    private $paymentProfileManagement;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var ResultFactory
     */
    private $resultFactory;

    /**
     * Delete constructor.
     * @param PaymentProfileManagementInterface $paymentProfileManagement
     * @param Session $session
     * @param RequestInterface $request
     * @param ResultFactory $resultFactory
     */
    public function __construct(
        PaymentProfileManagementInterface $paymentProfileManagement,
        Session $session,
        RequestInterface $request,
        ResultFactory $resultFactory
    ) {
        $this->paymentProfileManagement = $paymentProfileManagement;
        $this->session = $session;
        $this->request = $request;
        $this->resultFactory = $resultFactory;
    }

    /**
     * @return ResponseInterface|ResultInterface|void
     */
    public function execute()
    {
        $customerId = $this->getCustomerId();
        $publicHash = $this->request->getParam('public_hash');
        $response = ['success' => false, 'message' => '',];
        if (!$customerId || !$publicHash) {
            $response['message'] = __('Invalid Request.');
        }
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

    /**
     * @return int
     */
    private function getCustomerId()
    {
        return (int) $this->session->getCustomerId();
    }
}
