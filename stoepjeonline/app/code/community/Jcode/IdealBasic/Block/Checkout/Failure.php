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
class Jcode_IdealBasic_Block_Checkout_Failure
	extends Mage_Core_Block_Template
	{
	    /**
	     *  Returns error from session and clears it
	     *
	     *  @return	  string
	     */
	    public function getErrorMessage ()
	    {
	        $error = Mage::getSingleton('checkout/session')->getIdealErrorMessage();
	        Mage::getSingleton('checkout/session')->unsIdealErrorMessage();
	        return $error;
	    }
	
	    /**
	     * Get continue shopping url
	     */
	    public function getContinueShoppingUrl()
	    {
	        return Mage::getUrl('checkout/cart');
	    }
	}