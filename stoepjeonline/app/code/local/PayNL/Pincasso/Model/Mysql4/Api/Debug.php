<?php
/**
 * pay.nl pincasso Api Debug Resource
 *
 * @category    PayNL
 * @package     PayNL_Pincasso
 * @author      Sebastian Berm
 * @copyright   Copyright (c) 2009-2010 TinTel
 */
class PayNL_Pincasso_Model_Mysql4_Api_Debug extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('pincasso/api_debug', 'debug_id');
    }
}