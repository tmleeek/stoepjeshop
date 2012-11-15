<?php
/**
 * @copyright  Copyright (c) 2009 AITOC, Inc. 
 */

$installer = $this;
/* @var $installer Aitoc_Aitdownloadablefiles_Model_Resource_Eav_Mysql4_Setup */

$installer->startSetup();

$installer->addAttribute('catalog_product', 'aitfiles_title', array(
        'type'              => 'varchar',
        'backend'           => '',
        'frontend'          => '',
        'label'             => 'Free Downloads Title',
        'input'             => '',
        'class'             => '',
        'source'            => '',
        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
        'visible'           => false,
        'required'          => true,
        'user_defined'      => false,
        'default'           => '',
        'searchable'        => false,
        'filterable'        => false,
        'comparable'        => false,
        'visible_on_front'  => false,
        'unique'            => false,
        'apply_to'          => 'simple,configurable,virtual,bundle,downloadable',
        'is_configurable'   => false
    ));
$conn = $installer->getConnection();
/* @var $conn Varien_Db_Adapter_Pdo_Mysql */

$installer->run("
CREATE TABLE `{$installer->getTable('aitdownloadablefiles/aitfile')}` (
  `aitfile_id` int(10) unsigned NOT NULL auto_increment,
  `product_id` int(10) unsigned NOT NULL default '0',
  `aitfile_file` varchar(255) NOT NULL default '',
  `aitfile_url` varchar(255) NOT NULL default '',
  `aitfile_type` varchar(20) NOT NULL default '',
  `sort_order` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`aitfile_id`),
  KEY `DOWNLODABLE_AITFILE_PRODUCT` (`product_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
");

$conn->addConstraint(
    'FK_DOWNLODABLE_AITFILE_PRODUCT', $installer->getTable('aitdownloadablefiles/aitfile'), 'product_id', $installer->getTable('catalog/product'), 'entity_id'
);

$installer->run("
CREATE TABLE `{$installer->getTable('aitdownloadablefiles/aitfile_title')}` (
  `title_id` int(10) unsigned NOT NULL auto_increment,
  `aitfile_id` int(10) unsigned NOT NULL default '0',
  `store_id` smallint(5) unsigned NOT NULL default '0',
  `title` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`title_id`),
  KEY `DOWNLOADABLE_AITFILE_TITLE_AITFILE` (`aitfile_id`),
  KEY `DOWNLOADABLE_AITFILE_TITLE_STORE` (`store_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
");

$conn->addConstraint(
    'FK_DOWNLOADABLE_AITFILE_TITLE_AITFILE', $installer->getTable('aitdownloadablefiles/aitfile_title'), 'aitfile_id', $installer->getTable('aitdownloadablefiles/aitfile'), 'aitfile_id'
);
$conn->addConstraint(
    'FK_DOWNLOADABLE_AITFILE_TITLE_STORE', $installer->getTable('aitdownloadablefiles/aitfile_title'), 'store_id', $installer->getTable('core/store'), 'store_id'
);

$installer->endSetup();
