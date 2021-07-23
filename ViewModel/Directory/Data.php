<?php
/**
 * Copyright © 2021 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace TNW\AuthorizeCim\ViewModel\Directory;

use Magento\Directory\Block\Data as DirectoryData;
use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * Class Data
 */
class Data implements ArgumentInterface
{
    /**
     * @var DirectoryData
     */
    private $directoryData;

    /**
     * Data constructor.
     * @param DirectoryData $directoryData
     */
    public function __construct(
        DirectoryData $directoryData
    ) {
        $this->directoryData = $directoryData;
    }

    /**
     * @param $method
     * @param $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        return $this->directoryData->$method(...$args);
    }
}
