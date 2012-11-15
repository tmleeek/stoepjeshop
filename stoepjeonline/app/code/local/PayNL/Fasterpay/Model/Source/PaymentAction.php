<?php
/**
 * Dropdown source for the bank options.
 *
 * @category    PayNL
 * @package     PayNL_Fasterpay
 * @author      Sebastian Berm
 * @copyright   Copyright (c) 2009-2010 TinTel
 */
class PayNL_Fasterpay_Model_Source_PaymentAction
{
    public function toOptionArray()
    {
        return array(
            array('value' => PayNL_Fasterpay_Model_Fasterpay::PAYMENT_TYPE_SALE,
                'label' => Mage::helper('fasterpay')->__('Sale')),
        );
    }
}
?>