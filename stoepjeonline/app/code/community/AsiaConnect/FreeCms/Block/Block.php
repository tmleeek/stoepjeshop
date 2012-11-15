<?php
class AsiaConnect_FreeCms_Block_Block extends Mage_Core_Block_Abstract
{
    protected function _toHtml()
    {
		if (!$this->_beforeToHtml()) {
			return '';
		}
        $html = '';
        if ($blockId = $this->getBlockId()) {
            $block = Mage::getModel('cms/block')
                ->setStoreId(Mage::app()->getStore()->getId())
                ->load($blockId);
            if (!$block->getIsActive()) {
                $html = '';
            } else {
                $content = $block->getContent();

                $processor = Mage::getModel('core/email_template_filter');
                $html = $processor->filter($content);
            }
        }
        return $html;
    }
}
