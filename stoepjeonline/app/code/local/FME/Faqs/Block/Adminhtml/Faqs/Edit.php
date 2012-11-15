<?php
/**
 * Faqs extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 
 * @category   FME
 * @package    Faqs
 * @author     Kamran Rafiq Malik <kamran.malik@unitedsol.net>
 * @copyright  Copyright 2010 © free-magentoextensions.com All right reserved
 */

class FME_Faqs_Block_Adminhtml_Faqs_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
	
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'faqs';
        $this->_controller = 'adminhtml_faqs';
        
        $this->_updateButton('save', 'label', Mage::helper('faqs')->__('Save Faq'));
        $this->_updateButton('delete', 'label', Mage::helper('faqs')->__('Delete Faq'));
		
        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('faq_answar') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'faq_answar');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'faq_answar');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('faqs_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('faqs_data') && Mage::registry('faqs_data')->getId() ) {
            return Mage::helper('faqs')->__("Edit Faq '%s'", $this->htmlEscape(Mage::registry('faqs_data')->getTitle()));
        } else {
            return Mage::helper('faqs')->__('Add Faq');
        }
    }
}