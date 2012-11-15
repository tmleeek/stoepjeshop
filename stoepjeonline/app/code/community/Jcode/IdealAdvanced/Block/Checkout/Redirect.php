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
class Jcode_IdealAdvanced_Block_Checkout_Redirect
	extends Mage_Core_Block_Abstract
	{
	    protected function _toHtml()
	    {
	        $html = '<html><body>';
	        $html.= $this->getMessage();
	        $html.= '<script type="text/javascript">location.href = "' . $this->getRedirectUrl() . '";</script>';
	        $html.= '</body></html>';
	        return $html;
	    }
	}