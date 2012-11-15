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
class Jcode_IdealAdvanced_Model_Api_Advanced_AcquirerStatusRequest 
	extends Jcode_IdealAdvanced_Model_Api_Advanced_Request
	{
	    function clear()
	    {
	        parent::clear();
	        $this->unsTransactionId();
	    }

	    function checkMandatory()
	    {
	        if (parent::checkMandatory() && strlen($this->getTransactionId()) > 0) {
	            return true;
	        } else {
	            return false;
	        }
	    }
	}
