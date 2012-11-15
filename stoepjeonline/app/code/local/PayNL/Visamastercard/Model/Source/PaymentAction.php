<?php
/**
 * Dropdown source for the bank options.
 *
 * @category    PayNL
 * @package     PayNL_Visamastercard
 * @author      Sebastian Berm
 * @copyright   Copyright (c) 2009-2010 TinTel
 */
class PayNL_Visamastercard_Model_Source_PaymentAction
{
    public function toOptionArray()
    {
        return array(
            array('value' => PayNL_Visamastercard_Model_Visamastercard::PAYMENT_TYPE_SALE,
                'label' => Mage::helper('visamastercard')->__('Sale')),
        );
    }
}
?>