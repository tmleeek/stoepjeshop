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
class MageWorx_MultiFees_Model_Observer {

    protected function _getCheckoutSession() {
        return Mage::getSingleton('checkout/session');
    }

    protected function _getHelper() {
        return Mage::helper('multifees');
    }

    public function unsetCustomerMultifees(Varien_Event_Observer $observer) {
        $session = $this->_getCheckoutSession();

        if (!$this->_getHelper()->isOldVersion()) {
            if ($session->getMultifees()) {
                Mage::getModel('sales/order')->getResource()
                        ->setMultifeesAmount($session->getMultifees(), $session->getLastRealOrderId());
            }
        }

        $session->setMultifees();
        $session->setDetailsMultifees();
        $session->setStoreMultifees();
        $session->setOnlyOneAutoadd(false);
    }

    public function autoaddMultifees(Varien_Event_Observer $observer) {
        $helper = $this->_getHelper();
        $session = $this->_getCheckoutSession();
        $itemQty = $session->getQuote()->getItemsQty();
        $onlyOne = $session->getOnlyOneAutoadd();
        if (empty($onlyOne)) {
            $session->setOnlyOneAutoadd(false);
        }

        if ($helper->isAutoaddTotal() && $itemQty && $session->getOnlyOneAutoadd() === false) {
            $feeCollection = Mage::getResourceModel('multifees/fee_collection')
                            ->addFeeStoreFilter()
                            ->addStatusFilter()
                            ->addCheckoutTypeFilter(true)
                            ->load();

            $fees = $feeCollection->getItems();

            if ($fees) {
                try {
                    $subtotal = $session->getQuote()->getSubtotal();
                    $detailsFees = array();
                    $storeFee = array();
                    $resPrice = 0;

                    foreach ($fees as $item) {
                        $feeId = $item->getId();
                        $optionCollection = Mage::getResourceModel('multifees/option_collection')
                                        ->addFeeFilter($feeId)
                                        ->load();

                        $options = $optionCollection->getItems();

                        if ($options) {
                            foreach ($options as $option) {
                                $optionId = $option->getId();
                                if ($helper->isRequired($item->getRequired()) && $option->getIsDefault()) {
                                    $price = 0;
                                    $storeFee[$feeId][] = $optionId;

                                    $detailsFees[$feeId]['title'] = $feeId;
                                    $detailsFees[$feeId]['message'] = '';
                                    $detailsFees[$feeId]['date'] = '';

                                    if ($helper->isTypeFixed($option->getPriceType())) {
                                        $price = $option->getPrice();
                                    } else {
                                        if ($subtotal > 0) {
                                            $price = ($subtotal * $option->getPrice()) / 100;
                                        }
                                    }
                                    $resPrice += $price;

                                    $detailsFees[$feeId]['options'][$optionId] = Mage::getModel('multifees/option')->getOptionItem($optionId)->getOption();
                                    $detailsFees[$feeId]['price'][$optionId] = $price;
                                }
                            }
                        }
                    }
                    if ($detailsFees) {
                        $session->setDetailsMultifees($detailsFees);
                    }
                    $session->setStoreMultifees($storeFee);
                    $session->setMultifees($resPrice);
                    $session->setOnlyOneAutoadd(true);
                } catch (Exception $e) {
                    $session->addException($e, $e->getMessage());
                }
            }
        }
    }

    private function _prepareCheckoutMethod($fees) {
        $result = array();
        if ($fees) {
            foreach ($fees as $item) {
                $methods = $item->getCheckoutMethod();
                $methods = explode(',', $methods);
                $result = array_merge($result, $methods);
            }
        }
        return array_unique($result);
    }

