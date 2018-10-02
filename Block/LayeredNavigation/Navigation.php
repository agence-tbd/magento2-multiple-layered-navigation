<?php

namespace Niks\LayeredNavigation\Block\LayeredNavigation;

use Magento\Framework\View\Element\Template;
use Niks\LayeredNavigation\Model\Url\Hydrator;

class Navigation extends \Magento\LayeredNavigation\Block\Navigation
{
    const CONFIG_CUSTOM_TITLE_FILTERS = 'niks_layered_navigation/general/custom_title';

    protected $_pageConfig;

    protected $_scopeConfig;

    /**
     * @var \Niks\LayeredNavigation\Model\Url\Hydrator
     */
    protected $urlHydrator;

    /**
     * @var \Magento\Framework\Registry $registry
     */
    protected $_registry;

    /**
    * @param Template\Context $context
    * @param \Magento\Catalog\Model\Layer\Resolver $layerResolver
    * @param \Magento\Catalog\Model\Layer\FilterList $filterList
    * @param \Magento\Catalog\Model\Layer\AvailabilityFlagInterface $visibilityFlag
    * @param Hydrator $urlHydrator
    * @param \Magento\Framework\Registry $registry
    * @param array $data
    */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        \Magento\Catalog\Model\Layer\FilterList $filterList,
        \Magento\Catalog\Model\Layer\AvailabilityFlagInterface $visibilityFlag,
        Hydrator $urlHydrator,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_scopeConfig = $context->getScopeConfig();
        $this->_pageConfig = $context->getPageConfig();
        $this->urlHydrator = $urlHydrator;
        $this->_registry = $registry;
        parent::__construct($context, $layerResolver, $filterList, $visibilityFlag, $data);
    }

    /**
    * Apply layer
    *
    * @return $this
    */
    protected function _prepareLayout()
    {
        $this->renderer = $this->getChildBlock('renderer');
        foreach ($this->filterList->getFilters($this->_catalogLayer) as $filter) {
            $filter->apply($this->getRequest());
        }
        $this->getLayer()->apply();

        if ($this->_scopeConfig->getValue(self::CONFIG_CUSTOM_TITLE_FILTERS)) {
            $filtersValues = $this->getRequest()->getParam('custom_rewrite_url_filters_values');
            if (!empty($filtersValues)) {
                $category = $this->_registry->registry('current_category');
                if ($newTitle = $this->urlHydrator->getCustomPageTitle($filtersValues, $category)) {
                    $this->_pageConfig->getTitle()->set($newTitle);
                }
            }
        }
                                                                     
        return parent::_prepareLayout();
    }
}
