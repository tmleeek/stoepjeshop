<?php
/**
 * Dropdown source for the bank options.
 *
 * @category    PayNL
 * @package     PayNL_Directebanking
 * @author      Sebastian Berm
 * @copyright   Copyright (c) 2009-2010 TinTel
 */
class PayNL_Directebanking_Model_Source_PaymentAction
{
    public function toOptionArray()
    {
        return array(
            array('value' => PayNL_Directebanking_Model_Directebanking::PAYMENT_TYPE_SALE,
                'label' => Mage::helper('directebanking')->__('Sale')),
        );
    }
}
?>