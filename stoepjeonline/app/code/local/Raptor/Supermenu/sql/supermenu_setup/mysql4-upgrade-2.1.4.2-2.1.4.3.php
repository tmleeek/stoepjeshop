<?php
/**
 * Raptor Commerce
 *
 * @category   Raptor
 * @package    Raptor_Supermenu
 * @copyright  Copyright (c) 2009 Raptor Commerce (http://www.raptorcommerce.com)
 */

$installer = $this;
$installer->startSetup();
$installer->setConfigData('supermenu/categories/enable_brands', 1);

// Add the new attribute for the categories
$installer->addAttribute('catalog_category', 'enable_brands', array(
    'type'     => 'int',
    'label'    => 'Show brands',
    'input'    => 'select',
    'source'   => 'supermenu/entity_attribute_source_boolean',
    'global'   => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'required' => false,
    'default'  => 1
));

// Display the new attribute on the "Display Settings" tab
$entityTypeId     = $installer->getEntityTypeId('catalog_category');
$attributeSetId   = $installer->getDefaultAttributeSetId($entityTypeId);
$attributeGroupId = $installer->getDefaultAttributeGroupId($entityTypeId, $attributeSetId);
                    
$groups = array(
    'supermenu'    => array(
        'name'  => 'Supermenu',
        'sort'  => 40,
        'id'    => null
    )    
);

foreach ($groups as $k => $groupProp) {
    $installer->addAttributeGroup($entityTypeId, $attributeSetId, $groupProp['name'], $groupProp['sort']);
    $groups[$k]['id'] = $installer->getAttributeGroupId($entityTypeId, $attributeSetId, $groupProp['name']);
}

// update attributes group and sort
$attributes = array(
    'enable_brands' => array(
        'group' => 'supermenu',
        'sort'  => 40
    )
);

foreach ($attributes as $attributeCode => $attributeProp) {
    $installer->addAttributeToGroup(
        $entityTypeId,
        $attributeSetId,
        $groups[$attributeProp['group']]['id'],
        $attributeCode,
        $attributeProp['sort']
    );
}

$attributeId = $installer->getAttributeId($entityTypeId, 'enable_brands');

$installer->run("
INSERT INTO `{$installer->getTable('catalog_category_entity_int')}`
(`entity_type_id`, `attribute_id`, `entity_id`, `value`)
    SELECT '{$entityTypeId}', '{$attributeId}', `entity_id`, '1'
        FROM `{$installer->getTable('catalog_category_entity')}`;
");
$installer->endSetup(); 
