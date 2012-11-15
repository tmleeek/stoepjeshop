<?php
class AsiaConnect_FreeCms_IndexController extends Mage_Core_Controller_Front_Action
{
	public function indexAction($coreRoute = null)
    {
    	$pageId = Mage::getStoreConfig('web/default/cms_home_page');
        if (!Mage::helper('cms/page')->renderPage($this, $pageId)) {
            $this->_forward('defaultIndex');
        }
    }

    public function defaultIndexAction()
    {
        $this->getResponse()->setHeader('HTTP/1.1','404 Not Found');
        $this->getResponse()->setHeader('Status','404 File not found');

    	$this->loadLayout();
    	$this->renderLayout();
    }

    public function noRouteAction($coreRoute = null)
    {
        $this->getResponse()->setHeader('HTTP/1.1','404 Not Found');
        $this->getResponse()->setHeader('Status','404 File not found');

        $pageId = Mage::getStoreConfig('web/default/cms_no_route');
        if (!Mage::helper('cms/page')->renderPage($this, $pageId)) {
            $this->_forward('defaultNoRoute');
        }
    }

    public function defaultNoRouteAction()
    {
        $this->getResponse()->setHeader('HTTP/1.1','404 Not Found');
        $this->getResponse()->setHeader('Status','404 File not found');

    	$this->loadLayout();
    	$this->renderLayout();
    }
}
