<?php
/**
 * Dropdown source for the bank options.
 *
 * @category    PayNL
 * @package     PayNL_Maestro
 * @author      Sebastian Berm
 * @copyright   Copyright (c) 2009-2010 TinTel
 */
class PayNL_Maestro_Model_Source_PaymentAction
{
    public function toOptionArray()
    {
        return array(
            array('value' => PayNL_Maestro_Model_Maestro::PAYMENT_TYPE_SALE,
                'label' => Mage::helper('maestro')->__('Sale')),
        );
    }
}
?>