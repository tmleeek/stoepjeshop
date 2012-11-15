<?php
class AsiaConnect_FreeCms_Model_Block extends Mage_Core_Model_Abstract
{
    const CACHE_TAG     = 'cms_block';
    protected $_cacheTag= 'cms_block';

    protected function _construct()
    {
        $this->_init('cms/block');
    }
}
