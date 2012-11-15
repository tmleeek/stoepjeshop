<?php 
	class Drecommbv_Commercebug_Model_Collectorcontroller extends Drecommbv_Commercebug_Model_Observingcollector
	{
		public function collectInformation($observer)
		{
			if(!$this->_isOn($observer))
			{
				return;
			}		
			$collector = $this->getCollector();
			$collector = $this->getCollector();				
			$this->setControllerAction($observer->getControllerAction());
			$this->setRequest(Mage::app()->getRequest());
		}
		
		public function addToObjectForJsonRender($json)
		{
			$json->controller 				= new stdClass();
			if(is_object($this->getControllerAction()))
			{			
				$json->controller->className 		= get_class($this->getControllerAction());
				$json->controller->fileName			= $this->getClassFile($json->controller->className);
				$json->controller->fullActionName 	= $this->getControllerAction()->getFullActionName();		
			}		
			$json->request 					= new stdClass();
			if(is_object($this->getRequest()))
			{
				$json->request->moduleName 		= $this->getRequest()->getModuleName();
				$json->request->controllerName 	= $this->getRequest()->getControllerName();
				$json->request->actionName 		= $this->getRequest()->getActionName(); 		
				$json->request->pathInfo		= $this->getRequest()->getPathInfo();		
			}			
			return $json;
		}
		
		public function createKeyName()
		{
			return 'controller';
		}
	}