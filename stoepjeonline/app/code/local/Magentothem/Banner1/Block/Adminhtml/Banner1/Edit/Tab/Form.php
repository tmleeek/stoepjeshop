<?php
/*------------------------------------------------------------------------
# Websites: http://www.magentothem.com/
			http://www.plazathemes.com/
-------------------------------------------------------------------------*/ 
class Magentothem_Banner1_Block_Adminhtml_Banner1_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('banner1_form', array('legend'=>Mage::helper('banner1')->__('Item information')));
     
      $fieldset->addField('title', 'text', array(
          'label'     => Mage::helper('banner1')->__('Title'),
          'required'  => false,
          'name'      => 'title',
      ));
	  
	  $fieldset->addField('link', 'text', array(
          'label'     => Mage::helper('banner1')->__('Link'),
          'required'  => false,
          'name'      => 'link',
      ));

      $fieldset->addField('image', 'file', array(
          'label'     => Mage::helper('banner1')->__('Image'),
          'required'  => false,
          'name'      => 'image',
	  ));
		
      $fieldset->addField('status', 'select', array(
          'label'     => Mage::helper('banner1')->__('Status'),
          'name'      => 'status',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('banner1')->__('Enabled'),
              ),

              array(
                  'value'     => 2,
                  'label'     => Mage::helper('banner1')->__('Disabled'),
              ),
          ),
      ));
     
      $fieldset->addField('description', 'editor', array(
          'name'      => 'description',
          'label'     => Mage::helper('banner1')->__('Description'),
          'title'     => Mage::helper('banner1')->__('Description'),
          'style'     => 'width:700px; height:500px;',
          'wysiwyg'   => false,
          'required'  => false,
      ));
     
      if ( Mage::getSingleton('adminhtml/session')->getBanner1Data() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getBanner1Data());
          Mage::getSingleton('adminhtml/session')->setBanner1Data(null);
      } elseif ( Mage::registry('banner1_data') ) {
          $form->setValues(Mage::registry('banner1_data')->getData());
      }
      return parent::_prepareForm();
  }
}