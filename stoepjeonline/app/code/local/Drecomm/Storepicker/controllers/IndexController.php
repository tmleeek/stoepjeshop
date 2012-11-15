<?php

class Drecomm_Storepicker_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
		$id = $this->getRequest()->getParam('id');

		//Mage::getSingleton('core/session')->setStorepickerId();

		if (!empty($id)) {
			$model = Mage::getModel('storepicker/store')->load($id);
			//die(print_r($model, 1));

			Mage::getSingleton('core/session')->setStorepickerId($id);
			//Mage::getSingleton('core/session')->getStorepickerId();

			$redirectUrl = Mage::getSingleton('core/session')->getStorepickerRedirect();
			if (empty($redirectUrl)) {
				$redirectUrl = Mage::getUrl();
			}

			$this->_redirectUrl($redirectUrl);
		} else {
			$this->loadLayout();

			$this->renderLayout();
		}
    }
}
