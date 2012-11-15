<?php
/**
 */

/**
 * fee Total Row Renderer
 *
 */

class PayNL_Afterpay_Block_Adminhtml_Sales_Order_Create_Totals_Fee extends Mage_Adminhtml_Block_Sales_Order_Create_Totals_Default
{
    protected $_template = 'paynl/afterpay/order/create/totals/fee.phtml';

    public function getExtraFeeExcludeTax()
    {
        return $this->getTotal()->getValue();
    }
}
