<?php
/**
 * Redirection to payment screen for maestro for pay.nl
 *
 * @category    PayNL
 * @package     PayNL_Maestro
 * @author      Sebastian Berm
 * @copyright   Copyright (c) 2009-2010 TinTel
 */

class PayNL_Maestro_Block_Maestro_Redirect extends Mage_Core_Block_Abstract
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