    private function _prepareFeeOptions($fees, $method = null) {
        if (is_null($method)) {
            return false;
        }
        $helper = $this->_getHelper();
        $session = $this->_getCheckoutSession();
        $subtotal = $session->getQuote()->getSubtotal();
        $detailsFees = array();
        $storeFee = array();
        $resPrice = 0;

        $sesDetailsFees = $session->getDetailsMultifees();
        $sesStoreFees = $session->getStoreMultifees();
        $sesFees = $session->getMultifees();

        if ($fees) {
            $methods = $this->_prepareCheckoutMethod($fees);
            if (empty($methods)) {
                return false;
            } elseif (!in_array($method, $methods)) {
                foreach ($fees as $item) {
                    $feeId = $item->getId();
                    if (is_array($sesDetailsFees) && count($sesDetailsFees)) {
                        if (array_key_exists($feeId, $sesDetailsFees)) {
                            unset($sesDetailsFees[$feeId]);
                        }
                    }
                    if (is_array($sesStoreFees) && count($sesStoreFees)) {
                        if (array_key_exists($feeId, $sesStoreFees)) {
                            unset($sesStoreFees[$feeId]);
                        }
                    }

                    $optionCollection = Mage::getResourceModel('multifees/option_collection')
                                    ->addFeeFilter($feeId)
                                    ->load();

                    $options = $optionCollection->getItems();
                    if ($options) {
                        foreach ($options as $option) {
                            $optionId = $option->getId();
                            $price = 0;
                            if ($helper->isTypeFixed($option->getPriceType())) {
                                $price = $option->getPrice();
                            } else {
                                if ($subtotal > 0) {
                                    $price = ($subtotal / (100 + $option->getPrice())) * 100;
                                }
                            }
                            $sesFees -= $price;
                        }
                    }
                }
                //return false;
            }
            foreach ($fees as $item) {
                $itemMethods = $item->getCheckoutMethod();
                $itemMethods = explode(',', $itemMethods);
                $feeId = $item->getId();
                if (is_array($sesDetailsFees) && count($sesDetailsFees)) {
                    if (array_key_exists($feeId, $sesDetailsFees)) {
                        unset($sesDetailsFees[$feeId]);
                    }
                }
                if (is_array($sesStoreFees) && count($sesStoreFees)) {
                    if (array_key_exists($feeId, $sesStoreFees)) {
                        unset($sesStoreFees[$feeId]);
                    }
                }
                if (is_array($itemMethods) && in_array($method, $itemMethods)) {
                    $optionCollection = Mage::getResourceModel('multifees/option_collection')
                                    ->addFeeFilter($feeId)
                                    ->load();

                    $options = $optionCollection->getItems();
                    if ($options) {

                        foreach ($options as $option) {
                            $optionId = $option->getId();
                            $price = 0;

                            $storeFee[$feeId][] = $optionId;

                            $detailsFees[$feeId]['title'] = $feeId;
                            $detailsFees[$feeId]['message'] = '';
                            $detailsFees[$feeId]['date'] = '';

                            if ($helper->isTypeFixed($option->getPriceType())) {
                                $price = $option->getPrice();
                            } else {
                                if ($subtotal > 0) {
                                    $price = ($subtotal * $option->getPrice()) / 100;
                                }
                            }
                            $resPrice += $price;

                            $detailsFees[$feeId]['options'][$optionId] = Mage::getModel('multifees/option')->getOptionItem($optionId)->getOption();
                            $detailsFees[$feeId]['price'][$optionId] = $price;
                        }
                    }
                }
            }
            if ($detailsFees && $sesDetailsFees) {
                $detailsFees = $detailsFees + $sesDetailsFees;
            } elseif (!$detailsFees && $sesDetailsFees)
                $detailsFees = $sesDetailsFees;
            if ($storeFee && $sesStoreFees) {
                $storeFee = $storeFee + $sesStoreFees;
            } elseif (!$storeFee && $sesStoreFees)
                $storeFee = $sesStoreFees;
            if ($sesFees > 0) {
                $resPrice += $sesFees;
            }
        }
        return new Varien_Object(array('details' => $detailsFees, 'store' => $storeFee, 'final_price' => $resPrice));
    }

