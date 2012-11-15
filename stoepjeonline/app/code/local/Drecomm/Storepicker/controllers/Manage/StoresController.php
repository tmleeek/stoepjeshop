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

class Drecomm_Storepicker_Manage_StoresController extends Mage_Adminhtml_Controller_Action
{
	public function preDispatch()
    {
        parent::preDispatch();
    }
	
	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('storepicker/stores');
		
		return $this;
	}
 
	public function indexAction() {
		$this->_initAction()
			->renderLayout();
	}

	public function editAction() {
		
		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('storepicker/store')->load($id);

		if ($model->getId() || $id == 0) {
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (!empty($data)) {
				$model->setData($data);
			}

			Mage::register('storepicker_data', $model);

			$this->loadLayout();
			$this->_setActiveMenu('storepicker/stores');

			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);

			$this->renderLayout();
		} else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('blog')->__('Post does not exist'));
			$this->_redirect('*/*/');
		}
	}
 
	public function newAction() {
		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('storepicker/store')->load($id);

		$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
		if (!empty($data)) {
			$model->setData($data);
		}

		Mage::register('blog_data', $model);

		$this->loadLayout();
		$this->_setActiveMenu('storepicker/stores');

		$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

        $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);

		$this->renderLayout();
	}
 
	public function saveAction() {
		if ($data = $this->getRequest()->getPost()) {
			$model = Mage::getModel('storepicker/store');	

			//die(print_r($data, 1));
			
			$model->setData($data)
				->setId($this->getRequest()->getParam('id'));
			
			try {
				$model->save();
				
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('storepicker')->__('Store was successfully saved'));
				Mage::getSingleton('adminhtml/session')->setFormData(false);

				if ($this->getRequest()->getParam('back')) {
					$this->_redirect('*/*/edit', array('id' => $model->getId()));
					return;
				}
				$this->_redirect('*/*/');
				return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('storepicker')->__('Unable to find store to save'));
        $this->_redirect('*/*/');
	}
 
	public function deleteAction() {
		if( $this->getRequest()->getParam('id') > 0 ) {
			try {
				$model = Mage::getModel('storepicker/store');
				 
				$model->setId($this->getRequest()->getParam('id'))->delete();
					 
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Store was successfully deleted'));
				$this->_redirect('*/*/');
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
			}
		}
		$this->_redirect('*/*/');
	}

    public function massDeleteAction() {
        $blogIds = $this->getRequest()->getParam('blog');
        if(!is_array($blogIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select post(s)'));
        } else {
            try {
                foreach ($blogIds as $blogId) {
                    $blog = Mage::getModel('blog/blog')->load($blogId);
                    $blog->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($blogIds)
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
        $blogIds = $this->getRequest()->getParam('blog');
        if(!is_array($blogIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select post(s)'));
        } else {
            try {

                foreach ($blogIds as $blogId) {
                    $blog = Mage::getModel('blog/blog')
                        ->load($blogId)
                        ->setStatus($this->getRequest()->getParam('status'))
                        ->setStores('')
                        ->setIsMassupdate(true)
                        ->save();
 
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully updated', count($blogIds))
                );
            } catch (Exception $e) {
            	
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
}
