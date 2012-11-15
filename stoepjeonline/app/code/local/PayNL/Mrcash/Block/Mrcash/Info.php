<?php
/**
 * Pay.nl mrcash info block
 *
 * @category    PayNL
 * @package     PayNL_Mrcash
 * @author      Sebastian Berm
 * @copyright   Copyright (c) 2009-2010 TinTel
 */

class PayNL_Mrcash_Block_Mrcash_Info extends Mage_Payment_Block_Info
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('paynl/mrcash/info.phtml');
    }

    public function toPdf()
    {
        $this->setTemplate('paynl/mrcash/pdf/info.phtml');
        return $this->toHtml();
    }
}
?>
