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
if (Mage::helper('multifees')->isOldVersion()) {

    class MageWorx_MultiFees_Model_Mysql4_Sales_Report_Order_Collection {

    }

} else {

    class MageWorx_MultiFees_Model_Mysql4_Sales_Report_Order_Collection extends Mage_Sales_Model_Mysql4_Report_Order_Collection
    {
        protected function _getSelectedColumns()
        {
            if (version_compare(Mage::getVersion(), '1.4.1', '<')) {
                if ('month' == $this->_period) {
                    $this->_periodFormat = 'DATE_FORMAT(period, \'%Y-%m\')';
                } elseif ('year' == $this->_period) {
                    $this->_periodFormat = 'EXTRACT(YEAR FROM period)';
                } else {
                    $this->_periodFormat = 'period';
                }

                if (!$this->isTotals()) {
                    $this->_selectedColumns = array(
                        'period' => $this->_periodFormat,
                        'orders_count' => 'SUM(orders_count)',
                        'total_qty_ordered' => 'SUM(total_qty_ordered)',
                        'base_profit_amount' => 'SUM(base_profit_amount)',
                        'base_subtotal_amount' => 'SUM(base_subtotal_amount)',
                        'base_multifees_amount' => 'SUM(base_multifees_amount)',
                        'base_tax_amount' => 'SUM(base_tax_amount)',
                        'base_shipping_amount' => 'SUM(base_shipping_amount)',
                        'base_discount_amount' => 'SUM(base_discount_amount)',
                        'base_grand_total_amount' => 'SUM(base_grand_total_amount)',
                        'base_invoiced_amount' => 'SUM(base_invoiced_amount)',
                        'base_refunded_amount' => 'SUM(base_refunded_amount)',
                        'base_canceled_amount' => 'SUM(base_canceled_amount)'
                    );
                }

                if ($this->isTotals()) {
                    $this->_selectedColumns = $this->getAggregatedColumns();
                }

                return $this->_selectedColumns;
            } else {
                if ('month' == $this->_period) {
                    $this->_periodFormat = 'DATE_FORMAT(period, \'%Y-%m\')';
                } elseif ('year' == $this->_period) {
                    $this->_periodFormat = 'EXTRACT(YEAR FROM period)';
                } else {
                    $this->_periodFormat = 'period';
                }

                if (!$this->isTotals()) {
                    $this->_selectedColumns = array(
                        'period' => $this->_periodFormat,
                        'orders_count' => 'SUM(orders_count)',
                        'total_qty_ordered' => 'SUM(total_qty_ordered)',
                        'total_qty_invoiced' => 'SUM(total_qty_invoiced)',
                        'total_income_amount' => 'SUM(total_income_amount)',
                        'total_revenue_amount' => 'SUM(total_revenue_amount)',
                        'total_profit_amount' => 'SUM(total_profit_amount)',
                        'total_invoiced_amount' => 'SUM(total_invoiced_amount)',
                        'total_canceled_amount' => 'SUM(total_canceled_amount)',
                        'total_paid_amount' => 'SUM(total_paid_amount)',
                        'total_refunded_amount' => 'SUM(total_refunded_amount)',
                        'total_tax_amount' => 'SUM(total_tax_amount)',
                        'total_tax_amount_actual' => 'SUM(total_tax_amount_actual)',
                        'total_shipping_amount' => 'SUM(total_shipping_amount)',
                        'total_shipping_amount_actual' => 'SUM(total_shipping_amount_actual)',
                        'total_discount_amount' => 'SUM(total_discount_amount)',
                        'total_discount_amount_actual' => 'SUM(total_discount_amount_actual)',
                        'total_multifees_amount' => 'SUM(total_multifees_amount)',
                    );
                }

                if ($this->isTotals()) {
                    $this->_selectedColumns = $this->getAggregatedColumns();
                }

                return $this->_selectedColumns;
            }
        }

    }

}
