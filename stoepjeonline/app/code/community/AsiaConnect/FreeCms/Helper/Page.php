<?php
class AsiaConnect_FreeCms_Helper_Page extends Mage_Core_Helper_Abstract
{
    public function renderPage(Mage_Core_Controller_Front_Action $action, $pageId=null)
    {
        $page = Mage::getSingleton('cms/page');
        if (!is_null($pageId) && $pageId!==$page->getId()) {
            $page->setStoreId(Mage::app()->getStore()->getId());
            if (!$page->load($pageId)) {
                return false;
            }
        }

        if (!$page->getId()) {
            return false;
        }

        if ($page->getCustomTheme()) {
            $apply = true;
            $today = Mage::app()->getLocale()->date()->toValue();
            if (($from = $page->getCustomThemeFrom()) && strtotime($from)>$today) {
                $apply = false;
            }
            if ($apply && ($to = $page->getCustomThemeTo()) && strtotime($to)<$today) {
                $apply = false;
            }
            if ($apply) {
                list($package, $theme) = explode('/', $page->getCustomTheme());
                Mage::getSingleton('core/design_package')
                    ->setPackageName($package)
                    ->setTheme($theme);
            }
        }

        $action->loadLayout(array('default', 'cms_page'), false, false);
        $action->getLayout()->getUpdate()->addUpdate($page->getLayoutUpdateXml());
        $action->generateLayoutXml()->generateLayoutBlocks();

        if ($storage = Mage::getSingleton('catalog/session')) {
            $action->getLayout()->getMessagesBlock()->addMessages($storage->getMessages(true));
        }

        if ($storage = Mage::getSingleton('checkout/session')) {
            $action->getLayout()->getMessagesBlock()->addMessages($storage->getMessages(true));
        }

        $action->renderLayout();

        return true;
    }
}