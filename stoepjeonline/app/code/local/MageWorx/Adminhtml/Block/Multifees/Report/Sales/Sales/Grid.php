<?php
/**
 * MageWorx
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MageWorx EULA that is bundled with
 * this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.mageworx.com/LICENSE-1.0.html
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@mageworx.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extension
 * to newer versions in the future. If you wish to customize the extension
 * for your needs please refer to http://www.mageworx.com/ for more information
 * or send an email to sales@mageworx.com
 *
 * @category   MageWorx
 * @package    MageWorx_MultiFees
 * @copyright  Copyright (c) 2009 MageWorx (http://www.mageworx.com/)
 * @license    http://www.mageworx.com/LICENSE-1.0.html
 */

/**
 * Multi Fees extension
 *
 * @category   MageWorx
 * @package    MageWorx_MultiFees
 * @author     MageWorx Dev Team <dev@mageworx.com>
 */

class MageWorx_Adminhtml_Block_Multifees_Report_Sales_Sales_Grid extends Mage_Adminhtml_Block_Report_Sales_Sales_Grid
{
    protected function _prepareColumns()
    {
        $currency_code = $this->getCurrentCurrencyCode();

    	if (Mage::helper('multifees')->isOldVersion()) {
	        $this->addColumn('multifees_sum', array(
	            'header'        => Mage::helper('multifees')->__('Additional Fees'),
	            'type'          => 'currency',
	            'currency_code' => $currency_code,
	            'index'         => 'multifees_sum',
	            'total'         => 'sum',
	            'renderer'      => 'adminhtml/report_grid_column_renderer_currency',
	        ));
	        parent::_prepareColumns();

            foreach ($this->_columns as $_id => $_column) {
	            if ($_id == 'multifees_sum') continue;
	            $_columns[$_id] = $_column;
	            if ($_id == 'subtotal') {
	                $_columns['multifees_sum'] = $this->_columns['multifees_sum'];
	            }
	        }
	        $this->_columns = $_columns;
    	} else if (version_compare(Mage::getVersion(), '1.4.1', '<')) {
            $this->addColumnAfter('base_multifees_amount', array(
                'header'        => Mage::helper('multifees')->__('Additional Fees'),
                'type'          => 'currency',
                'currency_code' => $currency_code,
                'index'         => 'base_multifees_amount',
                'total'         => 'sum',
                'renderer'      => 'adminhtml/report_grid_column_renderer_currency',
                'sortable'      => false,
            ), 'base_subtotal_amount');

            parent::_prepareColumns();
    	} else {
        $this->addColumnAfter('total_multifees_amount', array(
                'header'        => Mage::helper('multifees')->__('Additional Fees'),
                'type'          => 'currency',
                'currency_code' => $currency_code,
                'index'         => 'total_multifees_amount',
                'total'         => 'sum',
                'renderer'      => 'adminhtml/report_grid_column_renderer_currency',
                'sortable'      => false,
            ), 'total_tax_amount');

            parent::_prepareColumns();
        }
        return $this;
    }
}
