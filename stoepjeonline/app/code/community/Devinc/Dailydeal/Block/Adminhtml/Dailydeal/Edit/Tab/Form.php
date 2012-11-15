<?php

class Devinc_Dailydeal_Block_Adminhtml_Dailydeal_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('dailydeal_form', array('legend'=>Mage::helper('dailydeal')->__('Deal information')));
           
	  $visibility = array(2, 4);
	  $productCollection = Mage::getResourceModel('catalog/product_collection')
				->addAttributeToSelect('entity_id')
				->addAttributeToSelect('name')
				->addAttributeToSelect('sku')
				->addAttributeToSort('sku', 'asc')
				->addAttributeToFilter('visibility', $visibility)
				->load();
	  
      $products = array();
           
	  $products[] = array(
                'label' => '--- Select product ---',
                'value' => ''
            );   
			
	  foreach ($productCollection as $product) {
            $products[] = array(
                'label' => 'SKU ['.$product->sku.'] - '.$product->getName().'',
                'value' => $product->entity_id
            );
      }

      $field = $fieldset->addField('product_id', 'select', array(
            'label'     => Mage::helper('dailydeal')->__('Product'),
            'name'      => 'product_id',
            'class'     => 'required-entry validate-select',
            'required'  => true,
            'values'    => $products,
      ));		  	
	
	  $field->setRenderer($this->getLayout()->createBlock('dailydeal/adminhtml_dailydeal_edit_renderer_product'));
			
	  if (substr(Mage::app()->getLocale()->getLocaleCode(),0,2)!='en') {
		  $dateFormatIso = Mage::app()->getLocale()->getDateFormat(
				Mage_Core_Model_Locale::FORMAT_TYPE_SHORT
		  );
	  } else {		
		  $dateFormatIso = Mage::app()->getLocale()->getDateFormat(
				Mage_Core_Model_Locale::FORMAT_TYPE_LONG
		  );
	  }
	  
	  $fieldset->addField('display_on', 'date', array(
          'name'      => 'display_on',
          'class'     => 'required-entry',
          'label'     => Mage::helper('dailydeal')->__('Display on'),
          'image'     => $this->getSkinUrl('images/grid-cal.gif'),
          'required'  => true,
          'format'    => $dateFormatIso
      ));
	  
      /* $fieldset->addField('display_from', 'date', array(
          'name'      => 'display_from',
          'class'     => 'required-entry',
          'label'     => Mage::helper('dailydeal')->__('Display from'),
          'image'     => $this->getSkinUrl('images/grid-cal.gif'),
          'required'  => true,
          'format'    => $dateFormatIso
      ));

      $fieldset->addField('display_to', 'date', array(
          'name'      => 'display_to',
          'class'     => 'required-entry',
          'label'     => Mage::helper('dailydeal')->__('Display to'),
          'image'     => $this->getSkinUrl('images/grid-cal.gif'),
          'required'  => true,
          'format'    => $dateFormatIso
      ));	  */

	  /* $field = $fieldset->addField('max_qty', 'text', array(
            'label'     => Mage::helper('dailydeal')->__('Maximum Qty'),
            'name'      => 'max_qty'
      ));
	
	  $field->setRenderer($this->getLayout()->createBlock('dailydeal/adminhtml_dailydeal_edit_renderer_qty')); */
		
	  $fieldset->addField('disable', 'select', array(
          'label'     => Mage::helper('dailydeal')->__('Disable product after deal ends'),
          'name'      => 'disable',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('dailydeal')->__('No'),
              ),

              array(
                  'value'     => 2,
                  'label'     => Mage::helper('dailydeal')->__('Yes'),
              ),
          ),
		  'note'     => 'If Yes - the product will be disabled from the catalog &amp; search after the deal ends to prevent it from appearing on search engines.'
      ));	
		
      $fieldset->addField('status', 'select', array(
          'label'     => Mage::helper('dailydeal')->__('Deal status'),
          'name'      => 'status',
          'class'     => 'required-entry validate-select',
          'required'  => true,
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('dailydeal')->__('Enabled'),
              ),

              array(
                  'value'     => 2,
                  'label'     => Mage::helper('dailydeal')->__('Disabled'),
              ),
          ),
      ));
	  
     
      if ( Mage::getSingleton('adminhtml/session')->getDailydealData() ) {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getDailydealData());
          Mage::getSingleton('adminhtml/session')->setDailydealData(null);
      } elseif ( Mage::registry('dailydeal_data') ) {
          $form->setValues(Mage::registry('dailydeal_data')->getData());
      }
      return parent::_prepareForm();
  }
}