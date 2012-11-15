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

$installer->setConfigData('supermenu/categories/columns', 2);
$installer->setConfigData('supermenu/categories/column_width', 130);
$installer->setConfigData('supermenu/brands/enabled', 1);
$installer->setConfigData('supermenu/brands/columns', 2);
$installer->setConfigData('supermenu/categories/brand_columns', 1);
$installer->setConfigData('supermenu/brands/column_width', 130);
$installer->setConfigData('supermenu/brands/attribute_code', 'brand');
$installer->setConfigData('supermenu/categories/variable_column_widths', 0);

// Add the new attribute for the categories
$installer->addAttribute('catalog_category', 'columns', array(
                        'type'              => 'int',
                        'backend'           => '',
                        'frontend'          => '',
                        'label'             => 'Number of columns',
                        'input'             => 'text',
                        'class'             => '',
                        'source'            => '',
                        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
                        'visible'           => true,
                        'required'          => false,
                        'user_defined'      => false,
                        'default'           => '',
                        'searchable'        => false,
                        'filterable'        => false,
                        'comparable'        => false,
                        'visible_on_front'  => false,
                        'unique'            => false,
                    ));
                    
// Add the new attribute for the categories
$installer->addAttribute('catalog_category', 'column_width', array(
                        'type'              => 'int',
                        'backend'           => '',
                        'frontend'          => '',
                        'label'             => 'Column width',
                        'input'             => 'text',
                        'class'             => '',
                        'source'            => '',
                        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
                        'visible'           => true,
                        'required'          => false,
                        'user_defined'      => false,
                        'default'           => '',
                        'searchable'        => false,
                        'filterable'        => false,
                        'comparable'        => false,
                        'visible_on_front'  => false,
                        'unique'            => false,
                    ));  

// Add the new attribute for the categories
$installer->addAttribute('catalog_category', 'logo', array(
                        'type'              => 'varchar',
                        'backend'           => 'catalog/category_attribute_backend_image',
                        'frontend'          => '',
                        'label'             => 'Logo/Image',
                        'input'             => 'image',
                        'class'             => '',
                        'source'            => '',
                        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
                        'visible'           => true,
                        'required'          => false,
                        'user_defined'      => false,
                        'default'           => '',
                        'searchable'        => false,
                        'filterable'        => false,
                        'comparable'        => false,
                        'visible_on_front'  => false,
                        'unique'            => false,
                    ));
                    
// Add the new attribute for the categories
$installer->addAttribute('catalog_category', 'brand_columns', array(
                        'type'              => 'int',
                        'backend'           => '',
                        'frontend'          => '',
                        'label'             => 'Shop by brand columns',
                        'input'             => 'text',
                        'class'             => '',
                        'source'            => '',
                        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
                        'visible'           => true,
                        'required'          => false,
                        'user_defined'      => false,
                        'default'           => '',
                        'searchable'        => false,
                        'filterable'        => false,
                        'comparable'        => false,
                        'visible_on_front'  => false,
                        'unique'            => false,
                    ));                    
                    
// Display the new attribute on the "Display Settings" tab

$entityTypeId     = $installer->getEntityTypeId('catalog_category');
$attributeSetId   = $installer->getDefaultAttributeSetId($entityTypeId);
$attributeGroupId = $installer->getDefaultAttributeGroupId($entityTypeId, $attributeSetId);

// update General Group
$installer->updateAttributeGroup($entityTypeId, $attributeSetId, $attributeGroupId, 'attribute_group_name', 'General Information');
$installer->updateAttributeGroup($entityTypeId, $attributeSetId, $attributeGroupId, 'sort_order', '10');

$groups = array(
    'display'   => array(
        'name'  => 'Display Settings',
        'sort'  => 20,
        'id'    => null
    ),
    'design'    => array(
        'name'  => 'Custom Design',
        'sort'  => 30,
        'id'    => null
    ),
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
    'custom_design'         => array(
        'group' => 'design',
        'sort'  => 10
    ),
    'custom_design_apply'   => array(
        'group' => 'design',
        'sort'  => 20
    ),
    'custom_design_from'    => array(
        'group' => 'design',
        'sort'  => 30
    ),
    'custom_design_to'      => array(
        'group' => 'design',
        'sort'  => 40
    ),
    'page_layout'           => array(
        'group' => 'design',
        'sort'  => 50
    ),
    'custom_layout_update'  => array(
        'group' => 'design',
        'sort'  => 60
    ),
    'columns'         => array(
        'group' => 'supermenu',
        'sort'  => 10
    ),
    'column_width'         => array(
        'group' => 'supermenu',
        'sort'  => 20
    ),  
    'logo'         => array(
        'group' => 'supermenu',
        'sort'  => 30
    ), 
    'brand_columns' => array(
        'group' => 'supermenu',
        'sort'  => 40
    ),      
    'display_mode'          => array(
        'group' => 'display',
        'sort'  => 20
    ),
    'landing_page'          => array(
        'group' => 'display',
        'sort'  => 30
    ),
    'is_anchor'             => array(
        'group' => 'display',
        'sort'  => 40
    ),
    'available_sort_by'     => array(
        'group' => 'display',
        'sort'  => 50
    ),
    'default_sort_by'       => array(
        'group' => 'display',
        'sort'  => 60
    ),
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

$describe = $installer->getConnection()->describeTable($installer->getTable('catalog/eav_attribute'));
foreach ($describe as $columnData) {
    if ($columnData['COLUMN_NAME'] == 'attribute_id') {
        continue;
    }
    $installer->getConnection()->dropColumn($installer->getTable('eav/attribute'), $columnData['COLUMN_NAME']);
}
                                        
$installer->endSetup(); 