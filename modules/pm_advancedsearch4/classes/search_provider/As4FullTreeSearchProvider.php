<?php
/**
 *
 * @author Presta-Module.com <support@presta-module.com>
 * @copyright Presta-Module
 * @license   Commercial
 *
 *           ____     __  __
 *          |  _ \   |  \/  |
 *          | |_) |  | |\/| |
 *          |  __/   | |  | |
 *          |_|      |_|  |_|
 *
 ****/

use PrestaShop\PrestaShop\Core\Product\Search\SortOrder;
use PrestaShop\PrestaShop\Core\Product\Search\ProductSearchProviderInterface;
use PrestaShop\PrestaShop\Core\Product\Search\ProductSearchContext;
use PrestaShop\PrestaShop\Core\Product\Search\ProductSearchQuery;
use PrestaShop\PrestaShop\Core\Product\Search\ProductSearchResult;
use PrestaShop\PrestaShop\Core\Product\Search\SortOrderFactory;
use Symfony\Component\Translation\TranslatorInterface;
class As4FullTreeSearchProvider implements ProductSearchProviderInterface
{
    private $module;
    private $translator;
    private $sortOrderFactory;
    public function __construct(PM_AdvancedSearch4 $module, TranslatorInterface $translator)
    {
        $this->module = $module;
        $this->translator = $translator;
        $this->sortOrderFactory = new SortOrderFactory($this->translator);
    }
    public function runQuery(
        ProductSearchContext $context,
        ProductSearchQuery $query
    ) {
        $result = new ProductSearchResult();
        $result->setAvailableSortOrders(
            $this->sortOrderFactory->getDefaultSortOrders()
        );

        $is_cat_primeur = [22];
        $cat_primeur = new Category(22);
        // $is_cat_primeur = [77];
        // $cat_primeur = new Category(77);
        $sub_categories = $cat_primeur->getAllChildren();

        if ($sub_categories)
        foreach ($sub_categories as $scat)
            $is_cat_primeur[] = $scat->id;

        // seulement si c'est un primeur
        if (in_array(Context::getContext()->controller->getCategory()->id_category, $is_cat_primeur))
        {        
            $result->addAvailableSortOrder((new SortOrder('product', 'wine_date', 'desc'))->setLabel($this->module->l('Date de sortie', 'as4searchprovider')));

            if (($encodedSortOrder = Tools::getValue('order'))) {
                $query->setSortOrder(SortOrder::newFromString(
                    $encodedSortOrder
                ));
            }
            else
                $query->setSortOrder((new SortOrder('product', 'wine_date', 'desc'))->setLabel($this->module->l('Date de sortie', 'as4searchprovider')));
        }
        
        $continue = true;
        $realContext = Context::getContext();
        if (As4SearchEngine::isSPAModuleActive()) {
            $pm_productsbyattributes = Module::getInstanceByName('pm_productsbyattributes');
            if (version_compare($pm_productsbyattributes->version, '1.0.4', '>=')) {
                $continue = false;
                $productCount = $pm_productsbyattributes->getCategoryProducts((int)$realContext->controller->getCategory()->id_category, null, null, null, $query->getSortOrder()->toLegacyOrderBy(), $query->getSortOrder()->toLegacyOrderWay(), true, true);
                $productList = $pm_productsbyattributes->getCategoryProducts((int)$realContext->controller->getCategory()->id_category, (int)$context->getIdLang(), (int)$query->getResultsPerPage(), (int)$query->getPage(), $query->getSortOrder()->toLegacyOrderBy(), $query->getSortOrder()->toLegacyOrderWay(), false, true);
                $result->setTotalProductsCount($productCount);
                $result->setProducts($productList);
                $pm_productsbyattributes->splitProductsList($productList);
            }
        }
        if ($continue) {
            $result->setTotalProductsCount($this->module->getCategoryProducts(null, null, null, $query->getSortOrder()->toLegacyOrderBy(), $query->getSortOrder()->toLegacyOrderWay(), true));
            $result->setProducts($this->module->getCategoryProducts((int)$context->getIdLang(), (int)$query->getPage(), (int)$query->getResultsPerPage(), $query->getSortOrder()->toLegacyOrderBy(), $query->getSortOrder()->toLegacyOrderWay()));
        }
        return $result;
    }
}
