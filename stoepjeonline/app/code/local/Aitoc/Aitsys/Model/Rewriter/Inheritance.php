<?php
/**
 * @copyright  Copyright (c) 2009 AITOC, Inc. 
 * @author Andrei
 */
class Aitoc_Aitsys_Model_Rewriter_Inheritance extends Aitoc_Aitsys_Model_Rewriter_Abstract
{
    
    public function loadOrderConfig()
    {
        $path = $this->_etcDir.'local.xml';
        if (file_exists($path))
        {
            $config = new Zend_Config_Xml($path);
            $conn = new Varien_Db_Adapter_Pdo_Mysql(array(
                'host'     => (string)$config->global->resources->default_setup->connection->host,
                'username' => (string)$config->global->resources->default_setup->connection->username,
                'password' => (string)$config->global->resources->default_setup->connection->password,
                'dbname'   => (string)$config->global->resources->default_setup->connection->dbname ,
                'type'     => 'pdo_mysql' ,
                'model'    => 'mysql4' ,
                'active'   => 1
            ));
            $select = $conn->select()->from(
                $config->global->resources->db->table_prefix.'core_config_data'
            )->where('path = ?','aitsys_rewriter_classorder')->where('scope = ?','default');
            $data = $conn->fetchRow($select);
            $conn->closeConnection();
            if (!$data)
            {
                return null;
            }
            return unserialize($data['value']);
        }
        return null;
    }
    
    /**
    * Creates inheritance array
    * 
    * @param array $rewriteClasses
    * @param array $baseClass
    */
    public function build($rewriteClasses, $baseClass, $useOrdering = true)
    {
        $inheritedClasses = array();
        krsort($rewriteClasses);
        $rewriteClasses = array_values($rewriteClasses);
        
        $i = 0;
        while ($i < count($rewriteClasses))
        {
            $inheritedClasses[$rewriteClasses[$i]] = isset($rewriteClasses[++$i]) ? $rewriteClasses[$i] : $baseClass;
        }
        // reversing to make it read classed in order of existence
        $inheritedClasses = array_reverse($inheritedClasses, true);

        // sorting in desired order
        $order = $this->loadOrderConfig();
        if (!$order)
        {
            $order = array();
        }
        if (!isset($order[$baseClass]))
        {
            $config = Aitoc_Aitsys_Model_Rewriter_MageConfig::get()->getConfig();
            $tmp = array();
            foreach ($rewriteClasses as $class)
            {
                list($vendor,$name) = explode('_',$class,3);
                $priority = (string)$config->getNode('modules/'.$vendor.'_'.$name.'/priority');
                $priority = $priority ? $priority : 1;
                while (isset($tmp[$priority]))
                {
                    ++$priority;
                }
                $tmp[$priority] = $class;
            }
            krsort($tmp);
            $i = 0;
            $order[$baseClass] = array();
            foreach ($tmp as $class)
            {
                $order[$baseClass][$class] = ++$i;
            }
        }
        if ($useOrdering && isset($order[$baseClass]))
        {
            $orderedClasses = array_flip($order[$baseClass]);
            ksort($orderedClasses);
            $orderedClasses = array_values($orderedClasses);
            
            $i             = 0;
            $replaceClass = array();
            while ($i < count($orderedClasses))
            {
                $contentsFromClass = $orderedClasses[$i];
                if (0 == $i && $orderedClasses[$i] != $rewriteClasses[$i])
                {
                    $parentClass = $rewriteClasses[$i];
                    $replaceClass[$rewriteClasses[$i]] = $orderedClasses[$i];
                } 
                else 
                {
                    $parentClass = $orderedClasses[$i];
                    if (isset($replaceClass[$parentClass]))
                    {
                        $parentClass = $replaceClass[$parentClass];
                    }
                }
                if (isset($orderedClasses[$i+1]))
                {
                    $childClass = $orderedClasses[$i+1];
                    if (isset($replaceClass[$childClass]))
                    {
                        $childClass = $replaceClass[$childClass];
                    }
                } else 
                {
                    $childClass = $baseClass;
                }
                $orderedInheritance[] = array(
                    'contents'  => $contentsFromClass,
                    'parent'    => $parentClass,
                    'child'     => $childClass,
                );
                $i++;
            }
            if ($orderedInheritance)
            {
                krsort($orderedInheritance);
                $inheritedClasses = $orderedInheritance;
            }
        }
        
        return $inheritedClasses;
    }
    
    public function buildAbstract($rewriteClass, $baseClass)
    {
        $inheritedClasses = array();
        $inheritedClasses[] = array(
            'contents'  => $baseClass,
            'parent'    => $rewriteClass,
            'child'     => '', // empty to keep current
        );
        $inheritedClasses[] = array(
            'contents'  => $rewriteClass,
            'parent'    => $baseClass,
            'child'     => $rewriteClass,
        );
        return $inheritedClasses;
    }
}