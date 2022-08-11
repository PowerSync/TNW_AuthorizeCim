<?php
/**
 * Copyright © TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */

namespace TNW\AuthorizeCim\Plugin\Framework\App\Config;

use Magento\Framework\App\Config;
use Magento\Vault\Model\CustomerTokenManagement;

/**
 * Plugin for enabling Authorize.net vault payments if vault is disabled, but customer has saved cards
 */
class AfterGetTnwAuthorizeCimVaultActiveValue
{
    /**
     * @var CustomerTokenManagement
     */
    private $customerTokenManagement;

    /**
     * @var bool|null
     */
    private $customerHasTokens;

    /**
     * AfterGetTnwAuthorizeCimVaultActiveValue constructor
     *
     * @param CustomerTokenManagement $customerTokenManagement
     */
    public function __construct(CustomerTokenManagement $customerTokenManagement)
    {
        $this->customerTokenManagement = $customerTokenManagement;
    }

    /**
     * Overrides 'active' config value of Auth.net CIM vault payment method when customer have active payment tokens.
     *
     * @param Config $subject
     * @param mixed $result
     * @param string|null $path
     * @return bool|mixed|null
     */
    public function afterGetValue(Config $subject, $result, $path = null)
    {
        if ($path === 'payment/tnw_authorize_cim_vault/active' && !$result) {
            return $this->getCustomerHasTokens();
        }
        return $result;
    }

    /**
     * Checks if customer has Authorize.net CIM tokens
     *
     * @return bool
     */
    private function getCustomerHasTokens()
    {
        if ($this->customerHasTokens === null) {
            $tokens = $this->customerTokenManagement->getCustomerSessionTokens();
            $this->customerHasTokens = false;
            foreach ($tokens as $token) {
                if ($token->getPaymentMethodCode() === 'tnw_authorize_cim') {
                    $this->customerHasTokens = true;
                    break;
                }
            }
        }

        return $this->customerHasTokens;
    }
}
