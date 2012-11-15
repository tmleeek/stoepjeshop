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

abstract class MageWorx_MultiFees_Model_Sales_Order_Pdf_Invoice extends Vianetz_AdvancedInvoiceLayout_Model_Order_Pdf_Invoice
{
    protected function insertTotals($page, $source){
        $order = $source->getOrder();
        $font = $this->_setFontBold($page);

        $order_subtotal = Mage::helper('sales')->__('Order Subtotal:');
        $page->drawText($order_subtotal,
475-$this->widthForStringUsingFontSize($order_subtotal, $font, $this->fontsize_bold),
$this->y, self::PDF_CHARSET);

        $order_subtotal = $order->formatPriceTxt($source->getSubtotalInclTax());
        $page->drawText($order_subtotal,
545-$this->widthForStringUsingFontSize($order_subtotal, $font, $this->fontsize_bold),
$this->y, self::PDF_CHARSET);
        $this->y -=15;

        if ((float)$source->getDiscountAmount()){
            $discount = Mage::helper('sales')->__('Discount :');
            $page->drawText($discount, 475-$this->widthForStringUsingFontSize($discount,
$font, $this->fontsize_bold), $this->y, self::PDF_CHARSET);

            $discount = $order->formatPriceTxt(0.00 - $source->getDiscountAmount());
            $page->drawText($discount, 545-$this->widthForStringUsingFontSize($discount,
$font, $this->fontsize_bold), $this->y, self::PDF_CHARSET);
            $this->y -=15;
        }

        if ((float)$source->getShippingAmount()){
            $order_shipping = Mage::helper('sales')->__('Shipping & Handling:');
            $page->drawText($order_shipping,
475-$this->widthForStringUsingFontSize($order_shipping, $font, $this->fontsize_bold),
$this->y, self::PDF_CHARSET);

            $order_shipping = $order->formatPriceTxt($source->getShippingAmount());
            $page->drawText($order_shipping,
545-$this->widthForStringUsingFontSize($order_shipping, $font, $this->fontsize_bold),
$this->y, self::PDF_CHARSET);
            $this->y -=15;
        }

        if ($source->getAdjustmentPositive()){
            $adjustment_refund = Mage::helper('sales')->__('Adjustment Refund:');
            $page ->drawText($adjustment_refund,
475-$this->widthForStringUsingFontSize($adjustment_refund, $font, $this->fontsize_bold),
$this->y, self::PDF_CHARSET);

            $adjustment_refund = $order->formatPriceTxt($source->getAdjustmentPositive());
            $page ->drawText($adjustment_refund,
545-$this->widthForStringUsingFontSize($adjustment_refund, $font, $this->fontsize_bold),
$this->y, self::PDF_CHARSET);
            $this->y -=15;
        }

        if ((float) $source->getAdjustmentNegative()){
            $adjustment_fee = Mage::helper('sales')->__('Adjustment Fee:');
            $page ->drawText($adjustment_fee,
475-$this->widthForStringUsingFontSize($adjustment_fee, $font, $this->fontsize_bold),
$this->y, self::PDF_CHARSET);

            $adjustment_fee=$order->formatPriceTxt($source->getAdjustmentNegative());
            $page ->drawText($adjustment_fee,
545-$this->widthForStringUsingFontSize($adjustment_fee, $font, $this->fontsize_bold),
$this->y, self::PDF_CHARSET);
            $this->y -=15;
        }

        if ((float) $source->getBaseMultifees()){
            $multifees = Mage::helper('sales')->__('Additional Fees:');
            $page ->drawText($multifees,
475-$this->widthForStringUsingFontSize($multifees, $font, $this->fontsize_bold),
$this->y, self::PDF_CHARSET);

            $multifees=$order->formatPriceTxt($source->getBaseMultifees());
            $page ->drawText($multifees,
545-$this->widthForStringUsingFontSize($multifees, $font, $this->fontsize_bold),
$this->y, self::PDF_CHARSET);
            $this->y -=15;
        }
        
        if ((float)$source->getTaxAmount()){
			$fullInfo = $order->getFullTaxInfo();
			
			if ( $fullInfo && Mage::getStoreConfig(self::XML_PATH_SALES_PDF_ADVANCEDINVOICELAYOUT_SPLIT_TAXES, $source->getStore()) ) {
				foreach ($fullInfo as $info) {
					$percent = $info['percent'];
					$amount = $info['amount']; 
					$rates = array_unique($info['rates']);
					foreach ($rates as $rate) {
						if ( $rate['percent'] ) {
                            if (
Mage::getStoreConfig(self::XML_PATH_SALES_PDF_ADVANCEDINVOICELAYOUT_SHOW_TAXRATE_NAME,
$source->getStore())) 
							    $order_tax = $rate['title'] . ":";
                            else
                                $order_tax = $rate['percent'] . "% " .
Mage::helper('sales')->__('Tax') . ":";

							$page->drawText($order_tax,
475-$this->widthForStringUsingFontSize($order_tax, $font, $this->fontsize_bold), $this->y,
self::PDF_CHARSET);
						}
						$order_tax = $order->formatPriceTxt($amount);
						$page->drawText($order_tax,
545-$this->widthForStringUsingFontSize($order_tax, $font, $this->fontsize_bold), $this->y,
self::PDF_CHARSET);
						$this->y -=15;
					}
				}
			} else {
				$order_tax = Mage::helper('sales')->__('Tax :');
				$page->drawText($order_tax,
475-$this->widthForStringUsingFontSize($order_tax, $font, $this->fontsize_bold), $this->y,
self::PDF_CHARSET);
				$order_tax = $order->formatPriceTxt($source->getTaxAmount());
				$page->drawText($order_tax,
545-$this->widthForStringUsingFontSize($order_tax, $font, $this->fontsize_bold), $this->y,
self::PDF_CHARSET);
			    $this->y -=15;
            }
            
			$this->y -=15;
        }


        $page->setFont($font, $this->fontsize_bold+2);

        $order_grandtotal = Mage::helper('sales')->__('Grand Total:');
        $page ->drawText($order_grandtotal,
475-$this->widthForStringUsingFontSize($order_grandtotal, $font, $this->fontsize_bold+2),
$this->y, self::PDF_CHARSET);

        $order_grandtotal = $order->formatPriceTxt($source->getGrandTotal());
        $page ->drawText($order_grandtotal,
545-$this->widthForStringUsingFontSize($order_grandtotal, $font, $this->fontsize_bold+2),
$this->y, self::PDF_CHARSET);
        $this->y -=15;
    }

}
