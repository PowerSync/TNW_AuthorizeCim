<?php
/**
 * Copyright © 2021 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
namespace TNW\AuthorizeCim\Block\Adminhtml\Customer;

use Magento\Customer\Controller\RegistryConstants;
use Magento\Framework\View\Element\Template;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use TNW\AuthorizeCim\Model\Customer\PaymentProfileManagementInterface;

/**
 * Class PaymentProfiles
 */
abstract class PaymentProfiles extends Template
{
    /**
     * @var PaymentTokenInterface[]
     */
    private $customerTokens = [];

    /**
     * @var PaymentProfileManagementInterface
     */
    private $paymentProfileManagement;

    /**
     * PaymentProfiles constructor.
     * @param Template\Context $context
     * @param PaymentProfileManagementInterface $paymentProfileManagement
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        PaymentProfileManagementInterface $paymentProfileManagement,
        array $data = []
    ) {
        $this->paymentProfileManagement = $paymentProfileManagement;
        parent::__construct(
            $context,
            $data
        );
    }

    /**
     * Get type of token
     * @return string
     */
    abstract public function getType();

    /**
     * @param $profile
     *
     * @return string
     */
    abstract public function renderProfileHtml($profile);

    /**
     * @return PaymentTokenInterface[]
     */
    public function getPaymentProfiles()
    {
        $tokens = [];
        foreach ($this->getCustomerTokens() as $token) {
            if ($token->getType() === $this->getType()) {
                $tokens[] = $token;
            }
        }
        return $tokens;
    }

    /**
     * Checks if customer tokens exists
     *
     * @return bool
     */
    public function isExistsPaymentProfiles()
    {
        return !empty($this->getCustomerTokens());
    }

    /**
     * Get customer tokens
     *
     * @return PaymentTokenInterface[]
     */
    private function getCustomerTokens()
    {
        if (empty($this->customerTokens)) {
            $customerId = $this->getCustomerId();
            if ($customerId) {
                $this->customerTokens = $this->paymentProfileManagement->getByCustomerId($customerId);
            }
        }
        return $this->customerTokens;
    }

    /**
     * Get customer Id
     *
     * @return int
     */
    private function getCustomerId()
    {
        return (int) $this->getRequest()->getParam('id');
    }
}
