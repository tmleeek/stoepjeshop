<?php
/**
 * pay.nl fasterpay Api Debug Resource
 *
 * @category    PayNL
 * @package     PayNL_Fasterpay
 * @author      Sebastian Berm
 * @copyright   Copyright (c) 2009-2010 TinTel
 */
class PayNL_Fasterpay_Model_Mysql4_Api_Debug extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('fasterpay/api_debug', 'debug_id');
    }
}