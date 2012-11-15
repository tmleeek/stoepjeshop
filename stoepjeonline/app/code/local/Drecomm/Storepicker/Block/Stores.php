<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/LICENSE-L.txt
 *
 * @category   AW
 * @package    AW_Blog
 * @copyright  Copyright (c) 2009-2010 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/LICENSE-L.txt
 */

class Drecomm_Storepicker_Block_Stores extends Mage_Core_Block_Template
{
    public function getStores()
   	{
        $collection = Mage::getModel('storepicker/store')->getCollection()
        //->addPresentFilter()
		//->addStoreFilter(Mage::app()->getStore()->getId())
		->setOrder('id', 'desc');

		return $collection;

		$collection->setPageSize((int)Mage::getStoreConfig(AW_Blog_Helper_Config::XML_BLOG_PERPAGE));
        $collection->setCurPage($page);
		
		
		
		$route = Mage::helper('storepicker')->getRoute();
		
		foreach ($collection as $item) 
		{
			$item->setAddress($this->getUrl($route . "/" . $item->getIdentifier()));
			
			$item->setCreatedTime($this->formatTime($item->getCreatedTime(),Mage::getStoreConfig('storepicker/stores/dateformat'), true));
			$item->setUpdateTime($this->formatTime($item->getUpdateTime(),Mage::getStoreConfig('storepicker/stores/dateformat'), true));
			
			if(Mage::getStoreConfig(Drecomm_Storepicker_Helper_Config::XML_BLOG_USESHORTCONTENT) && strip_tags(trim($item->getShortContent()))){
				$content = trim($item->getShortContent());
				$content = $this->closetags($content);
				$content .= ' <a href="' . $this->getUrl($route . "/" . $item->getIdentifier()) . '" >'.$this->__('Read More').'</a>';
				$item->setPostContent($content);
			}elseif ((int)Mage::getStoreConfig(Drecomm_Storepicker_Helper_Config::XML_STOREPICKER_READMORE) != 0){
				$content = $item->getPostContent();
				if(strlen($content) >= (int)Mage::getStoreConfig(Drecomm_Storepicker_Helper_Config::XML_STOREPICKER_READMORE))
				{
					$content = substr($content, 0, (int)Mage::getStoreConfig(Drecomm_Storepicker_Helper_Config::XML_STOREPICKER_READMORE));
					$content = substr($content, 0, strrpos($content, ' '));
					$content = $this->closetags($content);
					$content .= ' <a href="' . $this->getUrl($route . "/" . $item->getIdentifier()) . '" >'.$this->__('Read More').'</a>';
				}
				$item->setPostContent($content);
			}
			
			
			$comments = Mage::getModel('storepicker/comment')->getCollection()
			->addPostFilter($item->getPostId())
			->addApproveFilter(2);
			$item->setCommentCount(count($comments));
			
			$cats = Mage::getModel('storepicker/cat')->getCollection()
			->addPostFilter($item->getPostId());
			$catUrls = array();
			foreach($cats as $cat)
			{
				$catUrls[$cat->getTitle()] = Mage::getUrl($route . "/cat/" . $cat->getIdentifier());
			}
			$item->setCats($catUrls);
		}
		return $collection;
    }
	
	public function getBookmarkHtml($post){
		if (Mage::getStoreConfig('storepicker/stores/bookmarkslist'))
		{
			$this->setTemplate('drecomm_storepicker/bookmark.phtml');
			$this->setPost($post);
			return $this->toHtml();
		}
		return;
	}
	public function getTagsHtml($post){
		if (trim($post->getTags())){
			$this->setTemplate('drecomm_storepicker/line_tags.phtml');
			$this->setPost($post);
			return $this->toHtml();
		}
		return;
	}	
	

	public function getCommentsEnabled(){
		return Mage::getStoreConfig('storepicker/comments/enabled');
	}
	
	public function getPages()
	{
		if ((int)Mage::getStoreConfig('storepicker/stores/perpage') != 0)
		{
			$collection = Mage::getModel('storepicker/stores')->getCollection()
			->setOrder('created_time ', 'desc');
			
			Mage::getSingleton('storepicker/status')->addEnabledFilterToCollection($collection);
			
			$currentPage = (int)$this->getRequest()->getParam('page');
	
			if(!$currentPage){
				$currentPage = 1;
			}
			
			$pages = ceil(count($collection) / (int)Mage::getStoreConfig('storepicker/stores/perpage'));
			
			$links = "";
			
			$route = Mage::helper('blog')->getRoute();
			
			if ($currentPage > 1)
			{
				$links = $links . '<div class="left"><a href="' . $this->getUrl($route. '/page/' . ($currentPage - 1)) . '" >&lt; '.$this->__('Newer Posts').'</a></div>';
			}
			if ($currentPage < $pages)
			{
				$links = $links .  '<div class="right"><a href="' . $this->getUrl($route .'/page/' . ($currentPage + 1)) . '" >'.$this->__('Older Posts').' &gt;</a></div>';
			}
			echo $links;
		}
	}
	
