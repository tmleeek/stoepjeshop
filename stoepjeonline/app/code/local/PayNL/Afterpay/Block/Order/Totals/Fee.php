<?php
class PayNL_Afterpay_Block_Order_Totals_Fee extends Mage_Core_Block_Abstract
{
   public function initTotals()
   {
        $parent = $this->getParentBlock();
        $this->_order   = $parent->getOrder();
        if($this->_order->getExtraFee() && (int)$this->_order->getExtraFee() != 0){
            $extraFee = new Varien_Object();
            $extraFee->setLabel($this->_order->getPayment()->getMethodInstance()->getTitle()." ".$this->__('fee'));
            $extraFee->setValue($this->_order->getExtraFee());
            $extraFee->setBaseValue($this->_order->getBaseExtraFee());
            $extraFee->setCode('extra_fee');
            $parent->addTotalBefore($extraFee,'grand_total');
        }
        return $this;
   }
}