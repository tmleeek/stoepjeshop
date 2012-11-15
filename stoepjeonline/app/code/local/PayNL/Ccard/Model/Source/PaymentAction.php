<?php
/**
 * Dropdown source for the bank options.
 *
 * @category    PayNL
 * @package     PayNL_Ccard
 * @author      Sebastian Berm
 * @copyright   Copyright (c) 2009-2010 TinTel
 */
class PayNL_Ccard_Model_Source_PaymentAction
{
    public function toOptionArray()
    {
        return array(
            array('value' => PayNL_Ccard_Model_Ccard::PAYMENT_TYPE_SALE,
                'label' => Mage::helper('ccard')->__('Sale')),
        );
    }
}
?>