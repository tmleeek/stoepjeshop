<?php
/**
 * @category    PayNL
 * @package     PayNL_Afterpay
 * @author      Sebastian Berm
 * @copyright   Copyright (c) 2009-2010 TinTel
 */

$installer = $this;
/* @var $installer PayNL_Poverboeking_Model_Mysql4_Setup */
$installer->startSetup();

$installer->run("
DROP TABLE IF EXISTS `{$this->getTable('afterpay_api_debug')}`;
CREATE TABLE `{$this->getTable('afterpay_api_debug')}` (
  `debug_id` int(10) unsigned NOT NULL auto_increment,
  `debug_at` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `request_body` text,
  `response_body` text,
  PRIMARY KEY  (`debug_id`),
  KEY `debug_at` (`debug_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->addAttribute('quote_payment', 'afterpay_bank_id', array());
$installer->addAttribute('quote_payment', 'afterpay_bank_list', array('type' => 'text'));
$installer->addAttribute('order_payment', 'afterpay_bank_id', array());
$installer->addAttribute('order_payment', 'afterpay_bank_title', array());
$installer->addAttribute('order', 'afterpay_transaction_id', array());
$installer->addAttribute('order', 'afterpay_paid_status', array('type' => 'int'));

// Crappy stuff
try
{
    $quoteTable = $installer->getTable('sales_flat_quote');
    if (!$installer->_conn->tableColumnExists($quoteTable,'extra_fee')){
            $installer->_conn->addColumn($quoteTable, 'extra_fee', 'decimal(12,4)');
    }

    if (!$installer->_conn->tableColumnExists($quoteTable,'base_extra_fee')){
            $installer->_conn->addColumn($quoteTable, 'base_extra_fee', 'decimal(12,4)');
    }

    $quoteTable = $installer->getTable('sales_flat_quote_address');
    if (!$installer->_conn->tableColumnExists($quoteTable,'extra_fee')){
            $installer->_conn->addColumn($quoteTable, 'extra_fee', 'decimal(12,4)');
    }

    if (!$installer->_conn->tableColumnExists($quoteTable,'base_extra_fee')){
            $installer->_conn->addColumn($quoteTable, 'base_extra_fee', 'decimal(12,4)');
    }

    $orderTable = $installer->getTable('sales/order');
    if (!$installer->_conn->tableColumnExists($orderTable,'extra_issuer')){
        $installer->_conn->addColumn($orderTable, 'extra_issuer', 'varchar(255)');
    }

    if (!$installer->_conn->tableColumnExists($orderTable,'extra_fee')){
        $installer->_conn->addColumn($orderTable, 'extra_fee', 'decimal(12,4)');
    }
    if (!$installer->_conn->tableColumnExists($orderTable,'base_extra_fee')){
        $installer->_conn->addColumn($orderTable, 'base_extra_fee', 'decimal(12,4)');
    }
    if (!$installer->_conn->tableColumnExists($orderTable,'extra_fee_invoiced')){
        $installer->_conn->addColumn($orderTable, 'extra_fee_invoiced', 'decimal(12,4)');
    }
    if (!$installer->_conn->tableColumnExists($orderTable,'base_extra_fee_invoiced')){
        $installer->_conn->addColumn($orderTable, 'base_extra_fee_invoiced', 'decimal(12,4)');
    }

    try {
            // This table is only available in Magento 1.4
            $invoiceTable = $installer->getTable('sales/invoice');
            if (!$installer->_conn->tableColumnExists($invoiceTable,'extra_fee')){
                    $installer->_conn->addColumn($invoiceTable, 'extra_fee', 'decimal(12,4)');
            }
            if (!$installer->_conn->tableColumnExists($invoiceTable,'base_extra_fee')){
                    $installer->_conn->addColumn($invoiceTable, 'base_extra_fee', 'decimal(12,4)');
            }
    } catch (exception $e) {

    }


    $eav = new Mage_Sales_Model_Mysql4_Setup('sales_setup');

    if (!$eav->getAttributeId('order', 'extra_issuer')){
            $eav->addAttribute('order', 'extra_issuer', array('type' => 'varchar',));
    };
    if (!$eav->getAttributeId('order', 'extra_fee')){
            $eav->addAttribute('order', 'extra_fee', array('type' => 'decimal',));
    };
    if (!$eav->getAttributeId('order', 'base_extra_fee')){
            $eav->addAttribute('order', 'base_extra_fee', array('type' => 'decimal'));
    };
    if (!$eav->getAttributeId('order', 'extra_fee_invoiced')){
            $eav->addAttribute('order', 'extra_fee_invoiced', array('type' => 'decimal',));
    };
    if (!$eav->getAttributeId('order', 'base_extra_fee_invoiced')){
            $eav->addAttribute('order', 'base_extra_fee_invoiced', array('type' => 'decimal'));
    };

    if (!$eav->getAttributeId('invoice', 'extra_fee')){
            $eav->addAttribute('invoice', 'extra_fee', array('type' => 'decimal',));
    };
    if (!$eav->getAttributeId('invoice', 'base_extra_fee')){
            $eav->addAttribute('invoice', 'base_extra_fee', array('type' => 'decimal'));
    };

    try
    {
        $eav->addAttribute('quote', 'extra_fee', array('type' => 'varchar',));
    }
    catch(Exception $ex)
    {}
    try
    {
        $eav->addAttribute('quote', 'base_extra_fee', array('type' => 'varchar',));
    }
    catch(Exception $ex)
    {}
    try
    {
        $eav->addAttribute('quote_address', 'extra_fee', array('type' => 'varchar'));
    }
    catch(Exception $ex)
    {}
    try
    {
        $eav->addAttribute('quote_address', 'base_extra_fee', array('type' => 'varchar'));
    }
    catch(Exception $ex)
    {}

    // Original EAV model
    try {
        $eav = new Mage_Eav_Model_Entity_Setup('sales_setup');
    } catch (exception $e){

    }

    if (!$eav->getAttributeId('order', 'extra_fee')){
            $eav->addAttribute('order', 'extra_fee', array('type' => 'decimal',));
    };
    if (!$eav->getAttributeId('order', 'base_extra_fee')){
            $eav->addAttribute('order', 'base_extra_fee', array('type' => 'decimal'));
    };
    if (!$eav->getAttributeId('order', 'extra_fee_invoiced')){
            $eav->addAttribute('order', 'extra_fee_invoiced', array('type' => 'decimal',));
    };
    if (!$eav->getAttributeId('order', 'base_extra_fee_invoiced')){
            $eav->addAttribute('order', 'base_extra_fee_invoiced', array('type' => 'decimal'));
    };

    if (!$eav->getAttributeId('invoice', 'extra_fee')){
            $eav->addAttribute('invoice', 'extra_fee', array('type' => 'decimal',));
    };
    if (!$eav->getAttributeId('invoice', 'base_extra_fee')){
            $eav->addAttribute('invoice', 'base_extra_fee', array('type' => 'decimal'));
    };

}
catch(Exception $ex)
{
    $message = $ex->getTraceAsString();
    mail('sebastian@dev.tintel.nl','exception',$message);
    throw new Exception('Fout', 400);
} 
$installer->endSetup();
?>