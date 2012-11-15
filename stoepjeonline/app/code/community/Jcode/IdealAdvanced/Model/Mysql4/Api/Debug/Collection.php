<?php
/**
* J!Code WebDevelopment
*
* @title 		Magento payment module for iDeal Advanced
* @category 	J!Code
* @package 		Jcode_Community
* @author 		Jeroen Bleijenberg / J!Code WebDevelopment <support@jcode.nl>
* @license  	http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*/
class Jcode_IdealAdvanced_Model_Mysql4_Api_Debug_Collection 
	extends Mage_Core_Model_Mysql4_Collection_Abstract
	{
	    protected function _construct()
	    {
	        $this->_init('idealadvanced/api_debug');
	    }
	}