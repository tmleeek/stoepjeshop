<?php
/**
 * Dropdown source for the bank options.
 *
 * @category    PayNL
 * @package     PayNL_Pincasso
 * @author      Sebastian Berm
 * @copyright   Copyright (c) 2009-2010 TinTel
 */
class PayNL_Pincasso_Model_Source_PaymentAction
{
    public function toOptionArray()
    {
        return array(
            array('value' => PayNL_Pincasso_Model_Pincasso::PAYMENT_TYPE_SALE,
                'label' => Mage::helper('pincasso')->__('Sale')),
        );
    }
}
?>