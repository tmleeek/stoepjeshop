<?php

class PayNL_Afterpay_Model_Invoice_Total extends Mage_Sales_Model_Order_Invoice_Total_Abstract
{
    public function collect(Mage_Sales_Model_Order_Invoice $invoice)
    {
        $order = $invoice->getOrder();
		
        $baseExtraFee	= $order->getBaseExtraFee();
        $baseExtraFeeInvoiced = $order->getBaseExtraFeeInvoiced();
        $baseInvoiceTotal = $invoice->getBaseGrandTotal();
        $ExtraFee = $order->getExtraFee();
        $ExtraFeeInvoiced = $order->getExtraFeeInvoiced();
        $invoiceTotal = $invoice->getGrandTotal();
		
        if (!$baseExtraFee || (float)$baseExtraFeeInvoiced == (float)$baseExtraFee) {
            return $this;
        }

        $baseExtraFeeToInvoice = $baseExtraFee - $baseExtraFeeInvoiced;
        $ExtraFeeToInvoice = $ExtraFee - $ExtraFeeInvoiced;

        $baseInvoiceTotal = $baseInvoiceTotal + $baseExtraFeeToInvoice;
        $invoiceTotal = $invoiceTotal + $ExtraFeeToInvoice;

        $invoice->setBaseGrandTotal($baseInvoiceTotal);
        $invoice->setGrandTotal($invoiceTotal);

        $invoice->setBaseExtraFee($baseExtraFeeToInvoice);
        $invoice->setExtraFee($ExtraFeeToInvoice);

        $order->setBaseExtraFeeInvoiced($baseExtraFeeInvoiced+$baseExtraFeeToInvoice);
        $order->setExtraFeeInvoiced($ExtraFeeInvoiced+$ExtraFeeToInvoice);
		
        return $this;
    }
}