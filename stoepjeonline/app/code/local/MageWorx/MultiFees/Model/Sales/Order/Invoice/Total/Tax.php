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


class MageWorx_MultiFees_Model_Sales_Order_Invoice_Total_Tax extends Mage_Sales_Model_Order_Invoice_Total_Abstract
{
    /**
     * Collect invoice tax amount
     *
     * @param Mage_Sales_Model_Order_Invoice $invoice
     */
    public function collect(Mage_Sales_Model_Order_Invoice $invoice)
    {
        $totalTax       = 0;
        $baseTotalTax   = 0;
        $totalHiddenTax      = 0;
        $baseTotalHiddenTax  = 0;

        $order = $invoice->getOrder();
        foreach ($invoice->getAllItems() as $item) {
            $orderItem = $item->getOrderItem();
            $orderItemQty = $orderItem->getQtyOrdered();

            if ($orderItem->getTaxAmount() && $orderItemQty) {
                if ($item->getOrderItem()->isDummy()) {
                    continue;
                }

                /**
                 * Resolve rounding problems
                 */
                if ($item->isLast()) {
                    $tax            = $orderItem->getTaxAmount() - $orderItem->getTaxInvoiced();
                    $baseTax        = $orderItem->getBaseTaxAmount() - $orderItem->getBaseTaxInvoiced();
                    $hiddenTax      = $orderItem->getHiddenTaxAmount() - $orderItem->getHiddenTaxInvoiced();
                    $baseHiddenTax  = $orderItem->getBaseHiddenTaxAmount() - $orderItem->getBaseHiddenTaxInvoiced();
                } else {
                    $tax            = $orderItem->getTaxAmount()*$item->getQty()/$orderItemQty;
                    $baseTax        = $orderItem->getBaseTaxAmount()*$item->getQty()/$orderItemQty;
                    $hiddenTax      = $orderItem->getHiddenTaxAmount()*$item->getQty()/$orderItemQty;
                    $baseHiddenTax  = $orderItem->getBaseHiddenTaxAmount()*$item->getQty()/$orderItemQty;

                    $tax            = $invoice->getStore()->roundPrice($tax);
                    $baseTax        = $invoice->getStore()->roundPrice($baseTax);
                    $hiddenTax      = $invoice->getStore()->roundPrice($hiddenTax);
                    $baseHiddenTax  = $invoice->getStore()->roundPrice($baseHiddenTax);
                }

                $item->setTaxAmount($tax);
                $item->setBaseTaxAmount($baseTax);
                $item->setHiddenTaxAmount($hiddenTax);
                $item->setBaseHiddenTaxAmount($baseHiddenTax);

                $totalTax += $tax;
                $baseTotalTax += $baseTax;
                $totalHiddenTax += $hiddenTax;
                $baseTotalHiddenTax += $baseHiddenTax;
            }
        }

        if ($this->_canIncludeShipping($invoice)) {
            $totalTax           += $order->getShippingTaxAmount();
            $baseTotalTax       += $order->getBaseShippingTaxAmount();
            $totalHiddenTax     += $order->getShippingHiddenTaxAmount();
            $baseTotalHiddenTax += $order->getBaseShippingHiddenTaxAmount();
            $invoice->setShippingTaxAmount($order->getShippingTaxAmount());
            $invoice->setBaseShippingTaxAmount($order->getBaseShippingTaxAmount());
            $invoice->setShippingHiddenTaxAmount($order->getShippingHiddenTaxAmount());
            $invoice->setBaseShippingHiddenTaxAmount($order->getBaseShippingHiddenTaxAmount());
        }
        
        $allowedTax     = $order->getTaxAmount() - $order->getTaxInvoiced() - ($order->getMultifees() - $order->getBaseMultifees()); //TODO getMultifeesTax()
        $allowedBaseTax = $order->getBaseTaxAmount() - $order->getBaseTaxInvoiced() - ($order->getBaseMultifeesAmount() - $order->getBaseMultifees()); //TODO getBaseMultifeesTax();
        $allowedHiddenTax     = $order->getHiddenTaxAmount() + $order->getShippingHiddenTaxAmount()
            - $order->getHiddenTaxInvoiced() - $order->getShippingHiddenTaxInvoiced();
        $allowedBaseHiddenTax = $order->getBaseHiddenTaxAmount() + $order->getBaseShippingHiddenTaxAmount()
            - $order->getBaseHiddenTaxInvoiced() - $order->getBaseShippingHiddenTaxInvoiced();

        if ($invoice->isLast()) {
            $totalTax           = $allowedTax;
            $baseTotalTax       = $allowedBaseTax;
            $totalHiddenTax     = $allowedHiddenTax;
            $baseTotalHiddenTax = $allowedBaseHiddenTax;
        } else {
            $totalTax           = min($allowedTax, $totalTax);
            $baseTotalTax       = min($allowedBaseTax, $baseTotalTax);
            $totalHiddenTax     = min($allowedHiddenTax, $totalHiddenTax);
            $baseTotalHiddenTax = min($allowedBaseHiddenTax, $baseTotalHiddenTax);
        }

        $invoice->setTaxAmount($totalTax);
        $invoice->setBaseTaxAmount($baseTotalTax);
        $invoice->setHiddenTaxAmount($totalHiddenTax);
        $invoice->setBaseHiddenTaxAmount($baseTotalHiddenTax);

        $invoice->setGrandTotal($invoice->getGrandTotal() + $totalTax + $totalHiddenTax);
        $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $baseTotalTax + $baseTotalHiddenTax);

        return $this;
    }

    /**
     * Check if shipping tax calculation can be included to current invoice
     * @param Mage_Sales_Model_Order_Invoice $invoice
     */
    protected function _canIncludeShipping($invoice)
    {
        $includeShippingTax = true;
        /**
         * Check shipping amount in previus invoices
         */
        foreach ($invoice->getOrder()->getInvoiceCollection() as $previusInvoice) {
            if ($previusInvoice->getShippingAmount() && !$previusInvoice->isCanceled()) {
                $includeShippingTax = false;
            }
        }
        return $includeShippingTax;
    }
}
