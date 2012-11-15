<?php
/**
 * Pay.nl show issuers for ccard
 *
 * @category    PayNL
 * @package     PayNL_Ccard
 * @author      Sebastian Berm
 * @copyright   Copyright (c) 2009-2010 TinTel

 */

class PayNL_Ccard_Block_Ccard_Form extends Mage_Payment_Block_Form
{
    protected function _construct()
    {
        $this->setTemplate('paynl/ccard/form.phtml');
        parent::_construct();
    }
}
?>
