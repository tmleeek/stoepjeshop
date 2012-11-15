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
 * @package    MageWorx_MultiFees
 * @copyright  Copyright (c) 2009 MageWorx (http://www.mageworx.com/)
 * @license    http://www.mageworx.com/LICENSE-1.0.html
 */

/**
 * Multi Fees extension
 *
 * @category   MageWorx
 * @package    MageWorx_MultiFees
 * @author     MageWorx Dev Team <dev@mageworx.com>
 */

class MageWorx_MultiFees_Model_Mysql4_Fee extends Mage_Core_Model_Mysql4_Abstract
{
	public function _construct()
	{
	    $this->_init('multifees/fee', 'fee_id');
	}

	protected function _beforeDelete(Mage_Core_Model_Abstract $object)
	{
		$this->_setProcessDelete($object);
		$optionModel = Mage::getResourceSingleton('multifees/option');
	    $optionData = $optionModel->getFee($object->getId());
		if ($optionData) {
			$optionKeys = array_keys($optionData);
			$optionIds = implode(',', $optionKeys);

			$lngOptionModel = Mage::getResourceSingleton('multifees/language_option');
	    	$lngOptionModel->deleteOption($optionIds);
			foreach ($optionKeys as $optionId) {
				Mage::getSingleton('multifees/option')->removeOptionFile($optionId);
			}

	    	$optionModel->deleteFee($object->getId());
		}

		return parent::_beforeDelete($object);
	}

    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
    	$origData = $object->getOrigData();
    	if (!isset($origData)) {
			$this->_setProcessDelete($object);
    	}
        return parent::_afterSave($object);
    }

    private function _setProcessDelete(Mage_Core_Model_Abstract $object)
    {
    	$lngFeeModel = Mage::getResourceSingleton('multifees/language_fee');
    	$lngFeeModel->deleteFee($object->getId());

    	$storeModel = Mage::getResourceSingleton('multifees/store');
    	$storeModel->deleteFee($object->getId());

    	return $object;
    }

    protected function _afterLoad(Mage_Core_Model_Abstract $object)
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getTable('multifees/store'))
            ->where('mfs_fee_id = ?', $object->getId());

		$data = $this->_getReadAdapter()->fetchAll($select);
        if ($data) {
            $storesArray = array();
            foreach ($data as $row) {
                $storesArray[] = $row['store_id'];
            }
            $object->setData('store_id', $storesArray);
        }

        $checkoutMethod = $object->getData('checkout_method');
        if (!empty($checkoutMethod)) {
        	$checkoutMethod = explode(',', $checkoutMethod);
        	if ($object->getInputType() == MageWorx_MultiFees_Helper_Data::CHECKOUT_TYPE_PAYMENT) {
        		$object->setData('payment', $checkoutMethod);
        	} else {
                $object->setData('shipping', $checkoutMethod);
        	}
        }
        return parent::_afterLoad($object);
    }
}