<?php
/**
 * Failure resp. from pay.nl
 *
 * @category    PayNL
 * @package     PayNL_Ppaypal
 * @author      Sebastian Berm
 * @copyright   Copyright (c) 2009-2010 TinTel
 */
 
class PayNL_Ppaypal_Block_Ppaypal_Failure extends Mage_Core_Block_Template
{
    /**
     *  Returns error from session and clears it
     *
     *  @return	  string
     */
    public function getErrorMessage ()
    {
        $error = Mage::getSingleton('checkout/session')->getPpaypalErrorMessage();
        Mage::getSingleton('checkout/session')->unsPpaypalErrorMessage();
        return $error;
    }

    /**
     * Get continue shopping url
     */
    public function getContinueShoppingUrl()
    {
        return Mage::getUrl('checkout/cart');
    }
}
?>