<?php
	class Drecommbv_Commercebug_Model_Collector extends Mage_Core_Helper_Abstract
	{
		protected $controller;
		protected $layout;
		protected $request;
		
		protected $models = array();
		protected $blocks = array();

		protected $_singleCollectors=array();		
		public function registerSingleCollector(Drecommbv_Commercebug_Model_Observingcollector $object)
		{
			if(!in_array($object, $this->_singleCollectors))
			{
				$this->_singleCollectors[] = $object;
			}
			return $this;
		}		
				
		//renders as json.
		public function asJson()
		{
			$json = new stdClass();
			
			foreach($this->_singleCollectors as $single_collector)
			{
				$json = $single_collector->addToObjectForJsonRender($json);	
			}

			$json = Mage::getSingleton('commercebug/jslabels')->addTableLabelsToJson($json);
			
			$json = Mage::getSingleton('commercebug/jsonbroker')->jsonEncode($json); 
			
			#$message = __CLASS__ . 'Serialized:' . $json;
			$message = Mage::helper('commercebug/log')->format($json);
			Mage::helper('commercebug/log')->log($message);						
			return $json;			
		}
		
		private function getClassFile($className)
		{
			$r = new ReflectionClass($className);
			return $r->getFileName();		
		}
	}