<?php
	class Drecommbv_Commercebug_Helper_Log
	{
	    public function log($message, $level=null, $file = '')
	    {	    
			if(Mage::getStoreConfig('commercebug/options/should_log'))
			{
				Mage::Log($message, $level, $file);
			}	    	
	    }	    
	    
	    public function format($thing)
	    {
	    	$alias = Mage::getStoreConfig('commercebug/options/log_format_class');
	    	if($alias == 'custom')
	    	{
	    		$alias = Mage::getStoreConfig('commercebug/options/log_format_class_custom');
	    	}
	    	$helper = Mage::helper($alias);
	    	if($helper)
	    	{
		    	return $helper->format($thing);
	    	}
	    	Mage::Log(sprintf('Could not instantiate helper class: %s',$alias));
			return $thing;    	
			#return __CLASS__ . 'Serialized:' . $thing;	    	
	    }
	}