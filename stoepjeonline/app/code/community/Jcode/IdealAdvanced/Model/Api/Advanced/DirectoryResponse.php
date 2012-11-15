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
class Jcode_IdealAdvanced_Model_Api_Advanced_DirectoryResponse 
	extends Jcode_IdealAdvanced_Model_Api_Advanced_Response 
	{
	    function addIssuer($issuer) 
	    {
	        if(is_a($issuer, "Jcode_IdealAdvanced_Api_Advanced_Issuer")) {
	            $this->setIssuerList(array_merge((array)$this->getIssuerList(), (array)$issuer));
	        }
	    }
	}