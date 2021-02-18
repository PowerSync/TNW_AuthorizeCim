<?php
/**
 * Copyright © 2017 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace TNW\AuthorizeCim\Model\Adminhtml\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Environment
 * @package TNW\AuthorizeCim\Model\Adminhtml\Source
 */
class Environment implements OptionSourceInterface
{
    /**
     * @var string
     */
    const ENVIRONMENT_LIVE = 'live';

    /**
     * @var string
     */
    const ENVIRONMENT_SANDBOX = 'sandbox';

    /**
     * Possible environment types
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::ENVIRONMENT_SANDBOX,
                'label' => __('Sandbox'),
            ],
            [
                'value' => self::ENVIRONMENT_LIVE,
                'label' => __('Live')
            ]
        ];
    }
}
