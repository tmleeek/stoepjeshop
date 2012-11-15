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

class FME_Faqs_Helper_Data extends Mage_Core_Helper_Abstract
{

	const XML_PATH_LIST_PAGE_TITLE				=	'faqs/list/page_title';
	const XML_PATH_LIST_IDENTIFIER				=	'faqs/list/identifier';
	const XML_PATH_LIST_META_DESCRIPTION		=	'faqs/list/meta_description';
	const XML_PATH_LIST_META_KEYWORDS			=	'faqs/list/meta_keywords';
	
	const XML_PATH_DETAIL_TITLE_PREFIX				=	'faqs/detail/title_prefix';
	const XML_PATH_DETAIL_DEFAULT_META_DESCRIPTION	=	'faqs/detail/default_meta_description';
	const XML_PATH_DETAIL_DEFAULT_META_KEYWORDS		=	'faqs/detail/default_meta_keywords';
	const XML_PATH_SEO_URL_SUFFIX					=	'faqs/seo/url_suffix';

	
	public function getListPageTitle()
	{
		return Mage::getStoreConfig(self::XML_PATH_LIST_PAGE_TITLE);
	}
	
	public function getListIdentifier()
	{
		$identifier = Mage::getStoreConfig(self::XML_PATH_LIST_IDENTIFIER);
		if ( !$identifier ) {
			$identifier = 'faqs';
		}
		return $identifier;
	}
	
	public function getUrl($identifier = null)
	{
		
		if (is_null($identifier)) {
			$url = Mage::getUrl('') . self::getListIdentifier() . self::getSeoUrlSuffix();
		} else {
			$url = Mage::getUrl(self::getListIdentifier()) . $identifier . self::getSeoUrlSuffix();
		}
		return $url;
		
	}
		
	public function getListMetaDescription()
	{
		return Mage::getStoreConfig(self::XML_PATH_LIST_META_DESCRIPTION);
	}
	
	public function getListMetaKeywords()
	{
		return Mage::getStoreConfig(self::XML_PATH_LIST_META_KEYWORDS);
	}
	
	public function getSeoUrlSuffix()
	{
		return Mage::getStoreConfig(self::XML_PATH_SEO_URL_SUFFIX);
	}
	
	public function getDetailDefaultMetaDescription()
	{
		return Mage::getStoreConfig(self::XML_PATH_DETAIL_DEFAULT_META_DESCRIPTION);
	}
	
	public function getDetailDefaultMetaKeywords()
	{
		return Mage::getStoreConfig(self::XML_PATH_DETAIL_DEFAULT_META_KEYWORDS);
	}
	
	public function getDetailTitlePrefix()
	{
		return Mage::getStoreConfig(self::XML_PATH_DETAIL_TITLE_PREFIX);
	}
	
	public function recursiveReplace($search, $replace, $subject)
    {
        if(!is_array($subject))
            return $subject;

        foreach($subject as $key => $value)
            if(is_string($value))
                $subject[$key] = str_replace($search, $replace, $value);
            elseif(is_array($value))
                $subject[$key] = self::recursiveReplace($search, $replace, $value);

        return $subject;
    }

    public function extensionEnabled($extension_name)
	{
		$modules = (array)Mage::getConfig()->getNode('modules')->children();
		if (!isset($modules[$extension_name])
			|| $modules[$extension_name]->descend('active')->asArray()=='false'
			|| Mage::getStoreConfig('advanced/modules_disable_output/'.$extension_name)
		) return false;
		return true;
	}
	
	public function strip_only($str, $tags, $stripContent = false) {
		$content = '';
		if(!is_array($tags)) {
			$tags = (strpos($str, '>') !== false ? explode('>', str_replace('<', '', $tags)) : array($tags));
			if(end($tags) == '') array_pop($tags);
		}
		foreach($tags as $tag) {
			if ($stripContent)
				 $content = '(.+</'.$tag.'[^>]*>|)';
			 $str = preg_replace('#</?'.$tag.'[^>]*>'.$content.'#is', '', $str);
		}
		return $str;
	} 

}