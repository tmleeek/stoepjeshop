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

class FME_Faqs_Adminhtml_TopicController extends Mage_Adminhtml_Controller_action
{

	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('faqs/topic')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Manage Topics'), Mage::helper('adminhtml')->__('Manage Topics'));
		
		return $this;
	}   
 
	public function indexAction() {
		$this->_initAction();        
		$this->renderLayout();
      } 

	public function editAction() {
		
		
		$this->loadLayout();
		$this->_setActiveMenu('faqs/topic');
		$this->_addBreadcrumb(Mage::helper('faqs')->__('Manage Topics'), Mage::helper('faqs')->__('Manage Topics'));
		

		$id = $this->getRequest()->getParam('id');
		if ($id > 0) {
			
			$model  = Mage::getModel('faqs/topic')->load($id);
			Mage::register('topic_data', $model);
		}
		$this->_addContent($this->getLayout()->createBlock('faqs/adminhtml_topic_edit'))
			 ->_addLeft($this->getLayout()->createBlock('faqs/adminhtml_topic_edit_tabs'));
		$this->renderLayout();
	}
 
	public function newAction() {
		$this->_forward('edit');
	}
 
	public function saveAction() {
			
		$id=$this->getRequest()->getParam('id');
		$data = $this->getRequest()->getPost();
	
		$is_new=false;
		if ($id == 0)
			$is_new=true;
		try {

			// New Cat
			if ($is_new){
				$cat= Mage::getModel('faqs/topic');
				$cat->setData($data);
				$cat->setCreatedTime(now());
				$cat->setUpdateTime(now());
				$cat->save();		
			}
			// Existing Cat
			else{
				$cat=Mage::getModel('faqs/topic')->load($id);
				$cat->setData($data);
				$cat->setData('topic_id',$id);
				$cat->setUpdateTime(now());
				$cat->save();
			}
								
			Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('faqs')->__('Topic was successfully saved'));

        	if ($is_new)
				$this->_redirect('*/*/', array('id' => $id));
			else
				$this->_redirect('*/*/', array('id' => $id));
	
		} catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            Mage::getSingleton('adminhtml/session')->setFormData($data);
            $this->_redirect('*/*/', array('id' => $id));
	
	}
	}
 
	public function deleteAction() {
		if( $this->getRequest()->getParam('id') > 0 ) {
			try {
				$model = Mage::getModel('faqs/topic');
				 
				$model->setId($this->getRequest()->getParam('id'))
					->delete();
					 
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Topic was successfully deleted'));
				$this->_redirect('*/*/');
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
			}
		}
		$this->_redirect('*/*/');
	}

    public function massDeleteAction() {
        $faqsIds = $this->getRequest()->getParam('faqs');
        if(!is_array($faqsIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select Topic(s)'));
        } else {
            try {
                foreach ($faqsIds as $faqsId) {
                    $faqs = Mage::getModel('faqs/topic')->load($faqsId);
                    $faqs->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($faqsIds)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
    
    public function massStatusAction()
    {
        $faqsIds = $this->getRequest()->getParam('faqs');
        if(!is_array($faqsIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select Topic(s)'));
        } else {
            try {
                foreach ($faqsIds as $faqsId) {
                    $faqs = Mage::getSingleton('faqs/faqs')
                        ->load($faqsId)
                        ->setStatus($this->getRequest()->getParam('status'))
                        ->setIsMassupdate(true)
                        ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully updated', count($faqsIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
	
    
    public function exportCsvAction()
    {
        $fileName   = 'faqs.csv';
        $content    = $this->getLayout()->createBlock('faqs/adminhtml_faqs_grid')
            ->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction()
    {
        $fileName   = 'faqs.xml';
        $content    = $this->getLayout()->createBlock('faqs/adminhtml_faqs_grid')
            ->getXml();

        $this->_sendUploadResponse($fileName, $content);
    }

    protected function _sendUploadResponse($fileName, $content, $contentType='application/octet-stream')
    {
        $response = $this->getResponse();
        $response->setHeader('HTTP/1.1 200 OK','');
        $response->setHeader('Pragma', 'public', true);
        $response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        $response->setHeader('Content-Disposition', 'attachment; filename='.$fileName);
        $response->setHeader('Last-Modified', date('r'));
        $response->setHeader('Accept-Ranges', 'bytes');
        $response->setHeader('Content-Length', strlen($content));
        $response->setHeader('Content-type', $contentType);
        $response->setBody($content);
        $response->sendResponse();
        die;
    }
}