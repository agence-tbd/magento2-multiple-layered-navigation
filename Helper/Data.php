<?php
namespace Niks\LayeredNavigation\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_AJAX_ENABLED          = 'niks_layered_navigation/general/ajax';
    const XML_PATH_FRIENDLY_URLS_ENABLED = 'niks_layered_navigation/general/friendly_urls';
    const XML_PATH_SLIDER_ENABLED        = 'niks_layered_navigation/general/slider';
    const XML_PATH_ACCORDION_ENABLED     = 'niks_layered_navigation/general/accordion';
    const XML_PATH_CUSTOM_TITLE_ENABLED  = 'niks_layered_navigation/general/custom_title';

    public function isAjaxEnabled()
    {
        return $this->scopeConfig->isSetFlag(static::XML_PATH_AJAX_ENABLED);
    }

    public function isFriendlyUrlsEnabled()
    {
        return $this->scopeConfig->isSetFlag(static::XML_PATH_FRIENDLY_URLS_ENABLED);
    }

    public function isSliderEnabled()
    {
        return $this->scopeConfig->isSetFlag(static::XML_PATH_SLIDER_ENABLED);
    }

    public function isAccordionEnabled()
    {
        return $this->scopeConfig->isSetFlag(static::XML_PATH_ACCORDION_ENABLED);
    }

    public function isCustomTitleEnabled()
    {
        return $this->scopeConfig->isSetFlag(static::XML_PATH_CUSTOM_TITLE_ENABLED);
    }
}
