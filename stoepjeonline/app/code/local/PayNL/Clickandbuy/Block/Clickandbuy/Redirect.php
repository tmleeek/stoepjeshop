<?php
/**
 * Redirection to payment screen for clickandbuy for pay.nl
 *
 * @category    PayNL
 * @package     PayNL_Clickandbuy
 * @author      Sebastian Berm
 * @copyright   Copyright (c) 2009-2010 TinTel
 */

class PayNL_Clickandbuy_Block_Clickandbuy_Redirect extends Mage_Core_Block_Abstract
{
    protected function _toHtml()
    {
        $html = '<html><body>';
        $html.= $this->getMessage();
        $html.= '<script type="text/javascript">location.href = "' . $this->getRedirectUrl() . '";</script>';
        $html.= '</body></html>';
        return $html;
    }
}
?>