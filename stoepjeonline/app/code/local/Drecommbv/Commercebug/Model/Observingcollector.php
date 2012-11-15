<?php
	abstract class Drecommbv_Commercebug_Model_Observingcollector extends Varien_Object
	{
		protected $_storeIsSet=false;
		/**
		* Entry point
		*/	
		abstract public function collectInformation($observer);
		
		/**
		* Not using this yet, but may in future
		*/		
		abstract public function createKeyName();

		/**
		* Automatically passed top level stdClass object, client programmer
		* should populate with whatever they want
		*/		
		abstract public function addToObjectForJsonRender($parent_object);		
		
		protected function getLayout()
		{
			return Mage::getSingleton('core/layout');;	
		}
		
		protected function getCollector()
		{
			return $collector = Mage::getSingleton('commercebug/collector')->registerSingleCollector($this);	
			
		}
	
		protected function getClassFile($className)
		{
			$r = new ReflectionClass($className);
			return $r->getFileName();		
		}

		/**
		* If you attempt to call getStoreConfig before a store is
		* set, Magento throws an internal exception that it also 
		* catches, resulting in a 404.  This doesn't happen in a stock
		* install, but some users are reporting it in the wild.
		*/
		protected function storeIsSet()
		{		
			if($this->_storeIsSet)
			{
				return true;
			}
			try
			{ 
				$store = Mage::app()->getSafeStore();
				if(is_numeric($store->getId()))
				{
					$this->_storeIsSet = true;
					return true;
				}
				return false;
			}
			catch(Exception $e)
			{
				return false;
			}    					
			//just in case
			return false;
		}
		
		protected function _isOn($observer)
		{
			//if we're observing a store object, always return true.  Otherwise we may
			//get caught in an endless recursive loop
			if($observer->getEvent()->getObject() instanceof Mage_Core_Model_Store)
			{
				return true;
			}
			
			if($this->storeIsSet())
			{
				return Mage::getSingleton(Mage::getStoreConfig('commercebug/options/access_class'))->isOn();
			}
			else
			{
				return true; //error on the side of collecting the information
			}
		}
	}