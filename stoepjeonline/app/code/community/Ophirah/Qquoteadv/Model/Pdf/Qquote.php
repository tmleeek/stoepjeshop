<?php

class Ophirah_Qquoteadv_Model_Pdf_Qquote extends Mage_Sales_Model_Order_Pdf_Abstract
{
    //highest point by vercical axis
    public $y = 750;	
	
	//lowest position by vercical axis
    public $_minPosY     = 15;
    
    public $_quoteadvId = null;
    public $_quoteadv   = null;   
    
    public $_leftRectPad     = 45;
    public $_leftTextPad     = 55;    
    
    //save product name to avoid dublicate display produc't names 
    public $_prevItemName       = '';
    public $_prevItemOptions   = array();
    
	public $_itemNamePosY   = 0;
    public $_itemId         = null;
    public $columns         = array();
    public $pdf;
    public $requestId;
    public $latestY = null;
    public $totalPrice = 0;
	
    
    protected function insertLogo(&$page, $store = null)
    {   
        $image = Mage::getStoreConfig('sales/identity/logo', $store); 
        if ($image) {
            $image = Mage::getStoreConfig('system/filesystem/media', $store) . '/sales/store/logo/' . $image; 
            try{
            //if (is_file($image)) {
                $image = Zend_Pdf_Image::imageWithPath($image);
                //$this->y-=40;
                $page->drawImage($image, $this->_leftRectPad, $this->y, 125, $this->y+25);
            //}
            }catch(Exception $e){
              
            }
        }
        //return $page;
    } 
    
    protected function insertAddress(&$page, $store = null)
    { 
    	$page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
        
        //$this->_setFontBold($page, 7);
        $this->_setFontRegular($page, 7);

        $this->y +=20;
        $nextY  = $this->y;
        
        foreach (explode("\n", Mage::getStoreConfig('sales/identity/address', $store)) as $value){
            
            if ($value!=='') {
                $page->drawText(trim(strip_tags($value)), 180, $this->y, 'UTF-8');
                $this->y -=7;
            }
        }
        
        if($this->y > $nextY) 
            $this->y = $nextY;            
   
    }
    
