<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category   Mymonki
 * @package    Mymonki_Ship2pay
 * @copyright  Copyright (c) 2010 Freshmind Sp. z o.o. (pawel@freshmind.pl)
 */

class Mymonki_Ship2pay_Block_Adminhtml_Ship2pay extends Mage_Adminhtml_Block_System_Config_Form_Field
{
	protected $_addRowButtonHtml = array();
    protected $_removeRowButtonHtml = array();

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
    	$this->setElement($element);
    	
    	$html = '<div class="grid" >';
    	$html .= '<table style="display:none">';
    	$html .= '<tbody id="ship2pay_template">';
    	$html .= $this->_getRowTemplateHtml();
    	$html .= '</tbody>';
    	$html .= '</table>';
    	$html .= '<table class="border" cellspacing="0" cellpadding="0">';
    	$html .= '<tbody id="ship2pay_container">';
    	$html .= '<tr class="headings">';
    	$html .= '<th>'.$this->__('Shipping method').'</th><th>'.$this->__('Payment method').'</th><th>&nbsp;</th>';
    	$html .= '</tr>';
    	if ($this->_getValue('shipping_method')) {
            foreach ($this->_getValue('shipping_method') as $i=>$f) {
                if ($i) {
                    $html .= $this->_getRowTemplateHtml($i);
                }
            }
        }
    	$html .= '</tbody>';
    	$html .= '</table>';
    	$html .= '</div>';
    	$html .= $this->_getAddRowButtonHtml('ship2pay_container', 'ship2pay_template', $this->__('Add combination'));

        return $html;	
    }
    
	protected function _getRowTemplateHtml($i=0)
    {
    	$storeID = Mage::app()->getStore(Mage_Core_Model_Store::ADMIN_CODE)->getId();
    	
    	//Get list of avaiable payment methods
    	$payment_methods = Mage::helper('ship2pay')->getPaymentMethodOptions();
    	
    	//Get list of avaiable shipping methods
    	$shipping_methods = Mage::helper('ship2pay')->getShipingMethodOptions();
    	
    	$html = '<tr>';
    	$html .= '<td>';
    	$html .= '<select id="ship2pay_shipping" class="option-control" style="width: 150px" value="" name="'.$this->getElement()->getName().'[shipping_method][]" >';
		$html .= '<option value=""></option>';    
		foreach($shipping_methods as $shipping_method)
		{
			if($shipping_method['value'] == $this->_getValue('shipping_method/'.$i))
				$html .= '<option value="'.$shipping_method['value'].'" selected>'.$shipping_method['label'].'</option>';
			else
				$html .= '<option value="'.$shipping_method['value'].'">'.$shipping_method['label'].'</option>';
		}
    	$html .= "</select>";
    	$html .= '</td>';
    	$html .= '<td>';
		$html .= '<select id="ship2pay_payment" class="option-control" style="width: 150px" value="" name="'.$this->getElement()->getName().'[payment_method][]" >';
		$html .= '<option value=""></option>';
		foreach($payment_methods as $payment_method)
		{
			//Mage::log('Payment Method: '.var_export($payment_method, true));
			if($payment_method['value'] == $this->_getValue('payment_method/'.$i))
				$html .= '<option value="'.$payment_method['value'].'" selected>'.$payment_method['label'].'</option>';
			else
				$html .= '<option value="'.$payment_method['value'].'">'.$payment_method['label'].'</option>';
		}       
    	$html .= "</select>";    	
    	$html .= '</td>';
    	$html .= '<td>'.$this->_getRemoveRowButtonHtml().'</td>';
    	$html .= '</tr>';
    	
        return $html;
    }    
	
    protected function _getDisabled()
    {
        return $this->getElement()->getDisabled() ? ' disabled' : '';
    }
    
	protected function _getValue($key)
    {
        return $this->getElement()->getData('value/'.$key);
    }    
    
	protected function _getAddRowButtonHtml($container, $template, $title='Add')
    {
        if (!isset($this->_addRowButtonHtml[$container])) {
            $this->_addRowButtonHtml[$container] = $this->getLayout()->createBlock('adminhtml/widget_button')
                    ->setType('button')
                    ->setClass('add '.$this->_getDisabled())
                    ->setLabel($this->__($title))
                    ->setOnClick("Element.insert($('".$container."'), {bottom: $('".$template."').innerHTML})")
                    ->setDisabled($this->_getDisabled())
                    ->toHtml();
        }
        return $this->_addRowButtonHtml[$container];
    }
    
	protected function _getRemoveRowButtonHtml($selector='tr', $title='Delete')
    {
        if (!$this->_removeRowButtonHtml) {
            $this->_removeRowButtonHtml = $this->getLayout()->createBlock('adminhtml/widget_button')
                    ->setType('button')
                    ->setClass('delete v-middle '.$this->_getDisabled())
                    ->setLabel($this->__($title))
                    ->setOnClick("Element.remove($(this).up('".$selector."'))")
                    ->setDisabled($this->_getDisabled())
                    ->toHtml();
        }
        return $this->_removeRowButtonHtml;
    }
}
?>