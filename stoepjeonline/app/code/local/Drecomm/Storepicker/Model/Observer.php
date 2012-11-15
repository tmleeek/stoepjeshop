<?php

class Drecomm_Storepicker_Model_Observer {
	//this is hook to Magento's event dispatched before action is run
	public function hookToControllerActionPreDispatch($observer) {
		$currentAction = $observer->getEvent()->getControllerAction()->getFullActionName();
		$storepickerId = Mage::getSingleton('core/session')->getStorepickerId();

        //if (empty($storepickerId) && $currentAction != 'cms_index_index' && $currentAction != 'storepicker_index_index' && $currentAction != 'storepicker_admin_index_index') {
        if(empty($storepickerId) && $currentAction == 'checkout_onepage_index') {
			/*if ($currentAction == 'catalog_category_view' || $currentAction == 'cms_page_view') {
				$currentUrl = 'http://'. $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
				Mage::getSingleton('core/session')->setStorepickerRedirect($currentUrl);
			}*/
            $currentUrl = 'http://'. $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
            Mage::getSingleton('core/session')->setStorepickerRedirect($currentUrl);

			$storepickerIndexUrl = Mage::getUrl('storepicker/index/index');
			Mage::app()->getFrontController()->getResponse()->setRedirect($storepickerIndexUrl);
		}
	}
}
