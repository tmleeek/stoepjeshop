<?php
/**
 * Pay.nl maestro info block
 *
 * @category    PayNL
 * @package     PayNL_Maestro
 * @author      Sebastian Berm
 * @copyright   Copyright (c) 2009-2010 TinTel
 */

class PayNL_Maestro_Block_Maestro_Info extends Mage_Payment_Block_Info
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('paynl/maestro/info.phtml');
    }

    public function toPdf()
    {
        $this->setTemplate('paynl/maestro/pdf/info.phtml');
        return $this->toHtml();
    }
}
?>
