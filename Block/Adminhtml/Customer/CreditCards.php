<?php
/**
 * Copyright © 2021 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
namespace TNW\AuthorizeCim\Block\Adminhtml\Customer;

use \Magento\Vault\Api\Data\PaymentTokenFactoryInterface;
use Magento\Vault\Block\TokenRendererInterface;

/**
 * Class CreditCards
 */
class CreditCards extends PaymentProfiles
{
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
        $childBlock = $this->getChildBlock('adminhtml.customer.edit.tab.paymentinfo.card.item');
        if ($childBlock instanceof TokenRendererInterface) {
            return $childBlock->render($profile);
        }
        return '';
    }
}
