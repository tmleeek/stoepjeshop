<?php
/**
 * MageWorx
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MageWorx EULA that is bundled with
 * this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.mageworx.com/LICENSE-1.0.html
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@mageworx.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extension
 * to newer versions in the future. If you wish to customize the extension
 * for your needs please refer to http://www.mageworx.com/ for more information
 * or send an email to sales@mageworx.com
 *
 * @category   MageWorx
 * @package    MageWorx_Adminhtml
 * @copyright  Copyright (c) 2009 MageWorx (http://www.mageworx.com/)
 * @license    http://www.mageworx.com/LICENSE-1.0.html
 */

/**
 * MageWorx Adminhtml extension
 *
 * @category   MageWorx
 * @package    MageWorx_Adminhtml
 * @author     MageWorx Dev Team <dev@mageworx.com>
 */

class MageWorx_Adminhtml_Block_Multifees_Fee_Checkout_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->_objectId   = 'id';
        $this->_blockGroup = 'mageworx';
        $this->_controller = 'multifees_fee';
        $this->_mode       = 'checkout_edit';

        $this->_updateButton('save', 'label', Mage::helper('multifees')->__('Save Checkout Fee'));
        $this->_updateButton('delete', 'label', Mage::helper('multifees')->__('Delete Checkout Fee'));

        $this->_addButton('saveandcontinue', array(
            'label'   => Mage::helper('multifees')->__('Save And Continue Edit'),
            'onclick' => 'saveAndContinueEdit()',
            'class'   => 'save'
        ), -100);

        $this->_formScripts[] = "
            function saveAndContinueEdit() {
                editForm.submit($('edit_form').action + 'back/edit/checkout/1/');
            }
        ";
    }

    public function getHeaderText()
    {
        if (Mage::registry('multifees_data') && Mage::registry('multifees_data')->getId()) {
            return Mage::helper('multifees')->__("Edit Cart Fee '%s'", $this->htmlEscape(Mage::getSingleton('multifees/language_fee')->load(Mage::registry('multifees_data')->getId(), 'mfl_fee_id')->getTitle()));
        } else {
            return Mage::helper('multifees')->__('Add Cart Fee');
        }
    }
}