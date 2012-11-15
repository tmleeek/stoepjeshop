<?php
/**
* @copyright Amasty.
*/
class Amasty_Stockstatus_Model_Observer
{
    public function onModelSaveBefore($observer)
    {
        $model = $observer->getObject();
        if ($model instanceof Mage_Catalog_Model_Resource_Eav_Attribute)
        {
            if ('custom_stock_status' == $model->getAttributeCode())
            {
                Mage::getModel('amstockstatus/range')->clear(); // deleting all old values
                $ranges = Mage::app()->getRequest()->getPost('amstockstatus_range');
                // saving quantity ranges
                if ($ranges && is_array($ranges) && !empty($ranges))
                {
                    foreach ($ranges as $range)
                    {
                        $data = array(
                            'qty_from'   => $range['from'],
                            'qty_to'     => $range['to'],
                            'status_id'  => $range['status'],
                        );
                        $rangeModel = Mage::getModel('amstockstatus/range');
                        $rangeModel->setData($data);
                        $rangeModel->save();
                    }
                }
            }
        }
    }
}