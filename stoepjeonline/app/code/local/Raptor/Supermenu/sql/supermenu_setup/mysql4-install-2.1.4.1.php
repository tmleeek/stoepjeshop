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

$menuStructure=<<<eod
<menu_item display='Sample Menu' link='htp://www.raptorcommerce.com'>
  <!-- Multiple columns not necessary for Custom menu but will aid upgrade to 
       Supermenu if you choose to do so at a later date -->
  <column>
    <menu_item display="Raptor Products" link="htp://www.raptorcommerce.com">
      <menu_item display='Custom Menu' link='http://www.raptorcommerce.com/custommenu.html' />
      <menu_item display='Supermenu' link='http://www.raptorcommerce.com/supermenu.html' />
    </menu_item>
  </column>
  <column>
    <menu_item display="Useful links">
      <menu_item display='Magento' link='http://www.magentocommerce.com' />
      <menu_item display='Zend' link='http://www.zendframework.com' />
    </menu_item>
  </column>
</menu_item>
eod;

$installer->run("

CREATE TABLE IF NOT EXISTS {$this->getTable('supermenu/custommenu')} (
  `custommenu_id` smallint(5) unsigned NOT NULL auto_increment,
  `title` varchar(255) NOT NULL default '',
  `content` text NOT NULL default '',
  `status` smallint(6) NOT NULL default '0',
  `sort_order` smallint(6),
  `created_time` datetime NULL,
  `update_time` datetime NULL,
  PRIMARY KEY (`custommenu_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS {$this->getTable('supermenu/custommenu_store')} (
  `custommenu_id` smallint(5) unsigned NOT NULL,
  `store_id` smallint(5) unsigned NOT NULL,
  PRIMARY KEY  (`custommenu_id`,`store_id`),
  CONSTRAINT `FK_CUSTOMMENU_MENU` FOREIGN KEY (`custommenu_id`) REFERENCES {$this->getTable('supermenu/custommenu')} (`custommenu_id`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `FK_CUSTOMMENU_STORE` FOREIGN KEY (`store_id`) REFERENCES {$this->getTable('core/store')} (`store_id`) ON UPDATE CASCADE ON DELETE CASCADE  
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Custommenu to Stores';

");

$installer->setConfigData('supermenu/menu_style/use_exploded', 1);	
$installer->setConfigData('supermenu/homelink/enabled', 0);
$installer->setConfigData('supermenu/categories/show_categories', 1);
$installer->setConfigData('supermenu/categories/columns', 2);
$installer->setConfigData('supermenu/brands/enabled', 1);
$installer->setConfigData('supermenu/brands/columns', 2);
$installer->setConfigData('supermenu/brands/column_width', 130);
$installer->setConfigData('supermenu/categories/brand_columns', 1);
$installer->setConfigData('supermenu/brands/attribute_code', 'brand');
$installer->setConfigData('supermenu/categories/column_width', 130);
$installer->setConfigData('supermenu/categories/show_third_level', 1);
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
        'sort'  => 50
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

Mage::getModel('supermenu/custommenu')
    ->setTitle('Sample menu - please change')
    ->setStatus(1)
    ->setStores(array(0))
    ->setContent($menuStructure)
    ->save();