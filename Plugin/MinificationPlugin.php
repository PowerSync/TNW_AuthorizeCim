<?php
/**
 * Copyright © 2021 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
namespace TNW\AuthorizeCim\Plugin;

use Magento\Framework\View\Asset\Minification;

/**
 * Minification plugin - exclude authorize net from minification
 */
class MinificationPlugin
{
    /**
     * @param Minification $subject
     * @param array $excludes
     * @param $contentType
     * @return array
     */
    public function afterGetExcludes(Minification $subject, array $excludes, $contentType)
    {
        if ($contentType !== 'js') {
            return $excludes;
        }

        $excludes[] = '\.authorize\.net/v1/Accept';
        return $excludes;
    }
}
