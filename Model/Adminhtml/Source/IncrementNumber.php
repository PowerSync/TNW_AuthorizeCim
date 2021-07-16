<?php
/**
 * Copyright © 2021 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */

namespace TNW\AuthorizeCim\Model\Adminhtml\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class IncrementNumber
 * @package TNW\AuthorizeCim\Model\Adminhtml\Source
 */
class IncrementNumber implements OptionSourceInterface
{
    /**
     * @var int
     */
    const NO_DATA = 0;

    /**
     * @var int
     */
    const ORDER_NUMBER = 1;

    /**
     * @return array|array[]
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::NO_DATA,
                'label' => __('No data'),
            ],
            [
                'value' => self::ORDER_NUMBER,
                'label' => __('Order number')
            ],
        ];
    }
}
