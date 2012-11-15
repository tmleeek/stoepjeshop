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
class AW_Booking_Model_Checker_Bind extends AW_Core_Object {


    public function _construct() {
        Zend_Date::setOptions(array('extend_month' => true));
        return parent::_construct();
    }

    /**
     * Check if date can be binded
     * @param Zend_Date $Date
     * @param object    $product_id
     * @param object    $qty [optional]
     * @return bool
     */
    public function isQtyAvailable($product_id, Zend_Date $Date, $qty = 1, $includeCart = true) {
        return $this->isQtyAvailableForDate($product_id, $Date, $qty, $includeCart);
    }

    /**
     * Alias for $this->isQtyAvailable
     * @param <type> $product_id
     * @param Zend_Date $Date
     * @param <type> $qty
     * @param bool $includeCart
     */
    public function isQtyAvailableForDate($product_id, Zend_Date $Date, $qty = 1, $includeCart = true) {

        $_date = clone $Date;

        $model = Mage::getModel('catalog/product');
        if ($product_id instanceof $model) {
            $Product = $product_id;
        } else {
            $Product = $model->load($product_id);
        }

        if ($includeCart && $Quote = Mage::helper('checkout')->getQuote()) {
            $quoteId = $Quote->getId();
        } else {
            $quoteId = 0;
        }

        $Orders = Mage::getModel('booking/order')
        ->getCollection()
        ->addQuoteIdFilter($quoteId)
        ->addProductIdFilter($Product->getId());

        if ($Product->getAwBookingQratioMultiplier() == AW_Booking_Model_Entity_Attribute_Source_Qratiomultipliertype::HOURS) {
            $Orders->addBindDateTimeFilter($_date);
        } else {
            $Orders->addBindDateFilter($_date);
        }

        $total_binds = $Orders->count();

        if (!($bookingQty = $Product->getAwBookingQuantity())) {
            $Product = $Product->load($Product->getId());
            $bookingQty = $Product->getAwBookingQuantity();
        }

        return $total_binds <= ($bookingQty - $qty);
    }


    /**
     * Check if period if specified quantity is available for period
     * @param object    $product_id
     * @param Zend_Date $From
     * @param Zend_Date $To
     * @param object    $qty [optional]
     * @return bool
     */
    public function isQtyAvailableForPeriod($product_id, Zend_Date $_From, Zend_Date $_To, $qty = 1, $includeCart = true) {

        $From = clone $_From;
        $To = clone $_To;

        $model = Mage::getModel('catalog/product');
        if ($product_id instanceof $model) {
            $Product = $product_id;
        } else {
            $Product = $model->load($product_id);
        }

        if ($Product->getAwBookingQratioMultiplier() == AW_Booking_Model_Entity_Attribute_Source_Qratiomultipliertype::HOURS) {
            $method = 'addHour';
        } else {
            $method = 'addDayOfYear';
        }

        while ($this->compareDateOrTime($Product->getAwBookingRangeType(), $From, $To))
        {
            if (!$this->isQtyAvailable($Product, $From, $qty, $includeCart)) 
                return false;
            $From = call_user_func(array($From, $method), 1);
        }
        return true;
    }

    /**
     * Compares if $compareType is date, then use <= else (datetime and time) use <
     * @param string $compareType
     * @param Zend_Date $From
     * @param Zend_Date $To
     * @return bool 
     */

    private function compareDateOrTime($compareType, $From, $To)
    {
        if ($compareType == AW_Booking_Model_Entity_Attribute_Source_Rangetype::DATE)
            $compareResult = $From->compare($To) <= 0;
        else
            $compareResult = $From->compare($To) < 0;
        return $compareResult;
    }

    /**
     * Return unavailable dates as array
     * @param <type> $product_id
     * @param Zend_Date $_From
     * @param Zend_Date $_To
     * @param <type> $qty
     * @return array
     */
    public function getUnavailDays($Product, Zend_Date $_From, Zend_Date $_To, $qty = 1, $includeCart = true) {
        $dates = array();
        // Clone from and to to not affect original values
        $From = clone $_From;
        $To = clone $_To;
        while ($From->compare($To) <= 0) {
            $dates[$From->toString(AW_Core_Model_Abstract::DB_DATE_FORMAT)] = $this->isQtyAvailable($Product, $From, $qty, $includeCart);
            $From = $From->addDayOfYear(1);
        }

        return $dates;
    }
}