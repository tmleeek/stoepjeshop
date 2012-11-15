<?php
/**
 * Dropdown source for the bank options.
 *
 * @category    PayNL
 * @package     PayNL_Giropay
 * @author      Sebastian Berm
 * @copyright   Copyright (c) 2009-2010 TinTel
 */
class PayNL_Giropay_Model_Source_PaymentAction
{
    public function toOptionArray()
    {
        return array(
            array('value' => PayNL_Giropay_Model_Giropay::PAYMENT_TYPE_SALE,
                'label' => Mage::helper('giropay')->__('Sale')),
        );
    }
}
?>