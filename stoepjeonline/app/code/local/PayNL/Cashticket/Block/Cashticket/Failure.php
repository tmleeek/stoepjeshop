<?php
/**
 * Failure resp. from pay.nl
 *
 * @category    PayNL
 * @package     PayNL_Cashticket
 * @author      Sebastian Berm
 * @copyright   Copyright (c) 2009-2010 TinTel
 */
 
class PayNL_Cashticket_Block_Cashticket_Failure extends Mage_Core_Block_Template
{
    /**
     *  Returns error from session and clears it
     *
     *  @return	  string
     */
    public function getErrorMessage ()
    {
        $error = Mage::getSingleton('checkout/session')->getCashticketErrorMessage();
        Mage::getSingleton('checkout/session')->unsCashticketErrorMessage();
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