<?php
namespace Niks\LayeredNavigation\Controller;

use Magento\Framework\UrlInterface;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Niks\LayeredNavigation\Model\Url\Builder;
use Niks\LayeredNavigation\Model\Url\Hydrator;

/**
 * Class Router
 * @package Niks_LayeredNavigation
 */
class Router extends \Magento\UrlRewrite\Controller\Router implements \Magento\Framework\App\RouterInterface
{
    /** @var \Niks\LayeredNavigation\Model\Url\Hydrator  */
    protected $urlHydrator;

    /** @var \Magento\Framework\Registry  */
    protected $registry;

    /** @var int */
    protected $maxInteration = 3;

    /** @var array */
    protected $storageFilters = array();

    /**
     * @param \Magento\Framework\App\ActionFactory $actionFactory
     * @param \Magento\Framework\UrlInterface $url
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\ResponseInterface $response
     * @param UrlFinderInterface $urlFinder
     * @param Hydrator $urlHydrator
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Magento\Framework\App\ActionFactory $actionFactory,
        \Magento\Framework\UrlInterface $url,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\ResponseInterface $response,
        UrlFinderInterface $urlFinder,
        Hydrator $urlHydrator,
        \Magento\Framework\Registry $registry
    ) {
        $this->urlHydrator = $urlHydrator;
        $this->registry = $registry;
        parent::__construct($actionFactory, $url, $storeManager, $response, $urlFinder);
    }

    /**
     * Match corresponding navigation URL and modify request
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return \Magento\Framework\App\ActionInterface|null
     */
    public function match(\Magento\Framework\App\RequestInterface $request)
    {
        $parentMatch = parent::match($request);

        if ($parentMatch !== null) {
            $request->setAlias(
                Builder::REWRITE_NAVIGATION_PATH_ALIAS,
                ltrim($request->getOriginalPathInfo(), '/')
            );
            return $parentMatch;
        }

        $this->maxInteration = substr_count($request->getPathInfo(), "/") - 1;
        $_requestPathInfo = $request->getPathInfo();

        do {
            $filterString = '/' . $this->urlHydrator->getFilterString($_requestPathInfo);
            $originalPath = preg_replace('%' . $filterString . '(?!.*' . $filterString . '.*)%', '', $_requestPathInfo);
            
            $rewrite = $this->getRewrite($originalPath, $this->storeManager->getStore()->getId());

            if ($rewrite && $rewrite->getRedirectType()) {
                return $this->processRedirect($request, $rewrite);
            }

            $this->storageFilters[] = ltrim($filterString, '/');
            $_requestPathInfo = $originalPath;
        } while ($this->maxInteration-- > 1 && !$rewrite);
        
        if ($rewrite === null) {
            return null;
        }
        if ($rewrite->getRedirectType()) {
            return $this->processRedirect($request, $rewrite);
        }

        $this->registry->register('current_category_id', $rewrite->getEntityId());
        $filterParams = $this->urlHydrator->extract($this->storageFilters, $_requestPathInfo);
        
        if (empty($filterParams)) {
            return null;
        }
        
        $request->setParam('custom_rewrite_url_filters_values', $this->storageFilters);
        $request->setParam('navigation_filters', $filterParams);
        $request->setAlias(UrlInterface::REWRITE_REQUEST_PATH_ALIAS, ltrim($request->getPathInfo(), '/'));
        $request->setAlias(Builder::REWRITE_NAVIGATION_PATH_ALIAS, $rewrite->getRequestPath());
        $request->setPathInfo('/' . $rewrite->getTargetPath());
        return $this->actionFactory->create(\Magento\Framework\App\Action\Forward::class);
    }
}
