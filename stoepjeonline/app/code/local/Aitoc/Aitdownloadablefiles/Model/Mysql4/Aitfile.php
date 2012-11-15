<?php
/**
 * Product:     1.4.0.x-1.5.0.x
 * Package:     Aitoc_Aitdownloadablefiles_2.1.1_44829
 * Purchase ID: EUuGgWCe9R8lNix4kkddD7KpG37GhZF23A92LkTSFp
 * Generated:   2011-02-18 14:24:10
 * File path:   app/code/local/Aitoc/Aitdownloadablefiles/Model/Mysql4/Aitfile.php
 * Copyright:   (c) 2011 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitdownloadablefiles')){ TNNksYPijWAhPdNt('6cc0e82b66f9b9219813d59f04a16435'); ?><?php
/**
 * @copyright  Copyright (c) 2009 AITOC, Inc. 
 */

class Aitoc_Aitdownloadablefiles_Model_Mysql4_Aitfile extends Mage_Core_Model_Mysql4_Abstract
{

    /**
     * Varien class constructor
     *
     */
    protected function  _construct()
    {
        $this->_init('aitdownloadablefiles/aitfile', 'aitfile_id');
#        $this->_init('downloadable/aitfile', 'aitfile_id');
    }

    public function saveItemTitle($aitfileObject)
    {
        $stmt = $this->_getReadAdapter()->select()
            ->from($this->getTable('aitdownloadablefiles/aitfile_title'))
            ->where('aitfile_id = ?', $aitfileObject->getId())
            ->where('store_id = ?', $aitfileObject->getStoreId());
        if ($this->_getReadAdapter()->fetchOne($stmt)) {
            $where = $this->_getReadAdapter()->quoteInto('aitfile_id = ?', $aitfileObject->getId()) .
                ' AND ' . $this->_getReadAdapter()->quoteInto('store_id = ?', $aitfileObject->getStoreId());
            if ($aitfileObject->getUseDefaultTitle()) {
                $this->_getWriteAdapter()->delete(
                    $this->getTable('aitdownloadablefiles/aitfile_title'), $where);
            } else {
                $this->_getWriteAdapter()->update(
                    $this->getTable('aitdownloadablefiles/aitfile_title'),
                    array('title' => $aitfileObject->getTitle()), $where);
            }
        } else {
            if (!$aitfileObject->getUseDefaultTitle()) {
                $this->_getWriteAdapter()->insert(
                    $this->getTable('aitdownloadablefiles/aitfile_title'),
                    array(
                        'aitfile_id' => $aitfileObject->getId(),
                        'store_id' => $aitfileObject->getStoreId(),
                        'title' => $aitfileObject->getTitle(),
                    ));
            }
        }
        return $this;
    }

    public function deleteItems($items)
    {
        $where = '';
        if ($items instanceof Aitoc_Aitdownloadablefiles_Model_Aitfile) {
            $where = $this->_getReadAdapter()->quoteInto('aitfile_id = ?', $items->getId());
        }
        elseif (is_array($items)) {
            $where = $this->_getReadAdapter()->quoteInto('aitfile_id in (?)', $items);
        }
        else {
            $where = $this->_getReadAdapter()->quoteInto('aitfile_id = ?', $items);
        }
        if ($where) {
            $this->_getReadAdapter()->delete(
                $this->getTable('aitdownloadablefiles/aitfile'),$where);
            $this->_getReadAdapter()->delete(
                $this->getTable('aitdownloadablefiles/aitfile_title'), $where);
        }
        return $this;
    }

} } 