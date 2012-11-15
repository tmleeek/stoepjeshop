<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 * 
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Onsale
 * @copyright  Copyright (c) 2009-2010 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 */
class AW_Onsale_Block_System_Entity_Form_Element_Position_Render extends Mage_Adminhtml_Block_Abstract
{
    /**
     * Path to element template
     */
    const TEMPLATE_PATH = 'aw_onsale/form/element/render/position.phtml';

    protected function  _construct()
    {
        parent::_construct();
        $this->setTemplate(self::TEMPLATE_PATH);

    }

    public function getName()
    {
        return $this->getData('name') ? $this->getData('name') : $this->getData('html_id');
    }
    
}
