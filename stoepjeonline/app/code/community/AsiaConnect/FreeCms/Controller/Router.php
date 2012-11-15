<?php
class AsiaConnect_FreeCms_Controller_Router extends Mage_Core_Controller_Varien_Router_Abstract
{
    public function initControllerRouters($observer)
    {
        $front = $observer->getEvent()->getFront();

        $cms = new AsiaConnect_FreeCms_Controller_Router();
        $front->addRouter('cms', $cms);
    }

    public function match(Zend_Controller_Request_Http $request)
    {
        if (!Mage::isInstalled()) {
            Mage::app()->getFrontController()->getResponse()
                ->setRedirect(Mage::getUrl('install'))
                ->sendResponse();
            exit;
        }

        $identifier = trim($request->getPathInfo(), '/');

        $page = Mage::getModel('cms/page');
        $pageId = $page->checkIdentifier($identifier, Mage::app()->getStore()->getId());
        if (!$pageId) {
            return false;
        }

        $request->setModuleName(isset($d[0]) ? $d[0] : 'cms')
            ->setControllerName(isset($d[1]) ? $d[1] : 'page')
            ->setActionName(isset($d[2]) ? $d[2] : 'view')
            ->setParam('page_id', $pageId);
		$request->setAlias(
			Mage_Core_Model_Url_Rewrite::REWRITE_REQUEST_PATH_ALIAS,
			$identifier
		);
        return true;
    }
}