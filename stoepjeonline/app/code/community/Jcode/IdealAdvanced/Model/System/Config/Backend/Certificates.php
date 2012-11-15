<?php
/**
* J!Code WebDevelopment
*
* @title 		Magento payment module for iDeal Basic
* @category 	J!Code
* @package 		Jcode_Community
* @author 		Jeroen Bleijenberg / J!Code WebDevelopment <support@jcode.nl>
* @license  	http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*/
class Jcode_IdealAdvanced_Model_System_Config_Backend_Certificates
	extends Mage_Core_Model_Config_Data
	{
	    protected function _beforeSave()
	    {
	        $value = $this->getValue();
	        if (is_array($value) && !empty($value['delete'])) {
	            $this->setValue('');
	        }
	
	        if ($_FILES['groups']['tmp_name'][$this->getGroupId()]['fields'][$this->getField()]['value']){
	
	            $fieldConfig = $this->getFieldConfig();

	
	            if (empty($fieldConfig->upload_dir)) {
	                Mage::throwException(Mage::helper('catalog')->__('Base directory to upload image file is not specified'));
	            }
	
	            $uploadDir =  (string)$fieldConfig->upload_dir;
	
	            $el = $fieldConfig->descend('upload_dir');

	            if (!empty($el['scope_info'])) {
	                $uploadDir = $this->_appendScopeInfo($uploadDir);
	            }

	            if (!empty($el['config'])) {
	                $uploadRoot = (string)Mage::getConfig()->getNode((string)$el['config'], $this->getScope(), $this->getScopeId());
	                $uploadRoot = Mage::getConfig()->substDistroServerVars($uploadRoot);
	                $uploadDir = $uploadRoot . '/' . $uploadDir;
	            }
	
	            try {
	                $file = array();
	                $file['tmp_name'] = $_FILES['groups']['tmp_name'][$this->getGroupId()]['fields'][$this->getField()]['value'];
	                $file['name'] = $_FILES['groups']['name'][$this->getGroupId()]['fields'][$this->getField()]['value'];
	                $uploader = new Varien_File_Uploader($file);
	                $uploader->setAllowedExtensions($this->_getAllowedExtensions());
	                $uploader->setAllowRenameFiles(true);
	                $uploader->save($uploadDir);
	            } catch (Exception $e) {
	                Mage::throwException($e->getMessage());
	                return $this;
	            }
	
	            if ($filename = $uploader->getUploadedFileName()) {
	

	                if (!empty($el['scope_info'])) {
	                    $filename = $this->_prependScopeInfo($filename);
	                }
	
	                $this->setValue($filename);
	            }
	        }
	
	        return $this;
	    }
	
	    protected function _prependScopeInfo($path)
	    {
	        $scopeInfo = $this->getScope();
	        if ('default' != $this->getScope()) {
	            $scopeInfo .= '/' . $this->getScopeId();
	        }
	        return $scopeInfo . '/' . $path;
	    }

	    protected function _appendScopeInfo($path)
	    {
	        $path .= '/' . $this->getScope();
	        if ('default' != $this->getScope()) {
	            $path .= '/' . $this->getScopeId();
	        }
	        return $path;
	    }
	
	    protected function _getAllowedExtensions()
	    {
	        return array('key', 'cer', 'crt');
	    }
	}