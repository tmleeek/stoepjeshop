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
class Jcode_IdealBasic_Block_Checkout_Form
	extends Mage_Payment_Block_Form
	{
	    protected function _construct()
	    {
	        $this->setTemplate('jcode/idealbasic/checkout/form.phtml');
	        parent::_construct();
	    }
	}