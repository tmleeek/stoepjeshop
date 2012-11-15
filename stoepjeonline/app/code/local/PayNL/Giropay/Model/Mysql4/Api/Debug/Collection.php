<?php
/**
 * Mrcash Debug Resource Collection
 *
 * @category    PayNL
 * @package     PayNL_Giropay
 * @author      Sebastian Berm
 * @copyright   Copyright (c) 2009-2010 TinTel
 */
class PayNL_Giropay_Model_Mysql4_Api_Debug_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('giropay/api_debug');
    }
}