<?php
/**
 * Dropdown source for the bank options.
 *
 * @category    PayNL
 * @package     PayNL_Ppaypal
 * @author      Sebastian Berm
 * @copyright   Copyright (c) 2009-2010 TinTel
 */
class PayNL_Ppaypal_Model_Source_PaymentAction
{
    public function toOptionArray()
    {
        return array(
            array('value' => PayNL_Ppaypal_Model_Ppaypal::PAYMENT_TYPE_SALE,
                'label' => Mage::helper('ppaypal')->__('Sale')),
        );
    }
}
?>