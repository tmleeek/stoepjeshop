<?php

class Devinc_Dailydeal_Adminhtml_DailydealController extends Mage_Adminhtml_Controller_action
{

	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('dailydeal/items')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Items Manager'), Mage::helper('adminhtml')->__('Item Manager'));
		
		return $this;
	}   
 
	public function indexAction() {
		$this->_initAction()
			->renderLayout();
	}

	public function editAction() {
		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('dailydeal/dailydeal')->load($id);

		if ($model->getId() || $id == 0) {
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (!empty($data)) {
				$model->setData($data);
			}

			Mage::register('dailydeal_data', $model);

			$this->loadLayout();
			$this->_setActiveMenu('dailydeal/items');

			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item Manager'), Mage::helper('adminhtml')->__('Item Manager'));
			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item News'), Mage::helper('adminhtml')->__('Item News'));

			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

			$this->_addContent($this->getLayout()->createBlock('dailydeal/adminhtml_dailydeal_edit'))
				->_addLeft($this->getLayout()->createBlock('dailydeal/adminhtml_dailydeal_edit_tabs'));

			$this->renderLayout();
		} else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('dailydeal')->__('Deal does not exist'));
			$this->_redirect('*/*/');
		}
	}
 
	public function newAction() {
		$this->_forward('edit');
	}
 
	public function saveAction() {
		if ($data = $this->getRequest()->getPost()) {
			$data = $this->_filterPostData($data);  			
	  			
			$model = Mage::getModel('dailydeal/dailydeal');		
			$model->setData($data)
				->setId($this->getRequest()->getParam('id'));
			
			try {				
				
				$model->save();
				
				$_product = Mage::getModel('catalog/product')->load($model->getProductId());	
				$stockItem = $_product->getStockItem();
				if ($stockItem->getIsInStock()) {
					$qty = 1;
				} else {
					$qty = 0;
				}
				
				$product_status = Mage::getModel('catalog/product')->load($model->getProductId())->getStatus();
			
				if ($qty!=0 && $product_status==1) {
					Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('dailydeal')->__('Deal was successfully saved'));
				} elseif ($product_status!=1) {
					Mage::getSingleton('adminhtml/session')->addError(Mage::helper('dailydeal')->__('Deal was saved & disabled because the product is disabled.'));		
					$model->setId($model->getId())
					  ->setStatus('2')
					  ->save();			
				} elseif ($qty==0) {
					Mage::getSingleton('adminhtml/session')->addError(Mage::helper('dailydeal')->__('Deal was saved & disabled because the product is out of stock.'));		
					$model->setId($model->getId())
					  ->setStatus('2')
					  ->save();			
				}
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
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('dailydeal')->__('Unable to find deal to save'));
        $this->_redirect('*/*/');
	}
 
	public function deleteAction() {
		if( $this->getRequest()->getParam('id') > 0 ) {
			try {
				$model = Mage::getModel('dailydeal/dailydeal');
				 
				$model->setId($this->getRequest()->getParam('id'))
					->delete();
					 
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Deal was successfully deleted'));
				$this->_redirect('*/*/');
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
			}
		}
		$this->_redirect('*/*/');
	}

    public function massDeleteAction() {
        $dailydealIds = $this->getRequest()->getParam('dailydeal');
        if(!is_array($dailydealIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                foreach ($dailydealIds as $dailydealId) {
                    $dailydeal = Mage::getModel('dailydeal/dailydeal')->load($dailydealId);
                    $dailydeal->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($dailydealIds)
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
        $dailydealIds = $this->getRequest()->getParam('dailydeal');
        if(!is_array($dailydealIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
        } else {
            try {
                foreach ($dailydealIds as $dailydealId) {
                    $dailydeal = Mage::getSingleton('dailydeal/dailydeal')
                        ->load($dailydealId)
                        ->setStatus($this->getRequest()->getParam('status'))
                        ->setIsMassupdate(true)
                        ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully updated', count($dailydealIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
  
    public function exportCsvAction()
    {
        $fileName   = 'dailydeal.csv';
        $content    = $this->getLayout()->createBlock('dailydeal/adminhtml_dailydeal_grid')
            ->getCsv();

        $this->_sendUploadResponse($fileName, strip_tags($content));
    }

    public function exportXmlAction()
    {
        $fileName   = 'dailydeal.xml';
        $content    = $this->getLayout()->createBlock('dailydeal/adminhtml_dailydeal_grid')
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
	
	protected function _filterPostData($data)
    {
        $data = $this->_filterDatesCustom($data, array('display_on'));
        return $data;
    }
	
	protected function _filterDatesCustom($array, $dateFields)
    {
        if (empty($dateFields)) {
            return $array;
        }
		
        foreach ($dateFields as $dateField) {
            if (array_key_exists($dateField, $array) && !empty($dateField)) {
                $array[$dateField] = $this->LocalizedToNormalized($array[$dateField]);
                $array[$dateField] = $this->NormalizedToLocalized($array[$dateField]);
            }
        }
        return $array;
    }
	
	public function LocalizedToNormalized($value)
    {
		if (substr(Mage::app()->getLocale()->getLocaleCode(),0,2)!='en') {
		    $dateFormatIso = Mage::app()->getLocale()->getDateFormat(
		  		Mage_Core_Model_Locale::FORMAT_TYPE_SHORT
		    );
	    } else {		
		    $dateFormatIso = Mage::app()->getLocale()->getDateFormat(
		  		Mage_Core_Model_Locale::FORMAT_TYPE_LONG
		    );
	    }
	
		$_options = array(
			'locale'      => Mage::app()->getLocale()->getLocaleCode(),
			'date_format' => $dateFormatIso,
			'precision'   => null
		);
        return Zend_Locale_Format::getDate($value, $_options);        
    }
	
	public function NormalizedToLocalized($value)
    {
        #require_once 'Zend/Date.php';
        $date = new Zend_Date($value, Mage::app()->getLocale()->getLocaleCode());
        return $date->toString('yyyy-MM-dd');       
    }
}