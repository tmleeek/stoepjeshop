<?php
/**
 * MageWorx
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MageWorx EULA that is bundled with
 * this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.mageworx.com/LICENSE-1.0.html
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@mageworx.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extension
 * to newer versions in the future. If you wish to customize the extension
 * for your needs please refer to http://www.mageworx.com/ for more information
 * or send an email to sales@mageworx.com
 *
 * @category   MageWorx
 * @package    MageWorx_Adminhtml
 * @copyright  Copyright (c) 2009 MageWorx (http://www.mageworx.com/)
 * @license    http://www.mageworx.com/LICENSE-1.0.html
 */

/**
 * MageWorx Adminhtml extension
 *
 * @category   MageWorx
 * @package    MageWorx_Adminhtml
 * @author     MageWorx Dev Team <dev@mageworx.com>
 */

class MageWorx_Adminhtml_Multifees_FeeController extends  Mage_Adminhtml_Controller_Action
{
	protected function _isAllowed()
    {
    	Mage::getSingleton('admin/session')->isAllowed('media');
    	Mage::getSingleton('admin/session')->isAllowed('sales/multifees');
        return $this;
    }

    protected function _initAction()
    {
    	$breadcrumb = $this->__('Fees Manager');
		$this->loadLayout()
			->_setActiveMenu('sales/multifees')
			->_addBreadcrumb($breadcrumb, $breadcrumb);

		return $this;
	}

    public function indexAction()
    {
		$this->_initAction()->renderLayout();
    }

    public function newAction()
    {
		$this->_forward('edit');
    }

	public function editAction()
    {
        $id = (int) $this->getRequest()->getParam('id');
        $model = Mage::getModel('multifees/fee')->load($id);

        if ($model->getId() || $id == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data)->setId($id);
            }
            Mage::register('multifees_data', $model);

