<?php
/**
 * Dropdown source for the bank options.
 *
 * @category    PayNL
 * @package     PayNL_Ideal
 * @author      Sebastian Berm
 * @copyright   Copyright (c) 2009-2010 TinTel
 */
class PayNL_Ideal_Model_Source_PaymentAction
{
    public function toOptionArray()
    {
        return array(
            array('value' => PayNL_Ideal_Model_Ideal::PAYMENT_TYPE_SALE,
                'label' => Mage::helper('ideal')->__('Sale')),
        );
    }
}
?>