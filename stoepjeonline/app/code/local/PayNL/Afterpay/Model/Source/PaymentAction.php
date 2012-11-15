<?php
/**
 * Dropdown source for the bank options.
 *
 * @category    PayNL
 * @package     PayNL_Afterpay
 * @author      Sebastian Berm
 * @copyright   Copyright (c) 2009-2010 TinTel
 */
class PayNL_Afterpay_Model_Source_PaymentAction
{
    public function toOptionArray()
    {
        return array(
            array('value' => PayNL_Afterpay_Model_Afterpay::PAYMENT_TYPE_SALE,
                'label' => Mage::helper('afterpay')->__('Sale')),
        );
    }
}
?>
