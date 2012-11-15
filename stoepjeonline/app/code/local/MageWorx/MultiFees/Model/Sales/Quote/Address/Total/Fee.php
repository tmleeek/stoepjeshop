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

class MageWorx_MultiFees_Model_Sales_Quote_Address_Total_Fee extends Mage_Sales_Model_Quote_Address_Total_Abstract
{

    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        if ($address->getSubtotal() > 0) {
            $detailsFees = array();
            $helper = Mage::helper('multifees');
            $session = Mage::getSingleton('checkout/session');
            $detailsFees = $session->getDetailsMultifees();

            $address->setMultifees(0);
            $address->setBaseMultifees(0);
            
            $taxes = null;
            if (is_array($detailsFees) && count($detailsFees)) {
                if ($address->getAppliedTaxes()) {
                    $taxes = $address->getAppliedTaxes();
                }
                $prices = $helper->getMultifeesPrice($detailsFees, $address->getSubtotal(), $address->getShippingInclTax(), $taxes);

                $session->setDetailsMultifees($prices->getDetailsFees());
                $session->setBaseMultifees($prices->getBaseFees());
                $session->setMultifees($prices->getFees());
                $session->setMultifeesExclTax($prices->getFeesExclTax());
                $session->setBaseMultifeesInclTax($prices->getBaseFeesInclTax());

                if ($taxes) {
                    $taxes = $helper->setMultifeesTaxes($detailsFees,$taxes);
                }
            }

            if (!$helper->isFeeEnabled() || (Mage_Sales_Model_Quote_Address::TYPE_SHIPPING != $address->getAddressType()) || (0 > $session->getBaseMultifees())) {
                return $this;
            }

            if ($taxes) {
                $address->setAppliedTaxes($taxes);
            }
            $address->setMultifees($session->getMultifees());
            $address->setBaseMultifees($session->getBaseMultifees());
            $address->setDetailsMultifees($helper->setDetailsMultifees());
            $address->setMultifeesExclTax($session->getMultifeesExclTax());
            $address->setBaseMultifeesInclTax($session->getBaseMultifeesInclTax());

            $address->setGrandTotal($address->getGrandTotal() + $address->getMultifees());
            $address->setBaseGrandTotal($address->getBaseGrandTotal() + $address->getBaseMultifees());
            if ($taxes) {
                $address->setTaxAmount($address->getTaxAmount() + $address->getMultifees() - $address->getMultifeesExclTax());
                $address->setBaseTaxAmount($address->getBaseTaxAmount() + $address->getBaseMultifeesInclTax() - $address->getBaseMultifees());
            }
        }
        return $this;
    }

    public function fetch(Mage_Sales_Model_Quote_Address $address)
    {
        $helper = Mage::helper('multifees');
        if ($helper->isStoreMultifees() && $helper->isFeeEnabled()) {
            $address->addTotal(array(
                'code' => $this->getCode(),
                'title' => ($address->getTaxAmount() > 0 && $helper->isIncludingTax()) ? $helper->__('Additional Fees With Tax') : $helper->__('Additional Fees'),
                'value' => $address->getMultifees()
            ));
        }
        return $this;
    }

}
