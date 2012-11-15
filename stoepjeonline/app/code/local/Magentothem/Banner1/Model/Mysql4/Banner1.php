<?php
/*------------------------------------------------------------------------
# Websites: http://www.magentothem.com/
-------------------------------------------------------------------------*/ 
class Magentothem_Banner1_Model_Mysql4_Banner1 extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the banner1_id refers to the key field in your database table.
        $this->_init('banner1/banner1', 'banner1_id');
    }
}