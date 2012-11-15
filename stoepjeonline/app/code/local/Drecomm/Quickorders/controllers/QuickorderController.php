<?php

class Drecomm_Quickorders_QuickorderController extends Mage_Core_Controller_Front_Action {

    public function indexAction() {
        $this->loadLayout();
        $breadcrumbs = $this->getLayout()->getBlock('breadcrumbs');
        $breadcrumbs->addCrumb('home', array('label' => Mage::helper('cms')->__('Home'), 'title' => Mage::helper('cms')->__('Go to Home Page'), 'link' => Mage::getBaseUrl()));
        $breadcrumbs->addCrumb('quick_orders', array('label' => Mage::helper('cms')->__('Quick orders'), 'title' => Mage::helper('cms')->__('Quick orders')));
        $this->renderLayout();
    }

    public function loadAction() {
        $prodNr = $this->getRequest()->getParam('prod');
        $quantity = $this->getRequest()->getParam('qty');
        $prodNr = urldecode($prodNr);
        $productModel = Mage::getModel('catalog/product');
        $product = $productModel->loadByAttribute('sku', $prodNr);

        $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
        $lastUpdated = null;

        if ($stockItem->getLastUpdated() > 0) {
            $lastUpdated = Mage::getModel('core/date')->date('H:i', $stockItem->getLastUpdated());
        }
        $qty = (int) $stockItem->getQty();

        if ($product && $product->getTypeId()=='simple' && $product->getHasOptions() != 1) {
            $data = array(
                'nr' => $this->getRequest()->getParam('nr'),
                'product_id' => $product->getId(),
                'product_name' => $product->getName(),
                'product_sku' => $product->getSku(),
                'product_qty' => $quantity,
                'qty' => $qty, //intval(Mage::getModel('cataloginventory/stock_item')->loadByProduct($product)->getQty())
                'last_updated' => $lastUpdated,
                'class' => 'loaded',
            );

            $block = $this->getLayout()->createBlock('quickorders/element', 'element', $data)
                            ->setTemplate('quickorders/element.phtml');
            echo $block->toHtml();
        } else {
            $data = array(
                'nr' => $this->getRequest()->getParam('nr'),
                'product_name' => $this->__("Either this product does not excist or has custom options and therefore cannot be added to the cart."),
                'product_sku' => $prodNr,
                'class' => 'not_found',
                'qty' => $qty,
                'product_qty' => $quantity
            );

            $block = $this->getLayout()->createBlock('quickorders/element', 'element', $data)
                            ->setTemplate('quickorders/element.phtml');
            echo $block->toHtml();
        }
        die();
    }

    public function loadpartAction() {
        $storeId = Mage::app()->getStore()->getId();
        //$storeId = 0;
        $prodNr = $this->getRequest()->getParam('prod');
        $prodNr = str_replace("..", "/", $prodNr);
        //$products = Mage::getModel('catalog/product')->getCollection()->addAttributeToSelect('name')->addAttributeToSelect('sku');
        /*$products->getSelect()
                ->join(array('table1' => 'catalog_product_entity_varchar'), 'table1.entity_id=e.entity_id AND table1.attribute_id=\'96\'')
                ->where("(sku LIKE '%" . $prodNr . "%' OR table1.value LIKE '%" . $prodNr . "%') AND table1.store_id='" . $storeId . "'")
                ->order("char_length(sku),sku , char_length(table1.value),table1.value")
                ->limit(15);*/
        $product = Mage::getModel('catalog/product');
        $products = $product->getCollection()
                ->addAttributeToSelect('*')
                ->addAttributeToFilter('sku', $prodNr)
                ->load();
                
                
        /*$customer = Mage::getSingleton('customer/session')->getCustomer();
        if ($customer) {
            $customerGroupId = $customer->getGroupId();
            if ($customerGroupId) {
                $products->getSelect()
                    ->joinLeft(array('cpn' => 'customer_product_number'), 'e.entity_id=cpn.product_id and customer_group_id=' . $customerGroupId, array())
                    ->orWhere("cpn.customer_product_id LIKE '%" . $prodNr . "%'");
            }
        }*/

        if (count($products)) {
            $data = array();
            foreach ($products as $product) {
                $data[] = array(
                    'prod_data' => $product->getData(),
                    'sel' => $prodNr
                );
            }
            $_data = array('data' => $data);
            $block = $this->getLayout()->createBlock('quickorders/element', 'element', $_data)
                            ->setTemplate('quickorders/autosuggest.phtml');
            echo $block->toHtml();
        } else {
            echo "";
        }
        die();
    }

    public function addToCartAction() {
        $post = $this->getRequest()->getPost();
//		$quote = Mage::getModel('sales/quote')->setStoreId(Mage::app()->getStore()->getId());
        $quote = Mage::getSingleton('checkout/cart');
        //$customer = Mage::getSingleton('customer/session')->getCustomer();
        //$quote->setCustomer($customer);
        //$quote->setStore(Mage::app()->getStore());
        if (isset($post['product_id'])) {
            foreach ($post['product_id'] as $key => $product_id) {
                if ($product_id > 0) {
                    $product = Mage::getModel('catalog/product')->load($product_id);
                    if($product->getTypeId()=='simple' && $product->getHasOptions() != 1) {
                    	$quote->addProduct($product, (int) $post['qty'][$key]);
                    }
                }
            }
        }

        $quote->save();
//		if ($quote->getId())
//		{
//                        $checkout = Mage::getSingleton('checkout/session');
//                        $checkout->replaceQuote($quote);
//                }

        $this->_redirect('checkout/cart/');
    }

}
