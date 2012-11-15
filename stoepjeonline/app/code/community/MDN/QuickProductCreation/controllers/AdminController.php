<?php

class MDN_QuickProductCreation_AdminController extends Mage_Adminhtml_Controller_Action
{
    public function FormAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function CreateProductsAction()
    {
        $data = $this->getRequest()->getPost('products');
        $result = array();

        for($i=1;$i<=count($data);$i++)
        {
            $productName = $data[$i]['name'];

            try
            {
                $product = mage::helper('QuickProductCreation/ProductCreation')->createProduct($data[$i]);
                $result[] = array('result' => 'success', 'product' => $product);
            }
            catch(Exception $ex)
            {
                $msg = mage::helper('QuickProductCreation')->__('Error creating product %s : %s', $productName, $ex->getMessage());
                $result[] = array('result' => 'error', 'message' => $msg);
            }
        }

        //render
        $this->loadLayout();
        $this->getLayout()->getBlock('result')->setResult($result);
        $this->renderLayout();

    }
}
