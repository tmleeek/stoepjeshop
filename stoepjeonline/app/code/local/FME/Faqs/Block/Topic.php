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
class FME_Faqs_Block_Topic extends Mage_Core_Block_Template
{
	
	public function _prepareLayout()
    {
    	
    	 if ( Mage::getStoreConfig('web/default/show_cms_breadcrumbs') && ($breadcrumbs = $this->getLayout()->getBlock('breadcrumbs')) ) {
            $breadcrumbs->addCrumb('home', array('label'=>Mage::helper('cms')->__('Home'), 'title'=>Mage::helper('cms')->__('Go to Home Page'), 'link'=>Mage::getBaseUrl()));
            $breadcrumbs->addCrumb('faqs_home', array('label' => Mage::helper('faqs')->getListPageTitle(), 'title' => Mage::helper('faqs')->getListPageTitle()));
        }
        
        if ($head = $this->getLayout()->getBlock('head')) {
            $head->setTitle(Mage::helper('faqs')->getListPageTitle());
            $head->setDescription(Mage::helper('faqs')->getListMetaDescription());
            $head->setKeywords(Mage::helper('faqs')->getListMetaKeywords());
        }
		
        return parent::_prepareLayout();
        
    }
    
     public function getTopics()     
     {	
		$collection = Mage::getModel('faqs/topic')->getCollection()
					->addStoreFilter(Mage::app()->getStore(true)->getId())
					->getData();
			
        if (!$this->hasData('topic')) {
            $this->setData('topic', $collection);
        }
        return $this->getData('topic');
        
    }
}