<?php

class Devinc_Dailydeal_Model_Dailydeal extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('dailydeal/dailydeal');
    }
}