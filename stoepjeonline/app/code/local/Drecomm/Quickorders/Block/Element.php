<?php
class Drecomm_Quickorders_Block_Element extends Mage_Core_Block_Template
{
	public function highlightSelection($text,$selection)
	{
		$pos = strpos($text,$selection);
		if(stristr($text,$selection)) {
			$tmpText = '<span style="color:#FC8911">'. $selection .'</span>'; //.substr($text,-(strlen($text)-strlen($selection)));
			$hText  = str_replace($selection,$tmpText,$text);
		} else {
			$hText = $text;
		}
		return $hText;
	}
}
