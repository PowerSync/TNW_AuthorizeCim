<?php
/**
 * Copyright © 2021 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace TNW\AuthorizeCim\Model\Adminhtml\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Authorize.net Payment Action Dropdown source
 */
class PaymentAction implements OptionSourceInterface
{
    /**
     * "authorize" payment action
     *
     * @var string
     */
    const ACTION_AUTHORIZE = 'authorize';

    /**
     * "authorize_capture" payment action
     *
     * @var string
     */
    const ACTION_AUTHORIZE_CAPTURE = 'authorize_capture';

    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => static::ACTION_AUTHORIZE,
                'label' => __('Authorize Only'),
            ],
            [
                'value' => static::ACTION_AUTHORIZE_CAPTURE,
                'label' => __('Authorize and Capture')
            ]
        ];
    }
}
