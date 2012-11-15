<?php
class AsiaConnect_FreeCms_Model_Page extends Mage_Core_Model_Abstract
{

    const NOROUTE_PAGE_ID = 'no-route';

    protected $_eventPrefix = 'cms_page';

    protected function _construct()
    {
        $this->_init('cms/page');
    }

    public function load($id, $field=null)
    {
        if (is_null($id)) {
            return $this->noRoutePage();
        }
        return parent::load($id, $field);
    }

    public function noRoutePage()
    {
        $this->setData($this->load(self::NOROUTE_PAGE_ID, $this->getIdFieldName()));
        return $this;
    }

    public function checkIdentifier($identifier, $storeId)
    {
        return $this->_getResource()->checkIdentifier($identifier, $storeId);
    }
}
