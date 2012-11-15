<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_CatalogSearch
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Catalog Search Controller
 */
class Raptor_Supermenu_BrandsController extends Mage_Core_Controller_Front_Action
{
	/**
	 * Retrieve catalog session
	 *
	 * @return Mage_Catalog_Model_Session
	 */
	protected function _getSession()
	{
		return Mage::getSingleton('catalog/session');
	}

	public function indexAction()
	{
		$this->loadLayout();
		$this->_initLayoutMessages('catalogsearch/session');
		$this->renderLayout();
	}

	/**
	 * Display search result
	 */
	public function searchAction()
	{
		$query = $this->getRequest()->getQuery();

		if (is_null($query) || count($query) < 1) {
			$update = $this->getLayout()->getUpdate();
			$update->addHandle('all');
		} else {
			try {
				// N.B. We have rewritten catalogsearch/advanced to use Raptor_Supermenu_Model_Brands_Advanced model
				$model = Mage::getSingleton('supermenu/brands_advanced');
				$model->addFilters($this->getRequest()->getQuery());
			} catch (Mage_Core_Exception $e) {
				Mage::logException($e);
				Mage::getSingleton('catalogsearch/session')->addError($e->getMessage());
				$this->_redirectError(Mage::getURL('*/*/'));
			}
		}
		$this->loadLayout();
		$this->_initLayoutMessages('catalog/session');
		$this->renderLayout();
	}
}
