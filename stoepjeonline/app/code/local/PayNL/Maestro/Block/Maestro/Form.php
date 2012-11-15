<?php
/**
 * Pay.nl show issuers for maestro
 *
 * @category    PayNL
 * @package     PayNL_Maestro
 * @author      Sebastian Berm
 * @copyright   Copyright (c) 2009-2010 TinTel

 */

class PayNL_Maestro_Block_Maestro_Form extends Mage_Payment_Block_Form
{
    protected function _construct()
    {
        $this->setTemplate('paynl/maestro/form.phtml');
        parent::_construct();
    }
}
?>
