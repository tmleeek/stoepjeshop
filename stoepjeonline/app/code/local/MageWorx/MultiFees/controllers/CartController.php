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
class MageWorx_MultiFees_CartController extends Mage_Core_Controller_Front_Action
{

    public function indexAction()
    {
        $session = $this->_getSession();
        $helper = Mage::helper('multifees');
        if ($helper->isFeeEnabled()) {
            $resPrice = 0;
            $storeFee = $this->getRequest()->getPost('fee');
            $feeMessage = $this->getRequest()->getPost('message');
            $feeDate = $this->getRequest()->getPost('date');

            $filter = new Zend_Filter();
            $filter->addFilter(new Zend_Filter_StringTrim());
            $filter->addFilter(new Zend_Filter_StripTags());

            $detailsFees = array();

            $productIds = Mage::getSingleton('checkout/cart')->getQuoteProductIds();

            if ($storeFee) {
                $subtotal = $session->getQuote()->getSubtotal();
                $quoteItems = $session->getQuote()->getAllItems();
                if ($quoteItems) {
                    foreach ($quoteItems as $quoteItem)
                    {
                        $productModel = Mage::getModel('catalog/product');
                        $product = $productModel->load($quoteItem->getProduct()->getId());
                        $additionalFees = $product->getAdditionalFees();
                        if ('-2' == $additionalFees) {
                            $subtotal -= $quoteItem->getPrice();
                        }
                    }
                }
                foreach ($storeFee as $feeId => $value) {
                    if (is_array($value) && count($value)) {
                        foreach ($value as $id) {
                            if ($id) {
                                $price = 0;
                                $detailsFees[$feeId]['title'] = $feeId;
                                $detailsFees[$feeId]['message'] = Mage::helper('core/string')->truncate($filter->filter($feeMessage[$feeId]), 1024);
                                $detailsFees[$feeId]['date'] = $filter->filter($feeDate[$feeId]);

                                $option = Mage::getSingleton('multifees/option')->load((int) $id);
                                if (Mage::helper('multifees')->isTypeFixed($option->getPriceType())) {
                                    $price = $option->getPrice();
                                } else {
                                    if ($subtotal > 0) {
                                        $price = ($subtotal * $option->getPrice()) / 100;
                                    }
                                }
                                $resPrice += $price;

                                $detailsFees[$feeId]['options'][$id] = Mage::getModel('multifees/option')->getOptionItem($id)->getOption();
                                $detailsFees[$feeId]['price'][$id] = $price;
                            }
                        }
                    }
                }
                if ($detailsFees) {
                    $session->setDetailsMultifees($detailsFees);
                }
            }
            if ($resPrice < 0 || !count($detailsFees)) {
                return $this->removeAction();
            } else {
                $session->setStoreMultifees($storeFee);
                $session->setMultifees($resPrice);
            }
        }
        $this->_redirect('checkout/cart');
    }

    public function removeAction()
    {
        $session = $this->_getSession();
        $session->setMultifees();
        $session->setStoreMultifees();
        $session->setDetailsMultifees();
        $this->_redirect('checkout/cart');
    }

    protected function _getSession()
    {
        return Mage::getSingleton('checkout/session');
    }

    public function getImageAction()
    {
        $optionId = (int) $this->getRequest()->getParam('option');
        $bigImg = (int) $this->getRequest()->getParam('big-image');
        $isRealSize = false;
        if ($bigImg) {
            $isRealSize = true;
        }
        $http = new Zend_Controller_Request_Http();
        if ($http->isXmlHttpRequest()) {
            $html = '';
            if (Mage::helper('multifees')->isMultifeesFile($optionId)) {
                $html = Mage::helper('multifees')->getOptionImgHtml(Mage::getModel('multifees/option')->load($optionId));
            }
            print $html;
        } else {
            return Mage::helper('multifees')->getImageView($optionId, $isRealSize);
        }
    }

}