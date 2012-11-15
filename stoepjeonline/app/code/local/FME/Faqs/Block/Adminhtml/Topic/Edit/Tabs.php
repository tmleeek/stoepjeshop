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

class FME_Faqs_Block_Adminhtml_Topic_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('topic_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('faqs')->__('Topics Management'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('faqs')->__('Topic Information'),
          'title'     => Mage::helper('faqs')->__('Topic Information'),
          'content'   => $this->getLayout()->createBlock('faqs/adminhtml_topic_edit_tab_form')->toHtml(),
      ));
     
         
      return parent::_beforeToHtml();
  }
}