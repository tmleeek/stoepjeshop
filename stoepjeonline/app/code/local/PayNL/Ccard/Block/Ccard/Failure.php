<?php
/**
 * Failure resp. from pay.nl
 *
 * @category    PayNL
 * @package     PayNL_Ccard
 * @author      Sebastian Berm
 * @copyright   Copyright (c) 2009-2010 TinTel
 */
 
class PayNL_Ccard_Block_Ccard_Failure extends Mage_Core_Block_Template
{
    /**
     *  Returns error from session and clears it
     *
     *  @return	  string
     */
    public function getErrorMessage ()
    {
        $error = Mage::getSingleton('checkout/session')->getCcardErrorMessage();
        Mage::getSingleton('checkout/session')->unsCcardErrorMessage();
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