            $this->_initAction();
            if ($this->getRequest()->getParam('checkout')) {
                $this->_addContent($this->getLayout()->createBlock('mageworx/multifees_fee_checkout_edit'));
            } else {
                $this->_addContent($this->getLayout()->createBlock('mageworx/multifees_fee_edit'));
            }
            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Fee does not exist'));
            $this->_redirect('*/*/');
        }
    }

	public function saveAction()
	{
	    $data     = $this->getRequest()->getPost();
	    $id       = (int) $this->getRequest()->getParam('id');
	    $checkout = (int) $this->getRequest()->getParam('checkout');
	    $error    = false;

	    if ($data) {
			$data = Mage::helper('multifees')->getFilter($data);

			$modelFee       = Mage::getSingleton('multifees/fee');
			$modelOption    = Mage::getSingleton('multifees/option');
			$modelStore     = Mage::getSingleton('multifees/store');
			$modelLngFee    = Mage::getSingleton('multifees/language_fee');
			$modelLngOption = Mage::getSingleton('multifees/language_option');

			if (empty($checkout)) {
	            $checkout = 0;
	        }

			try {
				if (!isset($data['option']) && !isset($data['fee']['checkout_type'])) {
					Mage::getSingleton('adminhtml/session')->addError($this->__('There are no options'));
					throw new Exception();
				}

                $checkoutMethod = null;
                if(!isset($data['fee']['apply_to'])) {
                    $data['fee']['apply_to'] = '';
                } else {
                    $data['fee']['apply_to'] = implode(',', $data['fee']['apply_to']);
                }

                if (isset($data['checkout_method'])) {
                    $checkoutMethod = implode(',', $data['checkout_method']);
                    $data['fee']['checkout_method'] = $checkoutMethod;
                }

	            $modelFee->setData($data['fee']);
	            if ($id) {
	            	$modelFee->setId($id);
	            }
				$modelFee->save();
				$feeId = $modelFee->getId();

				if ($feeId) {
					if ($data['stores']) {
						foreach ($data['stores'] as $storeId) {
							$modelStore->setData(array('mfs_fee_id' => $feeId, 'store_id' => $storeId));
							$modelStore->save();
						}
					}

					if ($data['titles']) {
						foreach ($this->_getStores() as $store) {
							$title = $data['titles'][$store->getStoreId()];
							if (!empty($title)) {
								$modelLngFee->setData(array(
									'mfl_fee_id' => $feeId,
									'store_id'   => $store->getStoreId(),
									'title'      => $title
								));
								$modelLngFee->save();
							}
						}
					}

					$option = $data['option'];
					if ($id) {
						$optionData = $modelOption->getResource()->getFee($id);
						$optionKeys = array_keys($optionData);
						$postKeys   = array_keys($option['title']);
						if ($optionKeys) {
							foreach ($optionKeys as $optionId) {
								if (!in_array($optionId, $postKeys)) {
									$modelOption->setId($optionId)->delete();
								}
							}
						}
					}
					if ($option['title']) {
						foreach ($option['title'] as $key => $value) {
							$default = null;
							if (isset($option['default'])) {
								foreach ($option['default'] as $def) {
									if ($def && $def == $key) {
										$default = 1;
									}
								}
							}
							$order = null;
							if (isset($option['order'][$key])) {
								$order = $option['order'][$key];
							}
							$modelOption->setData(array(
								'mfo_fee_id' => $feeId,
								'price'      => $option['price'][$key],
								'price_type' => $option['price_type'][$key],
								'is_default' => $default,
								'position'   => $order,
								)
							);
							if ($id && $optionKeys) {
								if (in_array($key, $optionKeys)) {
									$modelOption->setId($key);
								}
							}
							$modelOption->save();
							$optionId = $modelOption->getId();

							// Upload and Remove File
							if (isset($option['image_delete'][$optionId]) && $option['image_delete'][$optionId] == $optionId) {
								Mage::getSingleton('multifees/option')->removeOptionFile($optionId, false);
							}
							$this->_uploadImage('file_'. $key, $optionId);

							foreach ($this->_getStores() as $store) {
								if (!empty($value[$store->getStoreId()])) {
									$modelLngOption->setData(array(
										'fee_option_id' => $optionId,
										'store_id'      => $store->getStoreId(),
										'title'         => $value[$store->getStoreId()]
									));
									$modelLngOption->save();
								}
							}
						}
					}
				} else {
					Mage::getSingleton('adminhtml/session')->addError($this->__('Cannot add a record Fee. Please, try again.'));
					$error = true;
				}
				if ($error) {
					throw new Exception();
                }

				Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Fee was successfully saved'));
				Mage::getSingleton('adminhtml/session')->setFormData(false);

				if ($this->getRequest()->getParam('back')) {
					$this->_redirect('*/*/edit', array('id' => $feeId, 'checkout' => $checkout));
					return;
				}
				$this->_redirect('*/*/');
				return;
            } catch (Exception $e) {
            	if ($e->getMessage()) {
                	Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            	}
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $id, 'checkout' => $checkout));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError($this->__('Unable to find Fee to save'));
		$this->_redirect('*/*/');
	}

	private function _uploadImage($keyFile, $optionId)
	{
		if (isset($_FILES[$keyFile]['name']) && $_FILES[$keyFile]['name'] != '') {
			try {
				Mage::getSingleton('multifees/option')->removeOptionFile($optionId, false);

				$uploader = new Varien_File_Uploader($keyFile);
           		$uploader->setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png'));
				$uploader->setAllowRenameFiles(false);
				$uploader->setFilesDispersion(false);

				$uploader->save(Mage::helper('multifees')->getMultifeesPath($optionId), $_FILES[$keyFile]['name']);
			} catch (Exception $e) {
				if ($e->getMessage()) {
                	Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            	}
	        }
		}
	}

	public function getImageAction()
	{
		$optionId = (int) $this->getRequest()->getParam('option');
		return Mage::helper('multifees')->getImageView($optionId);
	}

	private function _getStores()
    {
		return Mage::getModel('core/store')
                ->getResourceCollection()
                ->setLoadDefault(true)
                ->load();
	}

	public function deleteAction()
	{
		$id = (int) $this->getRequest()->getParam('id');
		if ($id > 0) {
			try {
				$model = Mage::getModel('multifees/fee');
				$model->setId($id)->delete();

				Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Fee was successfully deleted'));
				$this->_redirect('*/*/');
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('id' => $id));
			}
		}
		$this->_redirect('*/*/');
	}

    public function massDeleteAction()
    {
        $feeIds = $this->getRequest()->getParam('fee');

        if (!is_array($feeIds)) {
			Mage::getSingleton('adminhtml/session')->addError($this->__('Please select Fee(s)'));
        } else {
            try {
                foreach ($feeIds as $feeId) {
                    $links = Mage::getModel('multifees/fee')->load($feeId);
                    $links->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Total of %d record(s) were successfully deleted', count($feeIds)));
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    public function massStatusAction()
    {
        $feeIds = $this->getRequest()->getParam('fee');

        if (!is_array($feeIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select Fee(s)'));
        } else {
            try {
                foreach ($feeIds as $feeId) {
                    Mage::getSingleton('multifees/fee')
                        ->load($feeId)
                        ->setStatus((int) $this->getRequest()->getParam('status'))
                        ->save();
                }
                $this->_getSession()->addSuccess($this->__('Total of %d record(s) were successfully updated', count($feeIds)));
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
}