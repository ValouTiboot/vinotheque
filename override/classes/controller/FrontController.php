<?php
/**
* 2007-2014 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2014 PrestaShop SA
*  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/
class FrontController extends FrontControllerCore
{
    /*
    * module: lgseoredirect
    * date: 2017-06-28 13:43:22
    * version: 1.2.7
    */
    public function init()
    {
        if (Module::isInstalled('lgseoredirect')) {
            $uri_var = $_SERVER['REQUEST_URI'];
            $shop_id = Context::getContext()->shop->id;
            $redirect = Db::getInstance()->getRow(
                'SELECT * FROM '._DB_PREFIX_.'lgseoredirect '.
                'WHERE url_old = "'.pSQL($uri_var).'" '.
                'AND id_shop = "'.(int)$shop_id.'" '.
                'ORDER BY id DESC'
            );
            if ($redirect and $uri_var == $redirect['url_old'] and $shop_id == $redirect['id_shop']) {
                if ($redirect['redirect_type'] == 301) {
                    $header = 'HTTP/1.1 301 Moved Permanently';
                }
                if ($redirect['redirect_type'] == 302) {
                    $header = 'HTTP/1.1 302 Moved Temporarily';
                }
                if ($redirect['redirect_type'] == 303) {
                    $header = 'HTTP/1.1 303 See Other';
                }
                Tools::redirect($redirect['url_new'], __PS_BASE_URI__, null, $header);
            }
        }
        parent::init();
    }

    /**
     * Initializes common front page content: header, footer and side columns.
     */
    public function initContent()
    {
        $this->assignGeneralPurposeVariables();
        $this->process();

        if (!isset($this->context->cart)) {
            $this->context->cart = new Cart();
        }

        if ($this instanceof pm_advancedsearch4searchresultsModuleFrontController)
            $this->context->smarty->assign(array('category' => $this->getTemplateVarsCategory()));

        $this->context->smarty->assign(array(
            'HOOK_HEADER' => Hook::exec('displayHeader'),
        ));
    }

    protected function getTemplateVarsCategory()
    {
        $cat = new Category(Tools::getValue('id_category_search'), $this->context->language->id);
        $category = $this->objectPresenter->present($cat);
        $category['image'] = $this->getImage(
            $cat,
            $cat->id_image
        );

        $images_types = ImageType::getImagesTypes('categories');
        $images = [];
        $ext = pathinfo($cat->id_image_highlight, PATHINFO_EXTENSION);

        foreach ($images_types as $k => $image_type)
        if (file_exists(_PS_CAT_IMG_DIR_.'highlight/'.$cat->id.'-'.$image_type['name'].'.'.$ext))
            $images[$image_type['name']] = $this->context->link->getMediaLink('/img/c/highlight/'.$cat->id.'-'.$image_type['name'].'.'.$ext);

        $category['image_highlight'] = $images;
        
        $sql = "SELECT P.`id_product`, SUM(OD.`product_quantity`) as mq
            FROM `" . _DB_PREFIX_ . "product` P
            RIGHT JOIN `" . _DB_PREFIX_ . "order_detail` OD ON OD.`product_id`=P.`id_product`
            WHERE P.`active`='1' AND P.`id_category_default`='" . pSQL($cat->id) . "'
            GROUP BY OD.`product_id`
            ORDER BY mq DESC
            LIMIT 5";

        $products = Db::getInstance()->executeS($sql);

        if (!count($products))
            return false;

        $product = $products[rand(0,count($products)-1)];

        $productSettings = $this->getProductPresentationSettings();
        $presenter = $this->getProductPresenter();
        $assembler = new ProductAssembler(Context::getContext());

        $category['best_seller'] = $presenter->present(
            $productSettings,
            $assembler->assembleProduct($product),
            $this->context->language
        );

        return $category;
    }
}
