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
 
class FME_Faqs_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction() 
	{
		$this->loadLayout();		
		$this->renderLayout();
    }
        
    public function viewAction()
   {
	   
		$post = $this->getRequest()->getPost();
		if($post){
			$sterm=$post['faqssearch'];
			$this->_redirect('*/*/search', array('term' => $sterm));
				return;   
		}
		
		$topicId = $this->_request->getParam('id', null);
	
    	if ( is_numeric($topicId) ) {
			
			$faqsTable = Mage::getSingleton('core/resource')->getTableName('faqs');
			$faqsTopicTable = Mage::getSingleton('core/resource')->getTableName('faqs_topics');
			$faqsStoreTable = Mage::getSingleton('core/resource')->getTableName('faqs_store');
		
			$sqry = "select f.*,t.title as cat from ".$faqsTable." f, ".$faqsTopicTable." t where f.topic_id='$topicId' and f.status=1 and t.topic_id='$topicId'"; 
			$connection = Mage::getSingleton('core/resource')->getConnection('core_read');
			$select = $connection->query($sqry);
			$collection = $select->fetchAll();
			if(count($collection) != 0){
				Mage::register('faqs', $collection);
			} else {
				Mage::register('faqs', NULL); 
			}
			
    	} else {
			
			Mage::register('faqs', NULL); 
		}
		
		$this->loadLayout();   
		$this->renderLayout();	
    }
    
    public function searchAction()
    {
    	
		$faqsTable = Mage::getSingleton('core/resource')->getTableName('faqs');
		$faqsTopicTable = Mage::getSingleton('core/resource')->getTableName('faqs_topics');
		$faqsStoreTable = Mage::getSingleton('core/resource')->getTableName('faqs_store');
		
		$sterm = $this->getRequest()->getParam('term');
		$post = $this->getRequest()->getPost();
		if($post){  
			$sterm=$post['faqssearch'];    
		}
		
		if(isset($sterm)){
			$sqry = "select * from ".$faqsTable." f,".$faqsStoreTable." fs where (f.title like '%$sterm%' or f.faq_answar like '%$sterm%') and (status=1)
			and f.topic_id = fs.topic_id
			and (fs.store_id =".Mage::app()->getStore()->getId()." OR fs.store_id=0)";
			$connection = Mage::getSingleton('core/resource')->getConnection('core_read');
			$select = $connection->query($sqry);
			$sfaqs = $select->fetchAll();
			if(count($sfaqs) != 0){
				Mage::register('faqs', $sfaqs);
			} 
		}
		$this->loadLayout();   
		$this->renderLayout();

    }

    public function topicsAction()
    {
		$this->loadLayout();   
		$this->renderLayout();
    }
 
}
