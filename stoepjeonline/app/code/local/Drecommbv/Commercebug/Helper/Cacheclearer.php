<?php
	class Drecommbv_Commercebug_Helper_Cacheclearer
	{
		public function clearCache()
		{			
			Mage::app()->cleanCache();
		}
	}