<?php
/**
 * @copyright  Copyright (c) 2009 AITOC, Inc. 
 */
class Aitoc_Aitsys_Helper_Rewriter extends Aitoc_Aitsys_Helper_Data
{
    public function getOrderConfig()
    {
        $order = unserialize( (string) Mage::getConfig()->getNode('default/aitsys_rewriter_classorder') );
        return $order;
    }
    
    public function saveOrderConfig($order)
    {
        Mage::getConfig()->saveConfig('aitsys_rewriter_classorder', serialize($order));
        Mage::app()->cleanCache();
    }
    
    public function mergeOrderConfig($order)
    {
        $currentOrder = unserialize( (string) Mage::getConfig()->getNode('default/aitsys_rewriter_classorder') );
        if (!$currentOrder)
        {
            $newOrder = $order;
        } else
        {
            $newOrder = array_merge($currentOrder, $order);
        }
        $this->saveOrderConfig($newOrder);
    }
    
    public function removeOrderConfig()
    {
        Mage::getConfig()->deleteConfig('aitsys_rewriter_classorder');
        Mage::app()->cleanCache();
    }
}