    public function addNewPage(){
        /* Add new table head */
        $page = $this->pdf->newPage(Zend_Pdf_Page::SIZE_A4);
        $this->pdf->pages[] = $page;
        $this->y = 800;

        $this->_setFontRegular($page);
        $page->setFillColor(new Zend_Pdf_Color_RGB(0.93, 0.92, 0.92));
        $page->setLineColor(new Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineWidth(0.5);
        $page->drawRectangle($this->_leftRectPad, $this->y, 570, $this->y-15);
        $this->y -=10;

        foreach($this->columns as $item){
            $textLabel 	=  $item[0];
            $xx			=  $item[1];
            $yy 		=  $item[2];
            $textEncod 	=  $item[3];                        
                        
            $page->drawText($textLabel, $xx, $yy, $textEncod);                
        }

        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
        $this->y -=20;
    }
    
    protected function checkLimitByY(){
                        
       if ($this->y < $this->_minPosY) {                    
          $this->addNewPage();
       }
    }
    
    public function getPdf($quotes = array())
    { 
        $this->_beforeGetPdf();        

        $this->pdf  = new Zend_Pdf();
        $style      = new Zend_Pdf_Style();
        $this->_setFontBold($style, 10);

 		if ($quotes instanceof Ophirah_Qquoteadv_Model_Qqadvcustomer) {
            	$this->_quoteadvId = $quotes->getId();
        }else{ 
		    foreach ($quotes as $item){
				$this->_quoteadvId = $item['quote_id'];
            }
		}
 			
 		$this->_quoteadv  = Mage::getModel('qquoteadv/qqadvcustomer')->load($this->_quoteadvId);
            
        if ($this->_quoteadv->getStoreId()) {
          Mage::app()->getLocale()->emulate($this->_quoteadv->getStoreId());
        }
            
        $page = $this->pdf->newPage(Zend_Pdf_Page::SIZE_A4);
        $this->pdf->pages[] = $page;

            /* Add image */
            $this->insertLogo($page, $this->_quoteadv->getStoreId());    
           
            /* Add address */
            $this->insertAddress($page, $this->_quoteadv->getStoreId());
            
            /* Add head */
            $this->insertTitles($page, $this->_quoteadvId, $this->_quoteadv->getStoreId());     
            
            $page->setFillColor(new Zend_Pdf_Color_GrayScale(1));
            $this->_setFontRegular($page);
            
            $this->y -=15;
            /* Add table */
            $page->setFillColor(new Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
            $page->setLineColor(new Zend_Pdf_Color_GrayScale(0.5));
            $page->setLineWidth(0.5);

            $page->drawRectangle($this->_leftRectPad, $this->y-10, 570, $this->y-25);
            $this->y -=20;

            /* Add table head */
            $page->setFillColor(new Zend_Pdf_Color_Rgb(0.4, 0.4, 0.4));
            
            $this->columns = array(
                    array(Mage::helper('qquoteadv')->__('Product name'),    $this->_leftTextPad, $this->y, 'UTF-8'),
                    array(Mage::helper('qquoteadv')->__('Atr. nr.'),        290, $this->y, 'UTF-8'),
                    array(Mage::helper('qquoteadv')->__('QTY'),             400, $this->y, 'UTF-8'),
                    array(Mage::helper('qquoteadv')->__('Price'),           480, $this->y, 'UTF-8'),
                    array(Mage::helper('qquoteadv')->__('Subtotal'),        535, $this->y, 'UTF-8')
            );            
            
            //#draw TABLE TITLES
            foreach($this->columns as $item){
                $textLabel =  $item[0];
                $textPosX =   $item[1];
                $textPosY =   $item[2];
                $textEncod =  $item[3];                
                
                $page->drawText($textLabel, $textPosX, $textPosY, $textEncod);                
            }            

            $this->y -=15; 
            $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));

            $requestItems = Mage::getModel('qquoteadv/qqadvproduct')
                            ->getCollection()
                            ->addFieldToFilter('quote_id', $this->_quoteadvId)
                        
           // ->load(true)
            ;                    	
         
            /* Add body */
            foreach ($requestItems as $product){
                /* Draw item */
                $this->draw($product, $page);
            }
            
            if(!is_null($this->latestY) && $this->latestY < $this->y){ 
                $this->y = $this->latestY - 5;
            }
                
            /* Add total */
    		$this->insertTotal($page);
            
            /* Add quote2cart general remark */
            $this->insertGeneralRemark($page);            
            
            if ($this->getStoreId()) {
                Mage::app()->getLocale()->revert();
            }

        $this->_afterGetPdf();

        return $this->pdf;
    }
    protected function _renderConfigurable($product, $item) {
      $html = array();
        
      $x = Mage::helper('qquoteadv')->getQuoteItem($product, $item->getAttribute());

      foreach($x->getAllItems() as $_zz) {
		if( $_zz->getProductId() == $product->getId() ) {
			$obj = new Ophirah_Qquoteadv_Block_Item_Renderer_Configurable;
            $obj->setTemplate('qquoteadv/item/configurable.phtml');
            $obj->setItem($_zz);
                   
            if ($_options = $obj->getOptionList()) { 
               foreach ($_options as $_option) { 
                 $_formatedOptionValue = $obj->getFormatedOptionValue($_option);
                 $html[] = $_option['label'];
                 $html[] = '  '.$_formatedOptionValue['value'];
               }
            }					
		}
      }
        
      return $html;    
    }
    
    protected function _renderBundle($product, $item) {
        $html = array();        
        $product->setStoreId($item->getStoreId()?$item->getStoreId():1);
		
        $virtualQuote = Mage::helper('qquoteadv')->getQuoteItem($product, $item->getAttribute());
		$_helper = Mage::helper('bundle/catalog_product_configuration');
		
		foreach($virtualQuote->getAllItems() as $_unit) {
			if( $_unit->getProductId() == $product->getId() ) {

				$_options = $_helper->getOptions($_unit);
				if( is_array($_options) ) {  
					
					 foreach ($_options as $_option): 

						//$_formatedOptionValue = $this->getFormatedOptionValue($_option);
                        $helperx = Mage::helper('catalog/product_configuration');
                        $params = array(
                            'max_length' => 55,
                            'cut_replacer' => ' <a href="#" class="dots" onclick="return false">...</a>'
                        );
                        $x = $helperx->getFormattedOptionValue($_option, $params);
						
                        $html[] = $_option['label'];
                        
                        $simple = explode("\n", $x['value']);
                        foreach($simple as $opt) {
                            $html[] = '  '.$opt;
                        }                        
						 
					endforeach;		
                   		  				
			 }
         }       
        }
        
        return $html;
    }
        
