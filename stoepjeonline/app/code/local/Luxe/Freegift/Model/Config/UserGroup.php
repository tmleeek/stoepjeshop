<?php
/**
 * Luxe 
 *
 * @category   Luxe 
 * @package    Luxe_Freegift
 * @copyright  Copyright (c) 2009-2010 Luxe Soft
 */


class Luxe_Freegift_Model_Config_UserGroup
{
    public function toOptionArray()
    {
        return Mage::getResourceModel('customer/group_collection')->load()->toOptionArray();
    }
}
