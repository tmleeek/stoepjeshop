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
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Customer new password field renderer
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Devinc_Dailydeal_Block_Adminhtml_Dailydeal_Edit_Renderer_Product extends Mage_Adminhtml_Block_Abstract implements Varien_Data_Form_Element_Renderer_Interface
{

    public function render(Varien_Data_Form_Element_Abstract $element) 
	{			  
        $element->setData('onchange', 'document.getElementById(\'product_edit\').style.display = \'block\';document.getElementById(\'product_edit\').href=\''.Mage::getBaseUrl().'admin/catalog_product/edit/id/\';document.getElementById(\'product_edit\').href+=this.value');
        $html = '<tr><td class="label"><label for="product">Product <span class="required">*</span></label></td><td class="value">'.$element->getElementHtml().'<p class="note"><span><a style="display:none;" target="blank" id="product_edit" href="'.Mage::getBaseUrl().'admin/catalog_product/edit/id/">Edit product</a></span></p></td></tr>';    
		if ($this->getRequest()->getParam('id')) {
			$html .= '<script type="text/javascript">document.getElementById(\'product_edit\').style.display = \'block\';document.getElementById(\'product_edit\').href+=document.getElementById(\'product_id\').value;</script>';    
		}
        return $html;
    }	
	

}