    public function draw($unit, $page) {
    
        $attr = array(); 
        $startLineY = $this->y;        
        $this->_setFontRegular($page);
        
        $productId      = $unit->getProductId();
        $itemRequest    = $unit->getClientRequest(); 
        
        $item       = Mage::getModel('catalog/product')->load($productId); 
        $name       = $item->getName();

        if ($item->getTypeId() == 'bundle') {
            $attr = $this->_renderBundle($item, $unit);
        } elseif ($item->getTypeId() == 'configurable') {
            $attr = $this->_renderConfigurable($item, $unit);
        } else {
            $superAttribute = $this->getOption($item, $unit->getAttribute());          
        
            //render custom options 
            $attr = $this->retrieveOptions($item, $superAttribute);              
        }
            
       //if ($this->requestId  != $unit->getRequestId() )  { 
            if($this->latestY>0 && $this->latestY < $this->y){ 
                $this->y = $this->latestY - 5;
                $startLineY = $this->y;
            }    
            
            $sku    = $this->getSku($item);	  
            if(strlen($sku)>40) { 
                $aSk = explode('-', $sku);
                foreach($aSk as $v) {
                    $page->drawText($v, 290, $this->y, 'UTF-8');
                    $this->y -=10;        
                }
               
            }else{
                $page->drawText($sku, 290, $this->y, 'UTF-8');
            }

            $this->y = $startLineY;
                        
            $this->requestId = $unit->getRequestId();      
            
            $this->_setFontBold($page,9); 
            
            /* in case Product name is longer than 80 chars - it is written in a few lines */
            $name = wordwrap($name, 80, "\n"); 
            foreach (explode("\n", $name) as $value){
            
                if ($value!=='') {                   
                    $page->drawText(trim(strip_tags($value)), $this->_leftTextPad, $this->y, 'UTF-8');
                    $this->y -=10;                    
                }                  
            }       
            
           	if (Mage::getStoreConfig('qquoteadv/attach/short_desc')) {
                $this->_setFontItalic($page, 6);
                $shortDesc = $item->getShortDescription();
                $shortDesc = str_replace("&nbsp;", ' ', $shortDesc);
                $shortDesc = preg_replace("/&#?[a-z0-9]{2,8};/i","",$shortDesc);
                $shortDesc = strip_tags($shortDesc);
                $shortDesc = wordwrap($shortDesc, 80, "\n"); 
                foreach (explode("\n", $shortDesc) as $value){

                    if ($value!=='') {                   
                        $page->drawText(trim($value), $this->_leftTextPad, $this->y, 'UTF-8');
                        $this->y -=7;                    
                    }                  
                }   
            }

            $this->_setFontItalic($page);
            //REQUESTED OPTIONS
            if (count($attr) > 0 ) {
              foreach ($attr as $value){            
                if ($value!=='') { 
                    $value = strip_tags($value);
                    $value = str_replace('&quot;', '"', $value);
                    $page->drawText($value, $this->_leftTextPad, $this->y, 'UTF-8');                    
                    $this->y -=10;                    
                }                  
              }
            }
            $this->y -=5;  
                      
            $this->_setFontItalic($page);
            $itemRequest = wordwrap($itemRequest, 80, "\n");
            foreach (explode("\n", $itemRequest) as $value){
            
                if ($value!=='') {                   
                    $page->drawText(trim(strip_tags($value)), $this->_leftTextPad, $this->y, 'UTF-8');
                    $this->y -=10;                    
                }                  
            }
            
           $this->latestY = $this->y;
            
           $this->_setFontRegular($page);
      // }     
 
        $requestedProductData = Mage::getModel('qquoteadv/requestitem')
                        ->getCollection()
						->addFieldToFilter('quote_id', $unit->getQuoteId())
                        ->addFieldToFilter('quoteadv_product_id', $unit->getId())        
                        ->addFieldToFilter('product_id', $productId)       
        ; 
        $requestedProductData->getSelect()->order(array('product_id ASC', 'request_qty ASC'));
        
        $this->y = $startLineY;
        foreach($requestedProductData as $product) {
            
            //set first line
            $productQty     = $product->getRequestQty()*1;
            $priceProposal  = $product->getOwnerBasePrice(); 

            $price 		= Mage::app()->getStore()->formatPrice($priceProposal, false);
            $row = $productQty*$priceProposal;
            $rowTotal 	= Mage::app()->getStore()->formatPrice($row, false); 

            $this->totalPrice+=$row; 

            $page->drawText($productQty, 	405, $this->y, 'UTF-8');
            $page->drawText($price, 		480, $this->y, 'UTF-8');
            $page->drawText($rowTotal,      542, $this->y, 'UTF-8'); 

            $this->y-=10;
        }
        
        if($this->latestY > $this->y) {
            $this->latestY = $this->y;
        }
    }
    
