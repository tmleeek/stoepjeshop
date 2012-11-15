<?php
/**
 * Product:     1.4.0.x-1.5.0.x
 * Package:     Aitoc_Aitdownloadablefiles_2.1.1_44829
 * Purchase ID: EUuGgWCe9R8lNix4kkddD7KpG37GhZF23A92LkTSFp
 * Generated:   2011-02-18 14:24:10
 * File path:   app/code/local/Aitoc/Aitdownloadablefiles/Model/Aitdownloadablefiles.php
 * Copyright:   (c) 2011 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitdownloadablefiles')){ qccesYDiyZUhDkcE('2ce98feffeaa59d0b3ea8458501058c3'); ?><?php
/**
 * @copyright  Copyright (c) 2009 AITOC, Inc. 
 */


class Aitoc_Aitdownloadablefiles_Model_Aitdownloadablefiles extends Mage_Eav_Model_Entity_Attribute
{
    public function getAitfileUrl($iAitfileId)
    {
        return $this->getUrl('downloadable/download/sample', array('sample_id' => $iAitfileId));
    }
    
}
 } ?>