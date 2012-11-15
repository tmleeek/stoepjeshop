<?php
/**
 * Pay.nl show issuers for clickandbuy
 *
 * @category    PayNL
 * @package     PayNL_Clickandbuy
 * @author      Sebastian Berm
 * @copyright   Copyright (c) 2009-2010 TinTel

 */

class PayNL_Clickandbuy_Block_Clickandbuy_Form extends Mage_Payment_Block_Form
{
    protected function _construct()
    {
        $this->setTemplate('paynl/clickandbuy/form.phtml');
        parent::_construct();
    }
}
?>