    protected function insertTitles(&$page, $source, $storeId)
    {
        $quoteadvId = $source;
        if(is_null($this->_quoteadv)){
          $this->_quoteadv = Mage::getModel('qquoteadv/qqadvcustomer')->load($quoteadvId);          
        }
        
        $this->y -=30;
        
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0.7));
        $page->setLineWidth(0.5);
        $page->drawRectangle($this->_leftRectPad, $this->y, 570, $this->y-45);

        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
        $this->_setFontRegular($page);
        
        $nextY = $this->y-10; 
        $page->drawText(Mage::helper('qquoteadv')->__('TO:'), $this->_leftTextPad, $nextY, 'UTF-8');

        $nextY  = $this->y-10;
        $this->_setFontBold($page);
        $page->drawText($this->_quoteadv->getCompany(), $this->_leftTextPad + 20, $nextY, 'UTF-8'); 
        
        $state 		= $this->_quoteadv->getRegion();
        $country 	= Mage::app()->getHelper('qquoteadv')->getCountryName($this->_quoteadv->getCountryId());
   
        $lastLine = $country;
        if( $state ) $lastLine = "$state, $lastLine";  
        
        $shipTo = array(            
            $this->_quoteadv->getAddress(),
            $this->_quoteadv->getCity() . ", ". $this->_quoteadv->getPostcode(),
        	$lastLine
        );
        
        $nextY  = $this->y-20;
        
        $this->_setFontRegular($page);
        foreach($shipTo as $value){
            if ($value!=='') {
                  $page->drawText($value, $this->_leftTextPad + 20, $nextY, 'UTF-8'); 
                  $nextY -=10;
            }        
        }       
        
        $x = 400;
        $xLeng = $x + 80;
        $this->y -=10; 
        $page->drawText(Mage::helper('qquoteadv')->__('Quote Proposal'), $x, $this->y, 'UTF-8');              

        
        $realQuoteadvId = $this->_quoteadv->getIncrementId()?$this->_quoteadv->getIncrementId():$this->_quoteadv->getId();
		$page->drawText($realQuoteadvId, $xLeng, $this->y, 'UTF-8');    
        
		$this->y -=10;
        $page->drawText(Mage::helper('qquoteadv')->__('Date of Proposal'), $x, $this->y, 'UTF-8');
        $page->drawText(Mage::helper('core')->formatDate( date( 'D M j Y'), 'medium', false), $xLeng, $this->y, 'UTF-8');               
             
        
        $expDays 	= Mage::getStoreConfig('qquoteadv/general/expirtime_proposal');
        
        $this->y -=10; 
        
        if( (int)$expDays) {
	               
	        $page->drawText(Mage::helper('qquoteadv')->__('Proposal valid until'), $x, $this->y, 'UTF-8');

	        $dat = mktime(0, 0, 0, date("m"), date('d') + $expDays, date("Y"));
	        $note = "( ".Mage::helper('qquoteadv')->__("%s days", $expDays)." )";
	        $page->drawText(Mage::helper('core')->formatDate( date( 'D M j Y', $dat), 'medium', false). '    ' . $note , $xLeng, $this->y, 'UTF-8'); 
        }
        
        $this->_setFontRegular($page);
        
    }   
    
    /**
     * Total information by quoteadv
     *
     * @param  $page
     */
    protected function insertTotal(&$page){
        $this->y -=20;
    	$currentY = $this->y;
        $excl = Mage::helper('qquoteadv')->__('(excl. TAX)');
        
        $this->_setFontBold($page,9); 
        $sLabel     = Mage::helper('qquoteadv')->__('Total price')." ".$excl;
        $totalPrice = Mage::app()->getStore()->formatPrice($this->totalPrice, false); 
        $page->drawText($sLabel, 430, $this->y, 'UTF-8');
        $page->drawText(strip_tags($totalPrice), 530, $this->y, 'UTF-8');
        
        $this->y -= 10;
        
        $this->_setFontRegular($page);    	
        //$this->_setFontItalic($page);
        $price        =  $this->_quoteadv->getShippingPrice();
        $shippingType =  $this->_quoteadv->getShippingType();
		
        if($shippingType=='I'){
        	    $sLabel = Mage::helper('qquoteadv')->__('Shipping & Handling price per Item')." ".$excl;
                $sPrice = Mage::helper('checkout')->formatPrice($price);  
                
                $page->drawText($sLabel, 390, $this->y, 'UTF-8');
        	    $page->drawText(strip_tags($sPrice), 545, $this->y, 'UTF-8');
                              
        }elseif($shippingType=='O'){
        	    $sLabel = Mage::helper('qquoteadv')->__('Fixed Shipping & Handling price'." ".$excl);
                $sPrice = Mage::helper('checkout')->formatPrice($price);
        	   
                $page->drawText($sLabel, 390, $this->y, 'UTF-8');
        	    $page->drawText(strip_tags($sPrice), 545, $this->y, 'UTF-8');
             
        }else{
        	    $sLabel    = Mage::helper('qquoteadv')->__("Shipping & Handling price varies");
        		$sLabelExt = Mage::helper('qquoteadv')->__("select required qty and check out online");
        	   
        		$page->drawText($sLabel, 430, $this->y, 'UTF-8');  
        		
        		$this->y -=10;    	   
        	    $page->drawText(Mage::helper('qquoteadv')->__("select required qty and check out online"), 430, $this->y, 'UTF-8');
        	    
        	    $this->y -=10;    	   
        	    $page->drawText(Mage::helper('qquoteadv')->__("to see applicable price**"), 430, $this->y, 'UTF-8');       	   
        	                
        }     
        	
        $this->y = $currentY;
        	
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0.9));
        $page->setLineWidth(0.5);
        $lowPoint = $this->y-55;
        $page->drawRectangle($this->_leftRectPad, $this->y, $this->_leftRectPad+215, $lowPoint);
    
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
        $this->_setFontRegular($page);
                        
        $this->y -=10;
        $remark = wordwrap($this->_quoteadv->getClientRequest(), 60, "\n");            
            
        $tmpY 		= $this->y; 
        $this->y 	= $lowPoint;
        
        foreach (explode("\n", $remark) as $value){
            
          if ($value!=='') {
             $page->drawText(trim(strip_tags($value)), $this->_leftTextPad, $tmpY, 'UTF-8');
             $tmpY -=7;
          }
        }
        
        if( $this->y > $tmpY )
        	$this->y = $tmpY;
    }
    
    /**
     * Quote reneral remark
     *
     * @param  $page
     */
    protected function insertGeneralRemark(&$page){
        
        $this->y -=10;
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(1));
        $page->setLineWidth(0.5);
        $page->drawRectangle($this->_leftRectPad, $this->y, 570, $this->y-25);
    
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
        $this->_setFontRegular($page);
        
        $this->y -=10;
            
        $qquoteadvRemark = wordwrap(Mage::getStoreConfig('qquoteadv/general/qquoteadv_remark'), 165, "\n"); 

        foreach (explode("\n", $qquoteadvRemark) as $value){
            
          if ($value!=='') {                   
               $page->drawText(trim(strip_tags($value)), $this->_leftTextPad, $this->y, 'UTF-8');
               $this->y -=7;
          }
        }           
        
    }
    
    public function getSku($item)
    {
        if ($item->getProductOptionByCode('simple_sku'))
            return $item->getProductOptionByCode('simple_sku');
        else
            return $item->getSku();
    }
    
    
    /**
	 * Get attribute options array 
	 * @param object $product 
	 * @param string $attribute
	 * @return array 
	 */
	public function getOption($product, $attribute)
	{
		$superAttribute = array(); 
		if($product->getTypeId() == 'simple') {
			$superAttribute = Mage::helper('qquoteadv')->getSimpleOptions($product, unserialize($attribute)); 	
		}		
		return $superAttribute;
	}
    
    protected function retrieveOptions($product, $superAttribute) {
        $attr  = array();

        if ($product->getTypeId() == 'simple') {
            if ($superAttribute) {
                foreach($superAttribute as $option => $value) {
                    $attr[]= $option;  
                    $attr[]= '   '.$value;
                }
            }
        }
        
        return $attr;
    }
    
}