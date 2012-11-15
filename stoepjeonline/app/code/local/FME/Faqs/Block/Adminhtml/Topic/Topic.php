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
class FME_Faqs_Block_Adminhtml_Topic extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'faqs';
        $this->_controller = 'adminhtml_topic';
        
        $this->_updateButton('save', 'label', Mage::helper('faqs')->__('Save Topic'));
        $this->_updateButton('delete', 'label', Mage::helper('faqs')->__('Delete Topic'));
		
    }

    public function getHeaderText()
    {
        if( Mage::registry('topic_data') ) {
            return Mage::helper('faqs')->__("Edit Topic");
        }
    }
}