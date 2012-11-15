<?php
/**
 * Pay.nl show issuers for walliecard
 *
 * @category    PayNL
 * @package     PayNL_Walliecard
 * @author      Sebastian Berm
 * @copyright   Copyright (c) 2009-2010 TinTel

 */

class PayNL_Walliecard_Block_Walliecard_Form extends Mage_Payment_Block_Form
{
    protected function _construct()
    {
        $this->setTemplate('paynl/walliecard/form.phtml');
        parent::_construct();
    }
}
?>
