<?php
/**
 * Pay.nl pincasso info block
 *
 * @category    PayNL
 * @package     PayNL_Pincasso
 * @author      Sebastian Berm
 * @copyright   Copyright (c) 2009-2010 TinTel
 */

class PayNL_Pincasso_Block_Pincasso_Info extends Mage_Payment_Block_Info
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('paynl/pincasso/info.phtml');
    }

    public function toPdf()
    {
        $this->setTemplate('paynl/pincasso/pdf/info.phtml');
        return $this->toHtml();
    }
}
?>
