<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/LICENSE-L.txt
 *
 * @category   AW
 * @package    AW_Blog
 * @copyright  Copyright (c) 2009-2010 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/LICENSE-L.txt
 */

class Drecomm_Storepicker_Block_Manage_Stores_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(
                                   array(
                                      'id' => 'edit_form',
                                      'action' => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
                                      'method' => 'post',
                                   ));
        $form->setUseContainer(true);
        $this->setForm($form);
        $fieldset = $form->addFieldset('storepicker_form', array('legend'=>Mage::helper('storepicker')->__('Post information')));

        $fieldset->addField('customerno', 'text', array(
          'label'     => Mage::helper('storepicker')->__('Customer No.'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'customerno',
        ));

        $fieldset->addField('name', 'text', array(
          'label'     => Mage::helper('storepicker')->__('Name'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'name',
        ));

        $fieldset->addField('street', 'text', array(
          'label'     => Mage::helper('storepicker')->__('Street'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'street',
        ));

        $fieldset->addField('housenr', 'text', array(
          'label'     => Mage::helper('storepicker')->__('House number'),
          'required'  => false,
          'name'      => 'housenr',
        ));

        $fieldset->addField('postal', 'text', array(
          'label'     => Mage::helper('storepicker')->__('Postal'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'postal',
        ));

        $fieldset->addField('city', 'text', array(
          'label'     => Mage::helper('storepicker')->__('City'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'city',
        ));

        $fieldset->addField('country', 'text', array(
          'label'     => Mage::helper('storepicker')->__('Country'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'country',
        ));

        $fieldset->addField('phone', 'text', array(
          'label'     => Mage::helper('storepicker')->__('Phone number'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'phone',
        ));

        $fieldset->addField('email', 'text', array(
          'label'     => Mage::helper('storepicker')->__('E-mail address'),
          'required'  => false,
          'name'      => 'email',
        ));

        $fieldset->addField('textualpos', 'textarea', array(
          'label'     => Mage::helper('storepicker')->__('Textual position'),
          'required'  => false,
          'name'      => 'textualpos',
        ));

        $fieldset->addField('banknr', 'text', array(
          'label'     => Mage::helper('storepicker')->__('Bank number'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'banknr',
        ));

        $fieldset->addField('pickup', 'select', array(
          'label'     => Mage::helper('storepicker')->__('Can pickup'),
          'name'      => 'pickup',
          'values'    => array(
            array(
              'value'     => 0,
              'label'     => Mage::helper('storepicker')->__('No'),
            ),
            array(
              'value'     => 1,
              'label'     => Mage::helper('storepicker')->__('Yes'),
            ),
          ),
        ));

        $fieldset->addField('send', 'select', array(
          'label'     => Mage::helper('storepicker')->__('Can send'),
          'name'      => 'send',
          'values'    => array(
            array(
              'value'     => 0,
              'label'     => Mage::helper('storepicker')->__('No'),
            ),
            array(
              'value'     => 1,
              'label'     => Mage::helper('storepicker')->__('Yes'),
            ),
          ),
        ));

        $fieldset->addField('sendcosts', 'text', array(
          'label'     => Mage::helper('storepicker')->__('Send costs'),
          'name'      => 'sendcosts',
        ));

        $fieldset->addField('ghostfrom', 'text', array(
          'label'     => Mage::helper('storepicker')->__('Ghost from'),
          'name'      => 'ghostfrom',
        ));

        $fieldset->addField('opensunday', 'text', array(
          'label'     => Mage::helper('storepicker')->__('Open on Sunday'),
          'class'     => 'required-entry',
          'required'  => false,
          'name'      => 'opensunday',
        ));

        $fieldset->addField('openmonday', 'text', array(
          'label'     => Mage::helper('storepicker')->__('Open on Monday'),
          'class'     => 'required-entry',
          'required'  => false,
          'name'      => 'openmonday',
        ));

        $fieldset->addField('opentuesday', 'text', array(
          'label'     => Mage::helper('storepicker')->__('Open on Tuesday'),
          'class'     => 'required-entry',
          'required'  => false,
          'name'      => 'opentuesday',
        ));

        $fieldset->addField('openwednesday', 'text', array(
          'label'     => Mage::helper('storepicker')->__('Open on Wednesday'),
          'class'     => 'required-entry',
          'required'  => false,
          'name'      => 'openwednesday',
        ));

        $fieldset->addField('openthursday', 'text', array(
          'label'     => Mage::helper('storepicker')->__('Open on Thursday'),
          'class'     => 'required-entry',
          'required'  => false,
          'name'      => 'openthursday',
        ));

        $fieldset->addField('openfriday', 'text', array(
          'label'     => Mage::helper('storepicker')->__('Open on Friday'),
          'class'     => 'required-entry',
          'required'  => false,
          'name'      => 'openfriday',
        ));

        $fieldset->addField('opensaturday', 'text', array(
          'label'     => Mage::helper('storepicker')->__('Open on Saturday'),
          'class'     => 'required-entry',
          'required'  => false,
          'name'      => 'opensaturday',
        ));

        $fieldset->addField('sendradius', 'text', array(
          'label'     => Mage::helper('storepicker')->__('Send radius'),
          'class'     => 'required-entry',
          'required'  => false,
          'name'      => 'sendradius',
        ));

        $fieldset->addField('sendsunday', 'text', array(
          'label'     => Mage::helper('storepicker')->__('Send on Sunday'),
          'class'     => 'required-entry',
          'required'  => false,
          'name'      => 'sendsunday',
        ));

        $fieldset->addField('sendmonday', 'text', array(
          'label'     => Mage::helper('storepicker')->__('Send on Monday'),
          'class'     => 'required-entry',
          'required'  => false,
          'name'      => 'sendmonday',
        ));

        $fieldset->addField('sendtuesday', 'text', array(
          'label'     => Mage::helper('storepicker')->__('Send on Tuesday'),
          'class'     => 'required-entry',
          'required'  => false,
          'name'      => 'sendtuesday',
        ));

        $fieldset->addField('sendwednesday', 'text', array(
          'label'     => Mage::helper('storepicker')->__('Send on Wednesday'),
          'class'     => 'required-entry',
          'required'  => false,
          'name'      => 'sendwednesday',
        ));

        $fieldset->addField('sendthursday', 'text', array(
          'label'     => Mage::helper('storepicker')->__('Send on Thursday'),
          'class'     => 'required-entry',
          'required'  => false,
          'name'      => 'sendthursday',
        ));

        $fieldset->addField('sendfriday', 'text', array(
          'label'     => Mage::helper('storepicker')->__('Send on Friday'),
          'class'     => 'required-entry',
          'required'  => false,
          'name'      => 'sendfriday',
        ));

        $fieldset->addField('sendsaturday', 'text', array(
          'label'     => Mage::helper('storepicker')->__('Send on Saturday'),
          'class'     => 'required-entry',
          'required'  => false,
          'name'      => 'sendsaturday',
        ));

        $fieldset->addField('longitude', 'text', array(
          'label'     => Mage::helper('storepicker')->__('Longitude'),
          'class'     => 'required-entry',
          'required'  => false,
          'name'      => 'longitude',
        ));

        $fieldset->addField('latitude', 'text', array(
          'label'     => Mage::helper('storepicker')->__('Latitude'),
          'class'     => 'required-entry',
          'required'  => false,
          'name'      => 'latitude',
        ));

        if ( Mage::getSingleton('adminhtml/session')->getStorepickerData() )
        {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getStorepickerData());
          Mage::getSingleton('adminhtml/session')->setBlogData(null);
        } elseif ( Mage::registry('storepicker_data') ) {
          $form->setValues(Mage::registry('storepicker_data')->getData());
        }
        return parent::_prepareForm();
  }
}
