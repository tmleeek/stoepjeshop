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
class AW_Booking_Model_Order extends Mage_Core_Model_Abstract{

	/** Indicates if record is only cart entry */
	const BIND_TYPE_CART = "cart";
	/** Indicates if record is order entry */
	const BIND_TYPE_ORDER = "order";

	protected $_product;
	
	protected function _construct(){
		$this->_init('booking/order');
	}
	
	public function getProduct(){
		if(!$this->_product){
			$this->_product = Mage::getModel('catalog/product')->load($this->getProductId());
		}
		return $this->_product;
	}
}
