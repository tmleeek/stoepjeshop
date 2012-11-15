<?php
/**
 * Dropdown source for the bank options.
 *
 * @category    PayNL
 * @package     PayNL_Poverboeking
 * @author      Sebastian Berm
 * @copyright   Copyright (c) 2009-2010 TinTel
 */
class PayNL_Poverboeking_Model_Source_PaymentAction
{
    public function toOptionArray()
    {
        return array(
            array('value' => PayNL_Poverboeking_Model_Poverboeking::PAYMENT_TYPE_SALE,
                'label' => Mage::helper('poverboeking')->__('Sale')),
        );
    }
}
?>