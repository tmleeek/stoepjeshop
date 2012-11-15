<?php
/**
 * Dropdown source for the bank options.
 *
 * @category    PayNL
 * @package     PayNL_Clickandbuy
 * @author      Sebastian Berm
 * @copyright   Copyright (c) 2009-2010 TinTel
 */
class PayNL_Clickandbuy_Model_Source_PaymentAction
{
    public function toOptionArray()
    {
        return array(
            array('value' => PayNL_Clickandbuy_Model_Clickandbuy::PAYMENT_TYPE_SALE,
                'label' => Mage::helper('clickandbuy')->__('Sale')),
        );
    }
}
?>