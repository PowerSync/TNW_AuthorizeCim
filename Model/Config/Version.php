<?php
/**
 * Copyright © 2017 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
namespace TNW\AuthorizeCim\Model\Config;

use Magento\Framework\App\Config\Value;
use Magento\Framework\App\Config\Data\ProcessorInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\Module\Dir;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Data\Collection\AbstractDb;

/**
 * Config backend model for version display.
 */
class Version extends Value implements ProcessorInterface
{
    /**
     * @var Dir
     */
    protected $moduleDir;

    /**
     * @var File
     */
    protected $fileHandler;

    /**
     * Version constructor.
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param Dir $moduleDir
     * @param File $fileHandler
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        Dir $moduleDir,
        File $fileHandler,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $config,
            $cacheTypeList,
            $resource,
            $resourceCollection,
            $data
        );
        $this->moduleDir = $moduleDir;
        $this->fileHandler = $fileHandler;
    }

    /**
     * Get module version
     *
     * @return string
     */
    public function getDefaultValue()
    {
        try {
            $composerFile = $this->fileHandler->read(
                $this->moduleDir->getDir('TNW_AuthorizeCim') . DIRECTORY_SEPARATOR . 'composer.json'
            );
            $composer = json_decode($composerFile, 1);
            if (isset($composer['version'], $composer['time'])) {
                return $composer['version'] . ' (' . $composer['time'] . ')';
            } elseif (isset($composer['version'])) {
                return $composer['version'];
            } else {
                return __('Unknown (could not read composer.json)');
            }
        } catch (\Exception $e) {
            return __('Unknown (could not read composer.json)');
        }
    }

    /**
     * Inject current installed module version as the config value.
     *
     * @return $this
     */
    protected function _afterLoad()
    {
        $this->setValue($this->getDefaultValue());
        parent::_afterLoad();
        return $this;
    }

    /**
     * Process config value
     *
     * @param string $value
     * @return string
     */
    public function processValue($value)
    {
        return $this->getDefaultValue();
    }
}
