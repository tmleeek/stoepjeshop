<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 * 
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Booking
 * @copyright  Copyright (c) 2009-2010 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 */
class AW_Booking_Block_Checkout_Cart_Item_Renderer extends Mage_Checkout_Block_Cart_Item_Renderer{

	/**
	 * Return booking options array
	 * @return array
	 */
	protected function _getBookingOptions(){
		$product = $this->getProduct();
		$data = array(
			new Zend_Date($product->getCustomOption('aw_booking_from')->getValue()." ". $product->getCustomOption('aw_booking_time_from')->getValue(), AW_Core_Model_Abstract::DB_DATETIME_FORMAT),
			new Zend_Date($product->getCustomOption('aw_booking_to')->getValue()." ". $product->getCustomOption('aw_booking_time_to')->getValue(), AW_Core_Model_Abstract::DB_DATETIME_FORMAT)
		);
		
		return array(
			array('label' => $this->__('From'), 'value' => $this->formatDate($data[0], 'short', $this->getProduct()->getAwBookingRangeType() != 'date_fromto')),
			array('label' => $this->__('To'), 'value' => $this->formatDate($data[1], 'short', $this->getProduct()->getAwBookingRangeType() != 'date_fromto'))
		);
	}

    /**
     * Return merged options array
     * This array consist of standard Magento options and booking
     * @return array
     */
    public function getOptionList(){
        return array_merge($this->_getBookingOptions(), parent::getOptionList());
    }
 
}
