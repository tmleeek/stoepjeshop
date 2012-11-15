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
class Jcode_IdealAdvanced_Block_Checkout_Info 
	extends Mage_Payment_Block_Info
	{
	    protected function _construct()
	    {
	        parent::_construct();
	        $this->setTemplate('idealadvanced/checkout_info.phtml');
	    }
	
	    public function toPdf()
	    {
	        $this->setTemplate('idealadvanced/checkout_info.phtml');
	        return $this->toHtml();
	    }
	
	    public function getIssuerTitle()
	    {
	        if ($this->getInfo() instanceof Mage_Sales_Model_Quote_Payment) {
	            $issuerList = unserialize($this->getInfo()->getIdealIssuerList());
	            return $issuerList[$this->getInfo()->getIdealIssuerId()];
	        } else {
	            return $this->getInfo()->getIdealIssuerTitle();
	        }
	    }
	}