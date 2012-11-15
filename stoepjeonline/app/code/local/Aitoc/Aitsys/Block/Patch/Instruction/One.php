<?php
/**
 * @copyright  Copyright (c) 2009 AITOC, Inc. 
 */

class Aitoc_Aitsys_Block_Patch_Instruction_One extends Aitoc_Aitsys_Abstract_Adminhtml_Block
{
    protected $_sourceFile    = '';
    protected $_extensionPath = '';
    protected $_extensionName = '';
    protected $_patchFile     = '';
    protected $_removeBasedir = true;
    
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('aitsys/patch/instruction/one.phtml');
    }
    
    public function setSourceFile($path)
    {
        $this->_sourceFile = $path;
    }
    
    public function setExtensionPath($path)
    {
        $this->_extensionPath = $path;
    }
    
    public function setExtensionName($name)
    {
        $this->_extensionName = $name;
    }
    
    public function setPatchFile($file)
    {
        $this->_patchFile = $file;
    }
    
    protected function _getBaseDir()
    {
        return str_replace(array('/','\\'),DS,Mage::getBaseDir());
    }
    
    public function getSourceFile($includeBasedir = false)
    {
        $path = $this->_sourceFile;
        $path = str_replace(array('/','\\'),DS,$path);
        if ($this->_removeBasedir && !$includeBasedir)
        {
            $path = str_replace($this->_getBaseDir(), '', $path);
        }
        return $path;
    }    
    
    public function getExtensionPath($includeBasedir = false)
    {
        $path = $this->_extensionPath;
        $path = str_replace(array('/','\\'),DS,$path);
        if ($this->_removeBasedir && !$includeBasedir)
        {
            $path = str_replace($this->_getBaseDir(),'', $path);
        }
        return $path;
    }
    
    public function getExtensionName()
    {
        return $this->_extensionName;
    }
    
    public function getPatchFile()
    {
        return str_replace(array('/','\\'),DS,$this->_patchFile);
    }
    
    public function getPatchedFileName()
    {
        return str_replace('.patch', '', $this->getPatchFile());
    }
    
    public function getDestinationFile()
    {
        $destinationFile = str_replace(Mage::getBaseDir('app'), Mage::getBaseDir('var') . DS . 'ait_patch', $this->getSourceFile(true));
        $destinationFile = substr($destinationFile, 0, strrpos($destinationFile, DS) + 1);
        $destinationFile = str_replace(strstr($destinationFile,'template'),'',$destinationFile);
        $destinationFile .= 'template'.DS.'aitcommonfiles'.DS.str_replace('.patch', '', $this->getPatchFile());
        if ($this->_removeBasedir)
        {
            $destinationFile = str_replace($this->_getBaseDir(), '', $destinationFile);
        }
        return $destinationFile;
    }
    
    public function getDestinationDir()
    {
        return dirname($this->getDestinationFile());
    }
    
    public function getPatchConfigPath()
    {
        $config = $this->getExtensionPath() . DS . 'etc' . DS . 'custom.data.xml';
        return htmlspecialchars($config);
    }
    
    public function getPatchConfigLine()
    {
        $configLine = '<file path="' . substr($this->getPatchFile(), 0, strpos($this->getPatchFile(), '.')) . '"></file>';
        return htmlspecialchars($configLine);
    }
    
    /**
    * 
    * @return Aitoc_Aitsys_Model_Aitfilepatcher
    */
    protected function _makeAitfilepatcher()
    {
        return Mage::getModel('aitsys/aitfilepatcher');
    }
    
    public function getPatchContents()
    {
        $patcher = $this->_makeAitfilepatcher();
        $patchPath = $this->getExtensionPath(true) . DS . 'data' . DS . $this->getPatchFile();
        $patchInfo = $patcher->parsePatch(file_get_contents($patchPath));
        $beforePart = false;
        $afterPart  = false;
        $addPart    = false;
        $lineCounter = 0;
        $html = '<div class="patch">';
        foreach ($patchInfo as $_data)
        {
            foreach ($_data['aChanges'] as $_data)
            {
                foreach ($_data['aChangingStrings'] as $_line)
                {
                    $lineNum = $lineCounter;
                    $line = $_line[0] . $_line[2];
                    if (0 === strpos($line, 'diff') || 0 === strpos($line, '---') || 0 === strpos($line, '+++') || !$line)
                    {
                        continue;
                    }
                    if (0 === strpos($line, '@@'))
                    {
                        $afterPart  = false;
                        $addPart    = false;
                        $beforePart = true;
                        
                        if ($afterPart)
                        {
                            $html .= '</pre>';
                            $afterPart = false;
                        }
                        if ($beforePart)
                        {
                            $html .= '</pre>';
                            $beforePart = false;
                        }
                        $lineNum = intval(substr($line, strpos($line, '-') + 1));
                        $html .= $this->__('The place to add the code will be AFTER the following code or similar to it near line %d &mdash;', $lineNum);
                        $html .= '<pre>';
                        $beforePart = true;
                        continue;
                    }

                    if (0 === strpos($line, '+'))
                    {
                        $line = substr($line, 1); // removing + 
                        if (!$addPart)
                        {
                            if ($beforePart)
                            {
                                $html .= '</pre>';
                                $beforePart = false;
                            }
                            if ($afterPart)
                            {
                                $html .= '</pre>';
                                $afterPart = false;
                            }
                            $addPart    = true;
                            $html .= $this->__('You will need to add the following lines &mdash;') ;
                            $html .= '<pre>';
                        }
                    } 
                    else 
                    {
                        if ($addPart)
                        {
                            $html .= '</pre>';
                            $addPart = false;
                            $html .= $this->__('The above lines should be added BEFORE the following code or similar to it &mdash;');
                            $html .= '<pre>';
                            $afterPart = true;
                        }
                    }
                    $html .= htmlspecialchars($line) . "\r\n";
                    ++$lineCounter;
                }
            }
        }
        $html .= '</div>';
        return $html;
    }
    
    protected function _prepareLayout()
    {
        return parent::_prepareLayout();
    }

}