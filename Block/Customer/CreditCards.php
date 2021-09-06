<?php
/**
 * Copyright © 2021 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
namespace TNW\AuthorizeCim\Block\Customer;

use Magento\Framework\View\Element\Template;
use Magento\Vault\Api\Data\PaymentTokenFactoryInterface;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Block\TokenRendererInterface;
use Magento\Customer\Model\Session;
use TNW\AuthorizeCim\Model\Customer\PaymentProfileManagementInterface;

/**
 * Class CreditCards
 */
class CreditCards extends Template
{
    /**
     * @var PaymentProfileManagementInterface
     */
    private $paymentProfileManagement;

    /**
     * @var Session
     */
    private $session;

    /**
     * @param Template\Context $context
     * @param PaymentProfileManagementInterface $paymentProfileManagement
     * @param Session $session
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        PaymentProfileManagementInterface $paymentProfileManagement,
        Session $session,
        array $data = []
    ) {
        $this->paymentProfileManagement = $paymentProfileManagement;
        $this->session = $session;
        parent::__construct($context, $data);
    }

    /**
     * @inheritdoc
     */
    public function getType()
    {
        return PaymentTokenFactoryInterface::TOKEN_TYPE_CREDIT_CARD;
    }

    /**
     * @inheritdoc
     */
    public function renderProfileHtml($profile)
    {
        $childBlock = $this->getChildBlock('customer.edit.tab.paymentinfo.card.item');
        if ($childBlock instanceof TokenRendererInterface) {
            return $childBlock->render($profile);
        }
        return '';
    }

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
        return (int) $this->session->getCustomerId();
    }
}
