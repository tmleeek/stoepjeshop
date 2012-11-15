<?php

/**
 * Bestseller view block
 * 
 * @author Branko Ajzele
 * @category ActiveCodeline
 * @package ActiveCodeline_Bestseller
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ActiveCodeline_Bestseller_Block_Bestseller extends Mage_Core_Block_Template
{	
	/**
	 * "Connects" to Model class ActiveCodeline_Bestseller_Model_Bestseller and retrieves the
	 * bestsellers products.
	 * 
	 * We can later use "$this->fetchBestsellers()" from view file to output bestseller products.  
	 */
	public function fetchBestsellers($totalToFetch = 6)
	{
		$bestsellers = Mage::getModel('bestseller/bestseller');
		$bestsellers = $bestsellers->getBestsellers($totalToFetch);		
		return $bestsellers;
	}
}
