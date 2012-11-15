<?php
/**
 * 
 * @author Sergey Gozhedrianov
 *
 */
class Drecomm_Xmlrpc_Model_Order_Api extends Mage_Sales_Model_Order_Api
{
    
    /**
     * Retrieve list of orders by filters
     *
     * @param array $filters
     * @return array
     */
	public function items($filters = null) {
		// @TODO: Shipping address for all customers, Billing Address for non-AV users: table sales_flat_order_address
        $collection = Mage::getResourceModel('sales/order_collection')
            ->removeAllFieldsFromSelect()
            ->addFieldToSelect('customer_email')
			->addFieldToSelect('increment_id')
			->addAttributeToFilter('main_table.status', 'pending')
            ->join('order_item', 'order_item.order_id = main_table.entity_id', array('sku' => 'order_item.sku', 'qty' => 'order_item.qty_ordered'));

        $select = $collection->getSelect();
        $select->joinLeft(array('customer' => 'customer_entity'), 'customer.email = main_table.customer_email', '');
		$select->joinLeft(array('group' => 'customer_group'), 'customer.group_id = group.customer_group_id', array('customer_group' =>'customer_group_av_id'));

		$rows = Mage::getModel('core/resource')->getConnection('core_read')->fetchAll($select);

		$result = array();
        foreach ($rows as $row) {
			// Workaround Java NULL handling:  Check for anonymous web users
			$row['customer_group'] = is_null($row['customer_group']) ? "" : $row['customer_group'];
            $result[] = $row;
		}

        return $result;
    }
    
    /**
     * Change Order Status
     * @param array $orderData
     */
    public function change($orderData)
	{
		// @TODO: check if status and state are set correctly
        if (!isset($orderData['increment_id']) || !isset($orderData['status'])) {
            $this->_fault('data_invalid', 'Enter valid data');
        }
        $order = Mage::getModel('sales/order')->loadByIncrementId($orderData['increment_id']);
        if (!$order->getId()) {
            $this->_fault('not_exists');
        }

		switch ($orderData['status']) {
			case 'cancelled':
				$order->cancel();
				break;
			case 'processing':
				$order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true);
				break;
			case 'complete':
				if ($order->getStatus() == Mage_Sales_Model_Order::STATE_COMPLETE) {
					break;
				}
				$comment = $orderData['comment'] ? $orderData['comment'] : '';
				$notify  = $orderData['notify']  ? true : false;
				$completeorder = new Mage_Sales_Model_Order_Api();
				$completeorder->addComment($orderData['increment_id'], Mage_Sales_Model_Order::STATE_COMPLETE, $comment, $notify);
				break;
			default:
				$this->_fault('status_not_changed', 'Unknown state: '.$orderData['status']);
		}
        try {
            $order->save();
        } catch (Mage_Core_Exception $e) {
            $this->_fault('status_not_changed', $e->getMessage());
        }
        $response = new Zend_XmlRpc_Response($orderData['status']);
        return $response;
    }
}
