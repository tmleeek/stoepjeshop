<?php
/*------------------------------------------------------------------------
# Websites: http://www.magentothem.com/
			http://www.plazathemes.com/
-------------------------------------------------------------------------*/ 
class Magentothem_Banner1_Block_Adminhtml_Banner1_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'banner1';
        $this->_controller = 'adminhtml_banner1';
        
        $this->_updateButton('save', 'label', Mage::helper('banner1')->__('Save Item'));
        $this->_updateButton('delete', 'label', Mage::helper('banner1')->__('Delete Item'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('banner1_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'banner1_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'banner1_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('banner1_data') && Mage::registry('banner1_data')->getId() ) {
            return Mage::helper('banner1')->__("Edit Item '%s'", $this->htmlEscape(Mage::registry('banner1_data')->getTitle()));
        } else {
            return Mage::helper('banner1')->__('Add Item');
        }
    }
}