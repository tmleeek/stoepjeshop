<?php
/**
 * Failure resp. from pay.nl
 *
 * @category    PayNL
 * @package     PayNL_Mrcash
 * @author      Sebastian Berm
 * @copyright   Copyright (c) 2009-2010 TinTel
 */
 
class PayNL_Mrcash_Block_Mrcash_Failure extends Mage_Core_Block_Template
{
    /**
     *  Returns error from session and clears it
     *
     *  @return	  string
     */
    public function getErrorMessage ()
    {
        $error = Mage::getSingleton('checkout/session')->getMrcashErrorMessage();
        Mage::getSingleton('checkout/session')->unsMrcashErrorMessage();
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