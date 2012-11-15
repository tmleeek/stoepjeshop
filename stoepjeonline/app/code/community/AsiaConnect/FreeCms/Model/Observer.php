<?php
class AsiaConnect_FreeCms_Model_Observer
{
    public function noRoute($observer)
    {
        $observer->getEvent()->getStatus()
            ->setLoaded(true)
            ->setForwardModule('cms')
            ->setForwardController('index')
            ->setForwardAction('cmsNoRoute');
    }

}
