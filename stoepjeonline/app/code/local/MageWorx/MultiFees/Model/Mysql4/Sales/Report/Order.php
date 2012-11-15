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

class MageWorx_MultiFees_Model_Mysql4_Sales_Report_Order extends Mage_Sales_Model_Mysql4_Report_Order
{
    public function aggregate($from = null, $to = null) {
        if (version_compare(Mage::getVersion(), '1.4.1', '<')) {
            $writeAdapter = $this->getWriteConnection();
            try {
                if (!is_null($from)) {
                    $from = $this->formatDate($from);
                }
                if (!is_null($to)) {
                    $from = $this->formatDate($to);
                }

                $tableName = $this->getTable('sales/order_aggregated_created');

                $writeAdapter->beginTransaction();

                if (is_null($from) && is_null($to)) {
                    $writeAdapter->query("TRUNCATE TABLE {$tableName}");
                } else {
                    $where = (!is_null($from)) ? "so.updated_at >= '{$from}'" : '';
                    if (!is_null($to)) {
                        $where .= ( !empty($where)) ? " AND so.updated_at <= '{$to}'" : "so.updated_at <= '{$to}'";
                    }

                    $subQuery = $writeAdapter->select();
                    $subQuery->from(array('so' => $this->getTable('sales/order')), array('DISTINCT DATE(so.created_at)'))
                            ->where($where);

                    $deleteCondition = 'DATE(period) IN (' . new Zend_Db_Expr($subQuery) . ')';
                    $writeAdapter->delete($tableName, $deleteCondition);
                }

                $columns = array(
                    'period' => 'DATE(e.created_at)',
                    'store_id' => 'e.store_id',
                    'order_status' => 'e.status',
                    'orders_count' => 'COUNT(e.entity_id)',
                    'total_qty_ordered' => 'SUM(e.total_qty_ordered)',
                    'base_profit_amount' => 'SUM(IFNULL(e.base_subtotal_invoiced, 0) * e.base_to_global_rate) + SUM(IFNULL(e.base_discount_refunded, 0) * e.base_to_global_rate) - SUM(IFNULL(e.base_subtotal_refunded, 0) * e.base_to_global_rate) - SUM(IFNULL(e.base_discount_invoiced, 0) * e.base_to_global_rate) - SUM(IFNULL(e.base_total_invoiced_cost, 0) * e.base_to_global_rate)',
                    'base_subtotal_amount' => 'SUM(e.base_subtotal * e.base_to_global_rate)',
                    'base_multifees_amount' => 'SUM(e.base_multifees_amount * e.base_to_global_rate)',
                    'base_tax_amount' => 'SUM(e.base_tax_amount * e.base_to_global_rate)',
                    'base_shipping_amount' => 'SUM(e.base_shipping_amount * e.base_to_global_rate)',
                    'base_discount_amount' => 'SUM(e.base_discount_amount * e.base_to_global_rate)',
                    'base_grand_total_amount' => 'SUM(e.base_grand_total * e.base_to_global_rate)',
                    'base_invoiced_amount' => 'SUM(e.base_total_paid * e.base_to_global_rate)',
                    'base_refunded_amount' => 'SUM(e.base_total_refunded * e.base_to_global_rate)',
                    'base_canceled_amount' => 'SUM(IFNULL(e.subtotal_canceled, 0) * e.base_to_global_rate)'
                );

                $select = $writeAdapter->select()
                                ->from(array('e' => $this->getTable('sales/order')), $columns)
                                ->where('e.state NOT IN (?)', array(
                                    Mage_Sales_Model_Order::STATE_PENDING_PAYMENT,
                                    Mage_Sales_Model_Order::STATE_NEW
                                ));

                if (!is_null($from) || !is_null($to)) {
                    $select->where("DATE(e.created_at) IN(?)", new Zend_Db_Expr($subQuery));
                }

                $select->group(new Zend_Db_Expr('1,2,3'));

                $writeAdapter->query("
                    INSERT INTO `{$tableName}` (" . implode(',', array_keys($columns)) . ") {$select}
                ");

                $select = $writeAdapter->select();
                $columns = array(
                    'period' => 'period',
                    'store_id' => new Zend_Db_Expr('0'),
                    'order_status' => 'order_status',
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
                $select->from($tableName, $columns)
                        ->where("store_id <> 0");

                if (!is_null($from) || !is_null($to)) {
                    $select->where("DATE(period) IN(?)", new Zend_Db_Expr($subQuery));
                }

                $select->group(array(
                    'period',
                    'order_status'
                ));

                $writeAdapter->query("
                    INSERT INTO `{$tableName}` (" . implode(',', array_keys($columns)) . ") {$select}
                ");

                $reportsFlagModel = Mage::getModel('reports/flag');
                $reportsFlagModel->setReportFlagCode(Mage_Reports_Model_Flag::REPORT_ORDER_FLAG_CODE);
                $reportsFlagModel->loadSelf();
                $reportsFlagModel->save();
            } catch (Exception $e) {
                $writeAdapter->rollBack();
                throw $e;
            }

            $writeAdapter->commit();
            return $this;
        } else {
            // convert input dates to UTC to be comparable with DATETIME fields in DB
            $from = $this->_dateToUtc($from);
            $to = $this->_dateToUtc($to);

            $this->_checkDates($from, $to);
            $this->_getWriteAdapter()->beginTransaction();

            try {
                if ($from !== null || $to !== null) {
                    $subSelect = $this->_getTableDateRangeSelect(
                                    $this->getTable('sales/order'),
                                    'created_at', 'updated_at', $from, $to
                    );
                } else {
                    $subSelect = null;
                }

                $this->_clearTableByDateRange($this->getMainTable(), $from, $to, $subSelect);

                $columns = array(
                    // convert dates from UTC to current admin timezone
                    'period' => "DATE(CONVERT_TZ(o.created_at, '+00:00', '" . $this->_getStoreTimezoneUtcOffset() . "'))",
                    'store_id' => 'o.store_id',
                    'order_status' => 'o.status',
                    'orders_count' => 'COUNT(o.entity_id)',
                    'total_qty_ordered' => 'SUM(oi.total_qty_ordered)',
                    'total_qty_invoiced' => 'SUM(oi.total_qty_invoiced)',
                    'total_income_amount' => 'SUM((o.base_grand_total - IFNULL(o.base_total_canceled, 0)) * o.base_to_global_rate)',
                    'total_revenue_amount' => 'SUM((o.base_total_paid - IFNULL(o.base_total_refunded, 0)) * o.base_to_global_rate)',
                    'total_profit_amount' => 'SUM((o.base_total_paid - IFNULL(o.base_total_refunded, 0) - IFNULL(o.base_tax_invoiced, 0) - IFNULL(o.base_shipping_invoiced, 0) - IFNULL(o.base_total_invoiced_cost, 0)) * o.base_to_global_rate)',
                    'total_invoiced_amount' => 'SUM(o.base_total_invoiced * o.base_to_global_rate)',
                    'total_canceled_amount' => 'SUM(o.base_total_canceled * o.base_to_global_rate)',
                    'total_paid_amount' => 'SUM(o.base_total_paid * o.base_to_global_rate)',
                    'total_refunded_amount' => 'SUM(o.base_total_refunded * o.base_to_global_rate)',
                    'total_tax_amount' => 'SUM((o.base_tax_amount - IFNULL(o.base_tax_canceled, 0)) * o.base_to_global_rate)',
                    'total_tax_amount_actual' => 'SUM((o.base_tax_invoiced - IFNULL(o.base_tax_refunded, 0)) * o.base_to_global_rate)',
                    'total_shipping_amount' => 'SUM((o.base_shipping_amount - IFNULL(o.base_shipping_canceled, 0)) * o.base_to_global_rate)',
                    'total_shipping_amount_actual' => 'SUM((o.base_shipping_invoiced - IFNULL(o.base_shipping_refunded, 0)) * o.base_to_global_rate)',
                    'total_discount_amount' => 'SUM((ABS(o.base_discount_amount) - IFNULL(o.base_discount_canceled, 0)) * o.base_to_global_rate)',
                    'total_discount_amount_actual' => 'SUM((o.base_discount_invoiced - IFNULL(o.base_discount_refunded, 0)) * o.base_to_global_rate)',
                    'total_multifees_amount' => 'SUM(o.base_multifees_amount * o.base_to_global_rate)',
                );

                $select = $this->_getWriteAdapter()->select();
                $selectOrderItem = $this->_getWriteAdapter()->select();

                $cols = array(
                    'order_id' => 'order_id',
                    'total_qty_ordered' => 'SUM(qty_ordered - IFNULL(qty_canceled, 0))',
                    'total_qty_invoiced' => 'SUM(qty_invoiced)',
                );
                $selectOrderItem->from($this->getTable('sales/order_item'), $cols)
                        ->group('order_id');
                if ($subSelect !== null) {
                    //$selectOrderItem->where($this->_makeConditionFromDateRangeSelect($subSelect, 'created_at'));
                }

                $select->from(array('o' => $this->getTable('sales/order')), $columns)
                        ->join(array('oi' => $selectOrderItem), 'oi.order_id = o.entity_id', array())
                        ->where('o.state NOT IN (?)', array(
                            Mage_Sales_Model_Order::STATE_PENDING_PAYMENT,
                            Mage_Sales_Model_Order::STATE_NEW,
                            Mage_Sales_Model_Order::STATE_CANCELED,
                        ));

                if ($subSelect !== null) {
                    $select->where($this->_makeConditionFromDateRangeSelect($subSelect, 'o.created_at'));
                }

                $select->group(array(
                    'period',
                    'store_id',
                    'order_status',
                ));

                $this->_getWriteAdapter()->query($select->insertFromSelect($this->getMainTable(), array_keys($columns)));

                // setup all columns to select SUM() except period, store_id and order_status
                foreach ($columns as $k => $v) {
                    $columns[$k] = 'SUM(' . $k . ')';
                }
                $columns['period'] = 'period';
                $columns['store_id'] = new Zend_Db_Expr('0');
                $columns['order_status'] = 'order_status';

                $select->reset();
                $select->from($this->getMainTable(), $columns)
                        ->where('store_id <> 0');

                if ($subSelect !== null) {
                    $select->where($this->_makeConditionFromDateRangeSelect($subSelect, 'period'));
                }

                $select->group(array(
                    'period',
                    'order_status'
                ));

                $this->_getWriteAdapter()->query($select->insertFromSelect($this->getMainTable(), array_keys($columns)));

                $this->_setFlagData(Mage_Reports_Model_Flag::REPORT_ORDER_FLAG_CODE);
            } catch (Exception $e) {
                $this->_getWriteAdapter()->rollBack();
                throw $e;
            }

            $this->_getWriteAdapter()->commit();
            return $this;
        }
    }
}
