<?php
/**
 * data helper
 * This one isn't written by us...
 *
 * @category    Mage
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class PayNL_Ideal_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function encrypt($token)
    {
        return bin2hex(base64_decode(Mage::helper('core')->encrypt($token)));
    }

    public function decrypt($token)
    {
        return Mage::helper('core')->decrypt(base64_encode(pack('H*', $token)));
    }
}
?>
