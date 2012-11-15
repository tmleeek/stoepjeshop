<?php

class PayNL_Afterpay_Block_Checkout_Fee extends Mage_Checkout_Block_Total_Default
{

    protected $_template = 'paynl/afterpay/checkout/fee.phtml';


    public function getExtraFeeExcludeTax()
    {
        return $this->getTotal()->getValue();
    }

 }