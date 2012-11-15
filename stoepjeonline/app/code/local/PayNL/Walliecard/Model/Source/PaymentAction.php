<?php
/**
 * Dropdown source for the bank options.
 *
 * @category    PayNL
 * @package     PayNL_Walliecard
 * @author      Sebastian Berm
 * @copyright   Copyright (c) 2009-2010 TinTel
 */
class PayNL_Walliecard_Model_Source_PaymentAction
{
    public function toOptionArray()
    {
        return array(
            array('value' => PayNL_Walliecard_Model_Walliecard::PAYMENT_TYPE_SALE,
                'label' => Mage::helper('walliecard')->__('Sale')),
        );
    }
}
?>