	public function getRecent()
   	{
		if (Mage::getStoreConfig(AW_Blog_Helper_Config::XML_RECENT_SIZE) != 0)
		{
			$collection = Mage::getModel('storepicker/store')->getCollection()
			->addPresentFilter()
			->addStoreFilter(Mage::app()->getStore()->getId())
			->setOrder('created_time ', 'desc');
			
			$route = Mage::helper('storepicker')->getRoute();
			
			Mage::getSingleton('storepicker/status')->addEnabledFilterToCollection($collection);
			$collection->setPageSize(Mage::getStoreConfig(Drecomm_Storepicker_Helper_Config::XML_RECENT_SIZE));
			$collection->setCurPage(1);
			foreach ($collection as $item) 
			{
				$item->setAddress($this->getUrl($route . "/" . $item->getIdentifier()));
			}
			return $collection;
		}
		else
		{
			return false;
		}
    }
	
	public function getCategories()
   	{
        $collection = Mage::getModel('storepicker/cat')->getCollection()
		->addStoreFilter(Mage::app()->getStore()->getId())
		->setOrder('sort_order ', 'asc');
		
		$route = Mage::helper('storepicker')->getRoute();
		
		foreach ($collection as $item) 
		{
			$item->setAddress($this->getUrl($route . "/cat/" . $item->getIdentifier()));
		}
		return $collection;
    }
	
	public function addTopLink()
    {
		$route = Mage::helper('storepicker')->getRoute();
		$title = Mage::getStoreConfig('storepicker/stores/title');
        $this->getParentBlock()->addLink($title, $route, $title, true, array(), 15, null, 'class="top-link-blog"');
    }
	public function addFooterLink()
    {
		$route = Mage::helper('storepicker')->getRoute();
		$title = Mage::getStoreConfig('storepicker/stores/title');
        $this->getParentBlock()->addLink($title, $route, $title, true);
    }
	
	public function closetags($html){
		return Mage::helper('storepicker/store')->closetags($html);
	}
	
	protected function _prepareLayout()
    {
        
		
		$route = Mage::helper('blog')->getRoute(); 
		$isBlogPage = Mage::app()->getFrontController()->getAction()->getRequest()->getModuleName() == 'blog';
		
		// show breadcrumbs
		if ($isBlogPage && Mage::getStoreConfig('blog/blog/blogcrumbs') && ($breadcrumbs = $this->getLayout()->getBlock('breadcrumbs'))){
				$breadcrumbs->addCrumb('home', array('label'=>Mage::helper('blog')->__('Home'), 'title'=>Mage::helper('blog')->__('Go to Home Page'), 'link'=>Mage::getBaseUrl()));;
			if($tag = @urldecode($this->getRequest()->getParam('tag'))){
				$breadcrumbs->addCrumb('blog', array('label'=>Mage::getStoreConfig('blog/blog/title'), 'title'=>Mage::helper('blog')->__('Return to ' .Mage::getStoreConfig('blog/blog/title')), 'link'=>Mage::getUrl($route)));
				$breadcrumbs->addCrumb('blog_tag', array('label'=>Mage::helper('blog')->__('Tagged with "%s"', $tag), 'title'=> Mage::helper('blog')->__('Tagged with "%s"', $tag) ));
			}else{
				$breadcrumbs->addCrumb('blog', array('label'=>Mage::getStoreConfig('blog/blog/title'), 'title'=>Mage::helper('blog')->__('Return to ' .Mage::getStoreConfig('blog/blog/title')), 'link'=>Mage::getUrl($route)));
			}	
		}
		
	}
	
	
	public function _toHtml(){
		$isLeft = ($this->getParentBlock() === $this->getLayout()->getBlock('left'));
		$isRight = ($this->getParentBlock() === $this->getLayout()->getBlock('right'));
		
		$isBlogPage = Mage::app()->getFrontController()->getAction()->getRequest()->getModuleName() == 'blog';
		
		$leftAllowed = ($isBlogPage && Mage::getStoreConfig('blog/menu/left') == 2) || (Mage::getStoreConfig('blog/menu/left') == 1);
		$rightAllowed = ($isBlogPage && Mage::getStoreConfig('blog/menu/right') == 2) || (Mage::getStoreConfig('blog/menu/right') == 1);
		
		if(!$leftAllowed && $isLeft){
			return '';
		}
		if(!$rightAllowed && $isRight){
			return '';
		}		
		return parent::_toHtml();
	}
}
