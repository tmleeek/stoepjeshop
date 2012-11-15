<?php

class PayNL_Afterpay_Block_Invoice_Totals_Fee extends Mage_Core_Block_Abstract
{
   public function initTotals()
   {
        $parent = $this->getParentBlock();
        $this->_invoice   = $parent->getInvoice();
        if($this->_invoice->getExtraFee() && (int)$this->_invoice->getExtraFee() != 0){
            $extraFee = new Varien_Object();
            $extraFee->setLabel($this->_invoice->getOrder()->getPayment()->getMethodInstance()->getTitle()." ".$this->__('fee'));
            $extraFee->setValue($this->_invoice->getExtraFee());
            $extraFee->setBaseValue($this->_invoice->getBaseExtraFee());
            $extraFee->setCode('extra_fee');

            $parent->addTotalBefore($extraFee,'grand_total');
        }
        return $this;
   }

}