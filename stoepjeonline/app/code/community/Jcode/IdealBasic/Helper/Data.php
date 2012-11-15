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
class Jcode_IdealBasic_Helper_Data
	extends Mage_Core_Helper_Abstract
	{
	    public function encrypt($token)
	    {
	        return bin2hex(base64_decode(Mage::helper('core')->encrypt($token)));
	    }
	
	    public function decrypt($token)
	    {
	        return Mage::helper('core')->decrypt(base64_encode(pack('H*', $token)));
	    }
	}