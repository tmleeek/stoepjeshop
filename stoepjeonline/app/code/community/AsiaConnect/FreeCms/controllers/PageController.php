<?php
class AsiaConnect_FreeCms_PageController extends Mage_Core_Controller_Front_Action
{
	public function viewAction()
	{
        $pageId = $this->getRequest()->getParam('page_id', $this->getRequest()->getParam('id', false));
		if (!Mage::helper('cms/page')->renderPage($this, $pageId)) {
            $this->_forward('noRoute');
        }
	}
}