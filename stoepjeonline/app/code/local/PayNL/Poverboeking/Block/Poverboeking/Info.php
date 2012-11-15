<?php
/**
 * Pay.nl poverboeking info block
 *
 * @category    PayNL
 * @package     PayNL_Poverboeking
 * @author      Sebastian Berm
 * @copyright   Copyright (c) 2009-2010 TinTel
 */

class PayNL_Poverboeking_Block_Poverboeking_Info extends Mage_Payment_Block_Info
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('paynl/poverboeking/info.phtml');
    }

    public function toPdf()
    {
        $this->setTemplate('paynl/poverboeking/pdf/info.phtml');
        return $this->toHtml();
    }
}
?>
