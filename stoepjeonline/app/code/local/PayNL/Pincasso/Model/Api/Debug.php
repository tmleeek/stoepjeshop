<?php
/**
 * Pay.nl api debug
 * Not that this is really used, but still...
 *
 * @category    PayNL
 * @package     PayNL_Pincasso
 * @author      Sebastian Berm
 * @copyright   Copyright (c) 2009-2010 TinTel
 */
class Mage_Pincasso_Model_Api_Debug extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('pincasso/api_debug');
    }
}
?>