<?php
/**
 * Product:     1.4.0.x-1.5.0.x
 * Package:     Aitoc_Aitdownloadablefiles_2.1.1_44829
 * Purchase ID: EUuGgWCe9R8lNix4kkddD7KpG37GhZF23A92LkTSFp
 * Generated:   2011-02-18 14:24:10
 * File path:   app/code/local/Aitoc/Aitdownloadablefiles/Helper/Data.php
 * Copyright:   (c) 2011 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitdownloadablefiles')){ ohhgsYehrjcpeahr('99297b5209963ddd7a32656d795bd635'); ?><?php
/**
 * @copyright  Copyright (c) 2010 AITOC, Inc. 
 */

class Aitoc_Aitdownloadablefiles_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function isManualRendered()
	{
		return Mage::getStoreConfigFlag('catalog/aitdownloadablefiles/custom_links_render');
	}
} } 