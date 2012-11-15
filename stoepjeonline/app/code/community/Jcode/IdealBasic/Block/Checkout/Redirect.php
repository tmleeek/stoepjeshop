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
class Jcode_IdealBasic_Block_Checkout_Redirect
	extends Mage_Core_Block_Abstract
	{
	    protected function _toHtml()
	    {
	        $basic = $this->getOrder()->getPayment()->getMethodInstance();
	
	        $form = new Varien_Data_Form();
	        $form->setAction($basic->getApiUrl())
	            ->setId('ideal_basic_checkout')
	            ->setName('ideal_basic_checkout')
	            ->setMethod('POST')
	            ->setUseContainer(true);
	
	        foreach ($basic->getBasicCheckoutFormFields() as $field=>$value) :
	            $form->addField($field, 'hidden', array('name'=>$field, 'value'=>$value));
	        endforeach;
	
	        $html = '<html><body>';
	        $html.= $this->__('You will be redirected to iDEAL in a few seconds.');
	        $html.= $form->toHtml();
	        $html.= '<script type="text/javascript">document.getElementById("ideal_basic_checkout").submit();</script>';
	        $html.= '</body></html>';
	        return $html;
	    }
	}