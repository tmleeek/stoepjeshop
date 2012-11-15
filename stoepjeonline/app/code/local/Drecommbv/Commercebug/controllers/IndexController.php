<?php
	class Drecommbv_Commercebug_IndexController extends Mage_Core_Controller_Front_Action
	{		
		public function indexAction()
		{

// 			$currentStore = Mage::app()->getStore()->getStoreId();
// 	
// 			$designChange = Mage::getSingleton('core/design')
// 				->loadChange($currentStore);

			$designChange = Mage::getSingleton('core/design')
			->loadChange(Mage::app()->getStore()->getStoreId());	
			
			var_dump($designChange->getData());
// 			if ($designChange->getData()) {
// 				$designPackage->setPackageName($designChange->getPackage())
// 					->setTheme($designChange->getTheme());
// 			}
				
		}
	}