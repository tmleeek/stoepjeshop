<?php
/**
 * @copyright  Copyright (c) 2009 AITOC, Inc. 
 * @author Andrei
 */
abstract class Aitoc_Aitsys_Model_Rewriter_Abstract extends Aitoc_Aitsys_Abstract_Model
{
    protected $_etcDir          = '';
    protected $_codeDir         = '';
    protected $_rewriteDir      = '';
    protected $_checkClassDir   = array();
    protected $_phpcli          = false;
    
    const REWRITE_CACHE_DIR = '/var/ait_rewrite/';
    
    public function __construct()
    {
        $this->_etcDir      = Mage::getRoot().'/etc/';
        $this->_codeDir     = Mage::getRoot().'/code/';
        $this->_rewriteDir  = dirname(Mage::getRoot()) . self::REWRITE_CACHE_DIR;
        
        $this->_checkClassDir[] = $this->_codeDir . 'local/';
        $this->_checkClassDir[] = $this->_codeDir . 'community/';
        $this->_checkClassDir[] = $this->_codeDir . 'core/';
        
        if (!file_exists($this->_rewriteDir))
        {
            @mkdir($this->_rewriteDir);
        }
        
        if (defined('STDIN') OR isset($_SERVER['argc']) OR isset($_SERVER['argv']))
        {
            $this->_phpcli = true;
        }
    }
    
    // last changes start
        
    public function grantAll( $path , $recursive = true )
    {
        if (function_exists('chmod'))
        {
            @chmod($path, 0777);
            if ($recursive = is_dir($path))
            {
                $items = new RecursiveDirectoryIterator($path);
                foreach ($items as $item)
                {
                    $this->grantAll((string)$item,false);
                }
            }
        }
        return $this;
    }
    
    public function isPhpCli()
    {
        return $this->_phpcli;
    }
    
}