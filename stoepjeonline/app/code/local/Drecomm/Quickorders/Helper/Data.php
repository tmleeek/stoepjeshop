<?php

class Drecomm_Quickorders_Helper_Data extends Mage_Core_Helper_Abstract
{

	public function getStockQuantity($qty)
	{
		if($qty>50)
		{
			return '>50';
		}
		else
		{
			return $qty;
		}
	}

	public function getQtyClass($qty)
	{
		return $qty > 0 ? "" : "-zero";
	}

}