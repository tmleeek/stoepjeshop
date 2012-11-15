<?php
/**
 * Dropdown source for the bank options.
 *
 * @category    PayNL
 * @package     PayNL_Cashticket
 * @author      Sebastian Berm
 * @copyright   Copyright (c) 2009-2010 TinTel
 */
class PayNL_Cashticket_Model_Source_PaymentAction
{
    public function toOptionArray()
    {
        return array(
            array('value' => PayNL_Cashticket_Model_Cashticket::PAYMENT_TYPE_SALE,
                'label' => Mage::helper('cashticket')->__('Sale')),
        );
    }
}
?>