    public function getCheckoutShippingMethod(Varien_Event_Observer $observer) {
        $helper = $this->_getHelper();
        if ($helper->isEnableShipping()) {
            $session = $this->_getCheckoutSession();
            $method = $observer->getRequest()->getParam('shipping_method');
            if (is_array($method) && count($method)) {
                $method = $method[key($method)];
            }

            if ($method) {
                $feeCollection = Mage::getResourceModel('multifees/fee_collection')
                                ->addFeeStoreFilter()
                                ->addStatusFilter()
                                ->addCheckoutShippingTypeFilter()
                                ->load();

                $fees = $feeCollection->getItems();

                if ($fees) {
                    try {
                        $options = $this->_prepareFeeOptions($fees, $method);
                        if ($options === false) {
                            $session->setStoreMultifees();
                            $session->setMultifees();
                            $session->setDetailsMultifees();
                            return;
                        }
                        if ($options->getDetails()) {
                            $session->setDetailsMultifees($options->getDetails());
                        }
                        $session->setStoreMultifees($options->getStore());
                        $session->setMultifees($options->getFinalPrice());
                    } catch (Exception $e) {
                        $session->addException($e, $e->getMessage());
                    }
                }
            }
        }
    }

    public function checkoutSubmitAllAfter($observer) {
        $helper = $this->_getHelper();
        $order = $observer->getEvent()->getOrder();
        if ($helper->isEnablePayment()) {
            $session = $this->_getCheckoutSession();
            $method = Mage::app()->getRequest()->getParam('payment');
            $pay_method = '';
            $pay_method = Mage::app()->getRequest()->getParam('payment_method');
            if (isset($method['method'])) {
                $method = $method['method'];
            } elseif ($pay_method != '') {
                $method = $pay_method;
            } else {
                return;
            }
            if ($method) {
                $feeCollection = Mage::getResourceModel('multifees/fee_collection')
                                ->addFeeStoreFilter()
                                ->addStatusFilter()
                                ->addCheckoutPaymentTypeFilter()
                                ->load();

                $fees = $feeCollection->getItems();
                if ($fees) {
                    try {
                        $options = $this->_prepareFeeOptions($fees, $method);
                        if ($options === false) {
                            $order->setBaseMultifeesAmount();
                            $order->setMultifees();
                            $order->setBaseMultifees();
                            return;
                        }
                        if ($options->getDetails()) {
                            $order->setDetailsMultifees(serialize($options->getDetails()));
                        }
                        $finalPrice = $options->getFinalPrice();
                        $order->setMultifees($finalPrice);
                        $order->setBaseMultifeesAmount($finalPrice);
                        $order->setBaseMultifees($finalPrice);
                        $order->save();
                    } catch (Exception $e) {
                        $session->addException($e, $e->getMessage());
                    }
                }
            }
        }
        return $this;
    }

    public function getCheckoutPaymentMethod(Varien_Event_Observer $observer) {
        $helper = $this->_getHelper();
        if ($helper->isEnablePayment()) {
            $session = $this->_getCheckoutSession();
//            $address = $session->_getQuote()->getAddress();
//            $address = $session->getAddress();
            $method = Mage::app()->getRequest()->getParam('payment');
            $pay_method = '';
            $pay_method = Mage::app()->getRequest()->getParam('payment_method');
            if (isset($method['method'])) {
                $method = $method['method'];
            } elseif ($pay_method != '') {
                $method = $pay_method;
            } else {
                return;
            }
			
            if ($method) {
                $feeCollection = Mage::getResourceModel('multifees/fee_collection')
                                ->addFeeStoreFilter()
                                ->addStatusFilter()
                                ->addCheckoutPaymentTypeFilter()
                                ->load();

                $fees = $feeCollection->getItems();
                if ($fees) {
                    try {
                        $options = $this->_prepareFeeOptions($fees, $method);
                        if ($options === false || $options->getDetails() == array()) {
                            $session->setStoreMultifees();
                            $session->setMultifees();
                            $session->setDetailsMultifees();
                            return;
                        }
                        if ($options->getDetails()) {
                            $session->setDetailsMultifees($options->getDetails());
                        }
                        $session->setStoreMultifees($options->getStore());
                        $session->setMultifees($options->getFinalPrice());
                    } catch (Exception $e) {
                        $session->addException($e, $e->getMessage());
                    }
                }
            }
        }
    }

}
