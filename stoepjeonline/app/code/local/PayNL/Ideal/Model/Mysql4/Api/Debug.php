<?php
/**
 * pay.nl ideal Api Debug Resource
 *
 * @category    PayNL
 * @package     PayNL_Ideal
 * @author      Sebastian Berm
 * @copyright   Copyright (c) 2009-2010 TinTel
 */
class PayNL_Ideal_Model_Mysql4_Api_Debug extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('ideal/api_debug', 'debug_id');
    }
}