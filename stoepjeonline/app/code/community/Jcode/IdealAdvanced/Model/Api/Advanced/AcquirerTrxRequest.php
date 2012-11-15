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
class Jcode_IdealAdvanced_Model_Api_Advanced_AcquirerTrxRequest 
	extends Jcode_IdealAdvanced_Model_Api_Advanced_Request
	{
	    function clear() {
	        parent::clear();
	        $this->unsIssuerId();
	        $this->unsMerchantReturnUrl();
	        $this->unsPurchaseId();
	        $this->unsAmount();
	        $this->unsCurrency();
	        $this->unsExpirationPeriod();
	        $this->unsLanguage();
	        $this->unsDescription();
	        $this->unsEntranceCode();
	    }
	
	    function checkMandatory () {
	        if ((parent::checkMandatory() == true)
	            && (strlen($this->getIssuerId()) > 0)
	            && (strlen($this->getMerchantReturnUrl()) > 0)
	            && (strlen($this->getPurchaseID()) > 0)
	            && (strlen($this->getAmount()) > 0)
	            && (strlen($this->getCurrency()) > 0)
	            && (strlen($this->getExpirationPeriod()) > 0)
	            && (strlen($this->getLanguage()) > 0)
	            && (strlen($this->getEntranceCode()) > 0)
	            && (strlen($this->getDescription()) > 0)
	            ) {
	            return true;
	        } else {
	            return false;
	        }
	    }
	
	}
