<?php
class AsiaConnect_FreeCms_Block_Page extends Mage_Core_Block_Abstract
{
    public function getPage()
    {
        if (!$this->hasData('page')) {
            if ($this->getPageId()) {
                $page = Mage::getModel('cms/page')
                    ->setStoreId(Mage::app()->getStore()->getId())
                    ->load($this->getPageId(), 'identifier');
            } else {
                $page = Mage::getSingleton('cms/page');
            }
            $this->setData('page', $page);
        }
        return $this->getData('page');
    }

    protected function _prepareLayout()
    {
        $page = $this->getPage();

        // show breadcrumbs
        if (Mage::getStoreConfig('web/default/show_cms_breadcrumbs')
            && ($breadcrumbs = $this->getLayout()->getBlock('breadcrumbs'))
            && ($page->getIdentifier()!==Mage::getStoreConfig('web/default/cms_home_page'))
            && ($page->getIdentifier()!==Mage::getStoreConfig('web/default/cms_no_route'))) {
                $breadcrumbs->addCrumb('home', array('label'=>Mage::helper('cms')->__('Home'), 'title'=>Mage::helper('cms')->__('Go to Home Page'), 'link'=>Mage::getBaseUrl()));
                $breadcrumbs->addCrumb('cms_page', array('label'=>$page->getTitle(), 'title'=>$page->getTitle()));
        }

        if ($root = $this->getLayout()->getBlock('root')) {
            $template = (string)Mage::getConfig()->getNode('global/cms/layouts/'.$page->getRootTemplate().'/template');
            $root->setTemplate($template);
            $root->addBodyClass('cms-'.$page->getIdentifier());
        }

        if ($head = $this->getLayout()->getBlock('head')) {
            $head->setTitle($page->getTitle());
            $head->setKeywords($page->getMetaKeywords());
            $head->setDescription($page->getMetaDescription());
        }
    }

    protected function _toHtml()
    {
        $processor = Mage::getModel('core/email_template_filter');
        $html = $processor->filter($this->getPage()->getContent());
        $html = $this->getMessagesBlock()->getGroupedHtml() . $html;
        return $html;
    }
}
