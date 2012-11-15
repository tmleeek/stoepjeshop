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
class Jcode_IdealAdvanced_Model_Api_Advanced_Request 
	extends Varien_Object
	{
	    public function clear() {
	        $this->unsMerchantId();
	        $this->unsSubId();
	        $this->unsAuthentication();
	    }

	     function checkMandatory () {
	        if (strlen($this->getMerchantId()) > 0
	            && strlen($this->getSubID()) > 0
	            && strlen($this->getAuthentication()) > 0) {
	            return true;
	        } else {
	            return false;
	        }
	    }
	
	    function setAuthentication($authentication) {
	        $this->setData('authentication', trim($authentication));
	    }

	    function setMerchantId($merchantID) {
	        $this->setData('merchant_id', trim($merchantID));
	    }
	
	    function setSubId($subID) {
	        $this->setData('sub_id', trim($subID));
	    }
	}
