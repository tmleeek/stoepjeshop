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
 * @package    MageWorx_InstantCart
 * @copyright  Copyright (c) 2010 MageWorx (http://www.mageworx.com/)
 * @license    http://www.mageworx.com/LICENSE-1.0.html
 */

/**
 * Instant Cart extension
 *
 * @category   MageWorx
 * @package    MageWorx_InstantCart
 * @author     MageWorx Dev Team <dev@mageworx.com>
 */

require_once('Mage/Checkout/controllers/CartController.php');
class MageWorx_InstantCart_IndexController extends Mage_Checkout_CartController
{
    public function preDispatch() {
        $checkCookie = in_array($this->getRequest()->getActionName(), $this->_cookieCheckActions);
        $checkCookie = $checkCookie && !$this->getRequest()->getParam('nocookie', false);
        $cookies = Mage::getSingleton('core/cookie')->get();
        if ($checkCookie && empty($cookies)) {
            Mage::getSingleton('core/session', array('name' => $this->_sessionNamespace))->start();
            $this->getResponse()->setRedirect($this->getRequest()->getRequestUri().'?cookies')->sendResponse();
            exit;
        }

        parent::preDispatch();

        /*if (!$this->getRequest()->isXmlHttpRequest()) {
            $cartUrl = str_replace('/icart/', '/cart/', $this->getRequest()->getRequestUri());
            $this->getResponse()->setRedirect($cartUrl)->sendResponse();
            exit;
        }*/
    }

    public function addAction()
    {
        $cart   = $this->_getCart();
        $params = $this->getRequest()->getParams();        
        try {
            /*if (isset($params['qty'])) {
                $filter = new Zend_Filter_LocalizedToNormalized(
                    array('locale' => Mage::app()->getLocale()->getLocaleCode())
                );
                $params['qty'] = $filter->filter($params['qty']);
            }*/
            $product = $this->_initProduct();
            Mage::register('product', $product);
            if ('POST' != $this->getRequest()->getMethod()){
                Mage::register('current_product', $product);
                //if ($product->isGrouped() || $product->getTypeInstance(true)->hasRequiredOptions($product) || (Mage::helper('icart')->isExtensionEnabled('MageWorx_CustomPrice') && Mage::helper('customprice')->isCustomPriceAllowed($product))){
				//if ((Mage::helper('icart')->isExtensionEnabled('MageWorx_CustomPrice') && Mage::helper('customprice')->isCustomPriceAllowed($product))){
                    $update = $this->getLayout()->getUpdate();
                    $this->addActionLayoutHandles();

                    $update->addHandle('PRODUCT_TYPE_'.$product->getTypeId());
                    $update->addHandle('PRODUCT_'.$product->getId());

                    if ($product->getPageLayout()) {
                        $this->getLayout()->helper('page/layout')
                            ->applyHandle($product->getPageLayout());
                    }

                    $this->loadLayoutUpdates();
                    $update->addUpdate($product->getCustomLayoutUpdate());

                    $this->generateLayoutXml()->generateLayoutBlocks();

                    if ($product->getPageLayout()) {
                        $this->getLayout()->helper('page/layout')
                            ->applyTemplate($product->getPageLayout());
                    }
                    //echo $this->getLayout()->getOutput();
                    $this->renderLayout();
                    return;
                //}
            }
            $related = $this->getRequest()->getParam('related_product');

            /**
             * Check product availability
             */
            if (!$product) {
                $this->_getSession()->addError($this->__('Product is not available.'));
                $this->_forward('added');
            }

            Mage::dispatchEvent('checkout_icart_add_before', array('controller_action' => $this));
            $cart->addProduct($product, $params);
            if (!empty($related)) {
                $cart->addProductsByIds(explode(',', $related));
            }

            $cart->save();

            $this->_getSession()->setCartWasUpdated(true);

            /**
             * @todo remove wishlist observer processAddToCart
             */
            Mage::dispatchEvent('checkout_cart_add_product_complete',
                array('product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse())
            );

            if (!$this->_getSession()->getNoCartRedirect(true)) {
                if (!$cart->getQuote()->getHasError()){
                    $message = $this->__('%s was added to your shopping cart.', Mage::helper('core')->htmlEscape($product->getName()));
                    $this->_getSession()->addSuccess($message);
                }
            }
        }
        catch (Mage_Core_Exception $e) {
            if ($this->_getSession()->getUseNotice(true)) {
                $this->_getSession()->addNotice($e->getMessage());
            } else {
                $messages = array_unique(explode("\n", $e->getMessage()));
                foreach ($messages as $message) {
                    $this->_getSession()->addError($message);
                }
            }
        }
        catch (Exception $e) {
            $this->_getSession()->addException($e, $this->__('Cannot add the item to shopping cart.'));
        }
        $this->_forward('added');
    }
    
    
    
    protected function _getWishlist()
    {
        try {
            $wishlist = Mage::getModel('wishlist/wishlist')
                ->loadByCustomer(Mage::getSingleton('customer/session')->getCustomer(), true);
            Mage::register('wishlist', $wishlist);
        } catch (Mage_Core_Exception $e) {
            Mage::getSingleton('wishlist/session')->addError($e->getMessage());
        } catch (Exception $e) {
            Mage::getSingleton('wishlist/session')->addException($e,
                Mage::helper('wishlist')->__('Cannot create wishlist.')
            );
            return false;
        }
        return $wishlist;
    }
    
    public function addToWishlistAction()
    {                       
        
        if (!Mage::getSingleton('customer/session')->isLoggedIn()) {                
            $this->getLayout()->getUpdate()->addHandle('icart_index_authorization');
            $this->loadLayoutUpdates()->_initLayoutMessages('checkout/session');
            $this->generateLayoutXml()->generateLayoutBlocks();
            $this->renderLayout();                 
            $this->setFlag('', 'no-dispatch', true);
            
            Mage::getSingleton('customer/session')->setBeforeAuthUrl($this->_getRefererUrl());            
            return false;
        }
        
        $session = $this->_getSession();
        
        $productId = intval($this->getRequest()->getParam('product', false));
        $product = Mage::getModel('catalog/product')->load($productId);
        if (!$product->getId() || !$product->isVisibleInCatalog()) {
            $session->addError($this->__('Cannot specify product.'));
            $this->_forward('added');
            return false;
        }        
        
        
        try {
            $wishlist = $this->_getWishlist();        
            $buyRequest = new Varien_Object($this->getRequest()->getParams());

            $result = $wishlist->addNewItem($productId, $buyRequest);
            if (is_string($result)) {
                Mage::throwException($result);
            }
            $wishlist->save();            
            
            $session->addSuccess($this->__('%1$s was successfully added to your wishlist.', Mage::helper('core')->htmlEscape($product->getName())));            

        } catch (Mage_Core_Exception $e) {
            if ($session->getUseNotice(true)) {
                $session->addNotice($e->getMessage());
            } else {
                $messages = array_unique(explode("\n", $e->getMessage()));
                foreach ($messages as $message) {
                    $session->addError($message);
                }
            }
        } catch (Exception $e) {
            $session->addException($e, $this->__('Cannot add the item to wishlist.'));
        }
        
        $this->_forward('added');
    }
    
    
    public function addToCompareAction()
    {                                       
        
        $session = $this->_getSession();
        
        $productId = intval($this->getRequest()->getParam('product', false));
        $product = Mage::getModel('catalog/product')->load($productId);
        if (!$product->getId() || !$product->isVisibleInCatalog()) {
            $session->addError($this->__('Cannot specify product.'));
            $this->_forward('added');
            return false;
        }        
        
        try {            
            Mage::getSingleton('catalog/product_compare_list')->addProduct($product);                                    
            $session->addSuccess($this->__('The product %s has been added to comparison list.', Mage::helper('core')->htmlEscape($product->getName())));
            Mage::helper('catalog/product_compare')->calculate();
        } catch (Mage_Core_Exception $e) {
            if ($session->getUseNotice(true)) {
                $session->addNotice($e->getMessage());
            } else {
                $messages = array_unique(explode("\n", $e->getMessage()));
                foreach ($messages as $message) {
                    $session->addError($message);
                }
            }
        } catch (Exception $e) {
            $session->addException($e, $this->__('Cannot add the item to comparison list.'));
        }
        
        $this->_forward('added');
    }
    
    

    public function addedAction()
    {      
        $isInputFile = intval($this->getRequest()->getParam('is_input_file', 0));        
        if ($isInputFile) {
            $this->getResponse()->setBody('<script type="text/javascript">window.location="'.$this->getRequest()->getParam('referer_url', '').'"</script>');            
            return ;
        }
        
        $this->getLayout()->getUpdate()->addHandle('checkout_icart_added');
        $this->loadLayoutUpdates()->_initLayoutMessages('checkout/session');
        $this->generateLayoutXml()->generateLayoutBlocks();
        $this->renderLayout();
    }

    public function deleteAction()
    {
        $id = (int) $this->getRequest()->getParam('id');
        if ($id) {
            try {
                $this->_getCart()->removeItem($id)
                  ->save();
            } catch (Exception $e) {
                echo $this->__('Cannot remove the item.');
                exit;
            }
        }
        $this->getLayout()->getUpdate()->addHandle('checkout_icart_delete');
        $this->loadLayoutUpdates();
        $this->generateLayoutXml()->generateLayoutBlocks();
        $this->renderLayout();
    }
    
    public function removeWishlistAction()
    {
        $wishlist = $this->_getWishlist();
        $itemId = (int) $this->getRequest()->getParam('item');
        
        $item = Mage::getModel('wishlist/item')->load($itemId);

        if($item->getWishlistId()==$wishlist->getId()) {
            try {
                $item->delete();
                $wishlist->save();
            } catch (Exception $e) {
                echo $this->__('Cannot remove the item.');
                exit;
            }
        }

        Mage::helper('wishlist')->calculate();
                       
        $this->getLayout()->getUpdate()->addHandle('checkout_icart_delete');
        $this->loadLayoutUpdates();
        $this->generateLayoutXml()->generateLayoutBlocks();
        $this->renderLayout();
    }
    
    public function removeCompareAction()
    {                        
        
        if ($productId = (int) $this->getRequest()->getParam('product')) {
            $product = Mage::getModel('catalog/product')
                ->setStoreId(Mage::app()->getStore()->getId())
                ->load($productId);

            if($product->getId()) {
                $item = Mage::getModel('catalog/product_compare_item');
                if(Mage::getSingleton('customer/session')->isLoggedIn()) {
                    $item->addCustomerData(Mage::getSingleton('customer/session')->getCustomer());
                } else {
                    $item->addVisitorId(Mage::getSingleton('log/visitor')->getId());
                }

                $item->loadByProduct($product);

                if($item->getId()) {
                    $item->delete();                    
                    Mage::dispatchEvent('catalog_product_compare_remove_product', array('product'=>$item));
                    Mage::helper('catalog/product_compare')->calculate();
                }
            }
        }                                
                       
        $this->getLayout()->getUpdate()->addHandle('checkout_icart_delete');
        $this->loadLayoutUpdates();
        $this->generateLayoutXml()->generateLayoutBlocks();
        $this->renderLayout();
    }
    
    
    public function clearCompareAction()
    {                                                
        
        $items = Mage::getResourceModel('catalog/product_compare_item_collection')
            //->useProductItem(true)
            //->setStoreId(Mage::app()->getStore()->getId())
            ;

        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            $items->setCustomerId(Mage::getSingleton('customer/session')->getCustomerId());
        }
        else {
            $items->setVisitorId(Mage::getSingleton('log/visitor')->getId());
        }

        $session = Mage::getSingleton('catalog/session');
        /* @var $session Mage_Catalog_Model_Session */

        try {
            $items->clear();            
            Mage::helper('catalog/product_compare')->calculate();
        } catch (Exception $e) {
            echo $this->__('Cannot remove the item.');
            exit;
        }
        
                       
        $this->getLayout()->getUpdate()->addHandle('checkout_icart_delete');
        $this->loadLayoutUpdates();
        $this->generateLayoutXml()->generateLayoutBlocks();
        $this->renderLayout();
    }
    

}
