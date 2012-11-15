<?php

class MageWorx_MultiFees_Model_Resource_Eav_Mysql4_Setup extends Mage_Eav_Model_Entity_Setup
{
    public function getDefaultEntities()
    {
        return array(
            'catalog_product' => array(
                'entity_model'      => 'catalog/product',
                'attribute_model'   => 'catalog/resource_eav_attribute',
                'table'             => 'catalog/product',
                'additional_attribute_table' => 'catalog/eav_attribute',
                'entity_attribute_collection' => 'catalog/product_attribute_collection',
                'attributes'        => array(
					'additional_fees' => array(
                        'group'             => 'Prices',
                        'type'              => 'int',
                        'backend'           => 'multifees/catalog_product_attribute_backend_additional_fees',
                        'frontend'          => '',
                        'label'             => 'Additional Fees',
                        'input'             => 'multiselect',
                        'class'             => '',
                        'source'            => 'multifees/catalog_product_attribute_source_additional_fees',
                        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
                        'visible'           => true,
                        'required'          => false,
                        'user_defined'      => false,
                        'default'           => '',
                        'searchable'        => false,
                        'filterable'        => false,
                        'comparable'        => false,
                        'visible_on_front'  => false,
                        'unique'            => false,
                    ),
                ),
            ),
        );
    }

}
