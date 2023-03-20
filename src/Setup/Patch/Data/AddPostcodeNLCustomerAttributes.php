<?php

namespace Trinos\PostcodeNL\Setup\Patch\Data;

use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\ResourceModel\Attribute;
use Magento\Eav\Model\Config;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class AddPostcodeNLCustomerAttributes implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private ModuleDataSetupInterface $moduleDataSetup;

    /**
     * @var EavSetupFactory
     */
    private EavSetupFactory $eavSetupFactory;

    /**
     * @var Config
     */
    private Config $eavConfig;

    /**
     * @var Attribute
     */
    private Attribute $attributeResource;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     * @param Config $eavConfig
     * @param Attribute $attributeResource
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory          $eavSetupFactory,
        Config                   $eavConfig,
        Attribute                $attributeResource
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->eavConfig = $eavConfig;
        $this->attributeResource = $attributeResource;
    }

    /**
     * Get array of patches that have to be executed prior to this.
     *
     * Example of implementation:
     *
     * [
     *      \Vendor_Name\Module_Name\Setup\Patch\Patch1::class,
     *      \Vendor_Name\Module_Name\Setup\Patch\Patch2::class
     * ]
     *
     * @return string[]
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * Run code inside patch
     * If code fails, patch must be reverted, in case when we are speaking about schema - then under revert
     * means run PatchInterface::revert()
     *
     * If we speak about data, under revert means: $transaction->rollback()
     *
     * @return $this
     */
    public function apply()
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $eavSetup->addAttribute(
            Customer::ENTITY,
            'postcodenl_housenumber',
            [
                'input' => 'text',
                'is_visible_in_grid' => false,
                'visible' => false,
                'user_defined' => false,
                'is_filterable_in_grid' => false,
                'system' => false,
                'label' => 'Housenumber',
                'source' => null,
                'position' => 10,
                'type' => 'text',
                'is_used_in_grid' => false,
                'required' => false,
            ]
        );

        $eavSetup->addAttribute(
            Customer::ENTITY,
            'postcodenl_housenumber_addition',
            [
                'input' => 'text',
                'is_visible_in_grid' => false,
                'visible' => false,
                'user_defined' => false,
                'is_filterable_in_grid' => false,
                'system' => false,
                'label' => 'Housenumber addition',
                'source' => null,
                'position' => 10,
                'type' => 'text',
                'is_used_in_grid' => false,
                'required' => false,
            ]
        );

        $eavSetup->addAttributeToSet(
            CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
            CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER,
            'Default',
            'postcodenl_housenumber'
        );

        $eavSetup->addAttributeToSet(
            CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
            CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER,
            'Default',
            'postcodenl_housenumber_addition'
        );

        $eavSetup->addAttribute(
            AddressMetadataInterface::ENTITY_TYPE_ADDRESS,
            'postcodenl_manual_mode',
            [
                'type' => 'int',
                'input' => 'boolean',
                'label' => 'Manually set up address',
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'system' => false,
            ]);

        $eavSetup->addAttributeToSet(
            AddressMetadataInterface::ENTITY_TYPE_ADDRESS,
            AddressMetadataInterface::ATTRIBUTE_SET_ID_ADDRESS,
            'Default',
            'postcodenl_manual_mode'
        );

        $customAttribute = $this->eavConfig->getAttribute(AddressMetadataInterface::ENTITY_TYPE_ADDRESS, 'postcodenl_manual_mode');

        $customAttribute->setData(
            'used_in_forms',
            ['adminhtml_customer_address', 'customer_address_edit', 'customer_register_address'], //list of forms where you want to display the custom attribute
        );
        $customAttribute->save();

        return $this;
    }

    /**
     * Get aliases (previous names) for the patch.
     *
     * @return string[]
     */
    public
    function getAliases()
    {
        return [];
    }
}
