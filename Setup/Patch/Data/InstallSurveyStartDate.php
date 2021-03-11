<?php
/**
 * Copyright © 2016 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace TNW\AuthorizeCim\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

class InstallSurveyStartDate implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $setup;

    /**
     * @param ModuleDataSetupInterface $setup
     */
    public function __construct(
        ModuleDataSetupInterface $setup
    ) {
        $this->setup = $setup;
    }

    /**
     * @inheritDoc
     */
    public function apply()
    {
        $this->setup->getConnection()->insert(
            $this->setup->getTable('core_config_data'),
            [
                'scope' => 'default',
                'scope_id' => 0,
                'path' => 'tnw_module-authorizenetcim/survey/start_date',
                'value' => date_create()->modify('+7 day')->getTimestamp()
            ]
        );
    }

    /**
     * @inheritDoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public static function getVersion()
    {
        return '2.1.4';
    }
}
