<?php
/**
 * pay.nl walliecard Api Debug Resource
 *
 * @category    PayNL
 * @package     PayNL_Walliecard
 * @author      Sebastian Berm
 * @copyright   Copyright (c) 2009-2010 TinTel
 */
class PayNL_Walliecard_Model_Mysql4_Api_Debug extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('walliecard/api_debug', 'debug_id');
    }
}