<?php
/**
 * Copyright © 2016 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace TNW\AuthorizeCim\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Eav\Api\Data\AttributeSetInterfaceFactory;

/**
 * Class CreateCustomerProfileAttributes - adds two new auth net attributes to customer entities
 */
class CreateCustomerProfileAttributes implements DataPatchInterface
{
    /**
     * @var EavSetupFactory
     */
    protected $eavSetupFactory;

    /**
     * @var ModuleDataSetupInterface
     */
    protected $moduleDataSetup;

    /**
     * @var CustomerSetupFactory
     */
    protected $customerSetupFactory;

    /**
     * @var AttributeSetInterfaceFactory
     */
    protected $attributeSetFactory;

    /**
     * CreateCustomerProfileAttributes constructor.
     * @param EavSetupFactory $eavSetupFactory
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param CustomerSetupFactory $customerSetupFactory
     * @param AttributeSetInterfaceFactory $attributeSetFactory
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory,
        ModuleDataSetupInterface $moduleDataSetup,
        CustomerSetupFactory $customerSetupFactory,
        AttributeSetInterfaceFactory $attributeSetFactory
    ) {
        $this->attributeSetFactory = $attributeSetFactory;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->customerSetupFactory = $customerSetupFactory;
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * @return DataPatchInterface|void
     */
    public function apply()
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $eavSetup->removeAttribute(
            Customer::ENTITY,
            'customer_profile_id'
        );
        $customerSetup = $this->customerSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $customerEntity = $customerSetup->getEavConfig()->getEntityType('customer');
        $attributeSetId = $customerEntity->getDefaultAttributeSetId();

        $attributeSet = $this->attributeSetFactory->create();
        $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);

        $customerSetup->addAttribute(Customer::ENTITY, 'customer_profile_id', [
            'type' => 'varchar',
            'label' => 'AuthorizeNet Customer Profile Id',
            'input' => 'text',
            'required' => false,
            'visible' => true,
            'user_defined' => true,
            'visible_on_front' => false,
            'sort_order' => 1000,
            'position' => 1000,
            'system' => 0,
        ]);
        $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'customer_profile_id')
            ->addData([
                'attribute_set_id' => $attributeSetId,
                'attribute_group_id' => $attributeGroupId,
                'used_in_forms' => ['adminhtml_customer'],
            ])
            ->save();


        $eavSetup = $this->eavSetupFactory->create();

        $eavSetup->addAttribute('customer_address', 'customer_profile_id', [
            'type'             => 'varchar',
            'input'            => 'text',
            'label'            => 'AuthorizeNet Customer Profile ID',
            'visible'          => true,
            'required'         => false,
            'user_defined'     => true,
            'system'           => false,
            'group'            => 'General',
            'global'           => true,
            'visible_on_front' => false,
            'sort_order' => 1000,
            'position' => 1000,
        ]);

        $customAttribute = $customerSetup->getEavConfig()->getAttribute(
            'customer_address',
            'customer_profile_profile_id'
        );

        $customAttribute->setData(
            'used_in_forms',
            [
                'adminhtml_customer_address'
            ]
        )->save();
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
}
