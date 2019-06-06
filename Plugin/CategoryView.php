<?php

namespace Niks\LayeredNavigation\Plugin;

use Niks\LayeredNavigation\Helper\Data;

class CategoryView
{
    /**
     * @var \Niks\LayeredNavigation\Helper\Data
     */
    protected $helper;

    public function __construct(Data $helper)
    {
        $this->helper = $helper;
    }

    public function afterExecute(\Magento\Catalog\Controller\Category\View $subject, \Magento\Framework\Controller\ResultInterface $result)
    {
        if(!$this->helper->isAjaxEnabled()){
            return $result;
        }
        if ($subject->getRequest()->isXmlHttpRequest() && $subject->getRequest()->getParam('niksAjax', false)) {
            $subject->getResponse()->setHeader('Content-Type', 'application/json', true);
            $navigationBlock = $result->getLayout()->getBlock('catalog.leftnav');
            $productsBlock = $result->getLayout()->getBlock('category.products');
            if ($navigationBlock) {
                return $subject->getResponse()->setBody(json_encode(['products' => $productsBlock->toHtml(), 'leftnav' => $navigationBlock->toHtml()]));
            }
        }
        return $result;
    }
}
