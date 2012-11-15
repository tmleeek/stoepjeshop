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

class MageWorx_MultiFees_Model_Sales_Order_Invoice_Total_Fee extends Mage_Sales_Model_Order_Invoice_Total_Abstract
{
    public function collect(Mage_Sales_Model_Order_Invoice $invoice)
    {
        $orderFee        = $invoice->getOrder()->getMultifees();
        $baseOrderFee    = $invoice->getOrder()->getBaseMultifees();
        $detailsOrderFee = $invoice->getOrder()->getDetailsMultifees();

        if ($orderFee >= 0) {
        	$invoice->setMultifees($orderFee);
            $invoice->setBaseMultifees($baseOrderFee);
        	$this->_prepareDetailsMultifees($invoice, $detailsOrderFee);

            $invoice->setTaxAmount($invoice->getTaxAmount() + $invoice->getMultifees() - $invoice->getBaseMultifees()); //TODO incl excl tax
            $invoice->setBaseTaxAmount($invoice->getBaseTaxAmount() + $invoice->getMultifees() - $invoice->getBaseMultifees()); //TODO incl excl tax
            $invoice->setGrandTotal($invoice->getGrandTotal() + $invoice->getMultifees());
            $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $invoice->getBaseMultifees());
        }
        return $this;
    }

    private function _prepareDetailsMultifees($invoice, $detailsFees)
    {
		$helper = Mage::helper('multifees');
    	$fees = @unserialize($detailsFees);
    	if (is_array($fees) && count($fees)) {
			$tax = null;
			$checkTax = $helper->getFullTaxInfo($invoice->getOrderId());
			if (count($checkTax)) {
				$tax = $checkTax;
			}
    		$prices = $helper->getMultifeesPrice($fees, $invoice->getSubtotal(), $invoice->getShippingInclTax(), $tax);
    		$detailsFees = serialize($prices->getDetailsFees());
    		$invoice->setMultifees($prices->getFees());
    		$invoice->setBaseMultifees($prices->getBaseFees());
    	}
    	$invoice->setDetailsMultifees($detailsFees);

    	return $this;
    }
}
