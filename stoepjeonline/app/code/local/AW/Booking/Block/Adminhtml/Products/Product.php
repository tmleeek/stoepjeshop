<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 * 
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Booking
 * @copyright  Copyright (c) 2009-2010 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 */

class AW_Booking_Block_Adminhtml_Products_Product extends Mage_Adminhtml_Block_Widget_Grid_Container
{
	public function __construct(){
		parent::__construct();
		$this->_controller = 'adminhtml_products_product';
		$this->_blockGroup = 'booking';
		$this->_headerText = Mage::helper('booking')->__("Product '%s' Details", $this->getProduct()->getName());
		
		$this->_removeButton('add');
	}
	protected function _prepareLayout(){
		
		$this->_addButton('back',
		
			array(
				'label'     => $this->__('Back'),
				'onclick'   => 'setLocation(\'' . $this->getUrl('*/*') .'\')',
				'class'     => 'back'
			)
		);
		parent::_prepareLayout();
	}
	protected function getProduct(){
		return Mage::getModel('catalog/product')->load($this->getRequest()->getParam('id'));
	}
}
