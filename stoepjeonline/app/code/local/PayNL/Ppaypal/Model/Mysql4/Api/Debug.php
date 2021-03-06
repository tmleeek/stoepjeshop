<?php
/**
 * pay.nl ppaypal Api Debug Resource
 *
 * @category    PayNL
 * @package     PayNL_Ppaypal
 * @author      Sebastian Berm
 * @copyright   Copyright (c) 2009-2010 TinTel
 */
class PayNL_Ppaypal_Model_Mysql4_Api_Debug extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('ppaypal/api_debug', 'debug_id');
    }
}