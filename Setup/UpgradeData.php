<?php
/**
 * Copyright © 2016 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace TNW\AuthorizeCim\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;

/**
 * Class UpgradeData
 *
 * @package TNW\AuthorizeCim\Setup
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '2.1.4') < 0) {
            $this->version_2_1_4($context, $setup);
        }

        $setup->endSetup();
    }

    /**
     * @param ModuleContextInterface $context
     * @param ModuleDataSetupInterface $setup
     */
    protected function version_2_1_4(
        ModuleContextInterface $context,
        ModuleDataSetupInterface $setup
    ) {
        $setup->getConnection()->insert(
            $setup->getTable('core_config_data'),
            [
                'scope' => 'default',
                'scope_id' => 0,
                'path' => 'tnw_module-authorizenetcim/survey/start_date',
                'value' => date_create()->modify('+7 day')->getTimestamp()
            ]
        );
    }
}
