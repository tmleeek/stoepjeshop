<?php
/**
 * Pay.nl api debug
 * Not that this is really used, but still...
 *
 * @category    PayNL
 * @package     PayNL_Walliecard
 * @author      Sebastian Berm
 * @copyright   Copyright (c) 2009-2010 TinTel
 */
class Mage_Walliecard_Model_Api_Debug extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('walliecard/api_debug');
    }
}
?>