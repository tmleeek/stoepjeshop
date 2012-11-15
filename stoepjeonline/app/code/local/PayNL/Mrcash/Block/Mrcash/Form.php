<?php
/**
 * Pay.nl show issuers for mrcash
 *
 * @category    PayNL
 * @package     PayNL_Mrcash
 * @author      Sebastian Berm
 * @copyright   Copyright (c) 2009-2010 TinTel

 */

class PayNL_Mrcash_Block_Mrcash_Form extends Mage_Payment_Block_Form
{
    protected function _construct()
    {
        $this->setTemplate('paynl/mrcash/form.phtml');
        parent::_construct();
    }
}
?>
