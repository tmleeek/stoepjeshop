<?php

class PayNL_Afterpay_Model_Creditmemo_Total extends Mage_Sales_Model_Order_Creditmemo_Total_Abstract
{
    public function collect(Mage_Sales_Model_Order_Creditmemo $cm)
    {

        $order = $cm->getOrder();
        $baseCmTotal = $cm->getBaseGrandTotal();
        $cmTotal = $cm->getGrandTotal();

        $baseExtraFeeCredited 	= $order->getBaseExtraFeeCredited();
        $extraFeeCredited = $order->getExtraFeeCredited();

        $baseExtraFeeInvoiced 	= $order->getBaseExtraFeeInvoiced();
        $extraFeeInvoiced = $order->getExtraFeeInvoiced();
        
        $baseExtraFeeCreditedFeeToCredit = 0;
        $extraFeeToCredit = 0;
	if ($cm->getInvoice())
        {
            $invoice 				= $cm->getInvoice();
            $baseExtraFeeCreditedFeeToCredit 	= $invoice->getBaseExtraFee();
            $extraFeeToCredit 		= $invoice->getExtraFee();
        }
        else
        {
            $baseExtraFeeCreditedFeeToCredit 	= $baseExtraFeeInvoiced;
            $extraFeeToCredit 		= $extraFeeInvoiced;
        }
		
        if (!$baseExtraFeeCreditedFeeToCredit > 0)
        {
            return $this;
        }
		
	$cm->setBaseGrandTotal($baseCmTotal+$baseExtraFeeCreditedFeeToCredit);
        $cm->setGrandTotal($cmTotal+$extraFeeToCredit);

        $cm->setBaseExtraFee($baseExtraFeeCreditedFeeToCredit);
        $cm->setExtraFee($extraFeeToCredit);

        $order->setBaseExtraFeeCredited($baseExtraFeeCredited+$baseExtraFeeCreditedFeeToCredit);
        $order->setExtraFeeCredited($extraFeeCredited+$baseExtraFeeCreditedFeeToCredit);

        return $this;
    }
}