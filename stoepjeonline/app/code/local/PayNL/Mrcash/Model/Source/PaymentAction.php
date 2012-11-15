<?php
/**
 * Dropdown source for the bank options.
 *
 * @category    PayNL
 * @package     PayNL_Mrcash
 * @author      Sebastian Berm
 * @copyright   Copyright (c) 2009-2010 TinTel
 */
class PayNL_Mrcash_Model_Source_PaymentAction
{
    public function toOptionArray()
    {
        return array(
            array('value' => PayNL_Mrcash_Model_Mrcash::PAYMENT_TYPE_SALE,
                'label' => Mage::helper('mrcash')->__('Sale')),
        );
    }
}
?>