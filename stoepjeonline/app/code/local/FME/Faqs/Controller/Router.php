<?php
/**
 * Faqs extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php

 * @category   FME
 * @package    Faqs
 * @author     Kamran Rafiq Malik <kamran.malik@unitedsol.net>
 * @copyright  Copyright 2010 © free-magentoextensions.com All right reserved
 */

class FME_Faqs_Controller_Router extends Mage_Core_Controller_Varien_Router_Abstract
{
    public function initControllerRouters($observer)
    {    		
        $front = $observer->getEvent()->getFront();
        $faqs = new FME_Faqs_Controller_Router();
        $front->addRouter('faqs', $faqs);
        
    }

    public function match(Zend_Controller_Request_Http $request)
    {
 
        if (!Mage::isInstalled()) {
            Mage::app()->getFrontController()->getResponse()
                ->setRedirect(Mage::getUrl('install'))
                ->sendResponse();
            exit;
        }
		

        $route = Mage::helper('faqs')->getListIdentifier();
		
       	$identifier = trim($request->getPathInfo(), '/');
        $identifier = str_replace(Mage::helper('faqs')->getSeoUrlSuffix(), '', $identifier);
                
               
        if ( $identifier == $route ) {

        	$request->setModuleName('faqs')
        			->setControllerName('index')
        			->setActionName('index');
        			
        	return true;
        			
        } elseif ( strpos($identifier, $route . '/search') === 0) {
			
        	$request->setModuleName('faqs')
        			->setControllerName('index')
        			->setActionName('search')
        			->setParam('id', (int)$request->getParam('id'));
        			
        	return true;
        			        
        } elseif ( strpos($identifier, $route) === 0 && strlen($identifier) > strlen($route)) {
			
        	$identifier = trim(substr($identifier, strlen($route)), '/');        
	       	$topicId = Mage::getModel('faqs/topic')->checkIdentifier($identifier, Mage::app()->getStore()->getId());
        	if ( !$topicId ) {
            	return false;
        	}
        	$request->setModuleName('faqs')
            		->setControllerName('index')
            		->setActionName('view')
            		->setParam('id', $topicId);
            		
			$request->setAlias(
					Mage_Core_Model_Url_Rewrite::REWRITE_REQUEST_PATH_ALIAS,
					$identifier
			);
			
			return true;

        }  
       
        return false;

    }
}