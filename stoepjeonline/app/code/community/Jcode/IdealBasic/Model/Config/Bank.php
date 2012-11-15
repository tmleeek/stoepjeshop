<?php
/**
* J!Code WebDevelopment
*
* @title 		Magento payment module for iDeal Basic
* @category 	J!Code
* @package 		Jcode_Community
* @author 		Jeroen Bleijenberg / J!Code WebDevelopment <support@jcode.nl>
* @license  	http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*/
class Jcode_IdealBasic_Model_Config_Bank
	extends Mage_Adminhtml_Model_System_Config_Source_Yesno
	{
	    public function toOptionArray()
	    {
	        return array(
	            array('value' => '', 'label'=>Mage::helper('adminhtml')->__('-- Please Select --')),
	            array('value' => 'ing', 'label'=>Mage::helper('adminhtml')->__('ING')),
	            array('value' => 'abn', 'label'=>Mage::helper('adminhtml')->__('ABN Amro')),
	            array('value' => 'rabo', 'label'=>Mage::helper('adminhtml')->__('Rabobank'))
	        );
	    }
	}