<?php
/**
 * Failure resp. from pay.nl
 *
 * @category    PayNL
 * @package     PayNL_Clickandbuy
 * @author      Sebastian Berm
 * @copyright   Copyright (c) 2009-2010 TinTel
 */
 
class PayNL_Clickandbuy_Block_Clickandbuy_Failure extends Mage_Core_Block_Template
{
    /**
     *  Returns error from session and clears it
     *
     *  @return	  string
     */
    public function getErrorMessage ()
    {
        $error = Mage::getSingleton('checkout/session')->getClickandbuyErrorMessage();
        Mage::getSingleton('checkout/session')->unsClickandbuyErrorMessage();
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