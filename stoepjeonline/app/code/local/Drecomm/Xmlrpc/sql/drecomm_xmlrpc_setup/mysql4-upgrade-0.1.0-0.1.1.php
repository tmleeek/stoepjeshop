<?php
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$installer->run("
    ALTER TABLE {$this->getTable('customer/customer_group')} ADD COLUMN `customer_group_av_id` VARCHAR(500) NOT NULL AFTER `customer_group_id`;
    ALTER TABLE {$this->getTable('customer/customer_group')} ADD INDEX `customer_group_av_id` (`customer_group_av_id`);
    ALTER TABLE {$this->getTable('catalog/category')} ADD COLUMN `product_group_av_id` VARCHAR(500) NOT NULL AFTER `entity_id`;
    ALTER TABLE {$this->getTable('catalog/category')} ADD INDEX `product_group_av_id` (`product_group_av_id`);
");
$this->addAttribute('catalog_category', 'product_group_av_id', array(
    'type'     => 'static',
    'label'    => 'Product group Id',
    'visible'  => false,
    'required' => false,
    'input'    => 'text',
    'default'  => 0,
));