<?php
/**
 * Pay.nl show issuers for mrcash
 *
 * @category    PayNL
 * @package     PayNL_Giropay
 * @author      Sebastian Berm
 * @copyright   Copyright (c) 2009-2010 TinTel

 */

class PayNL_Giropay_Block_Giropay_Form extends Mage_Payment_Block_Form
{
    protected function _construct()
    {
        $this->setTemplate('paynl/giropay/form.phtml');
        parent::_construct();
    }
}
?>
