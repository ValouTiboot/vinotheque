<?php

class CategoryController extends CategoryControllerCore
{
    public function init()
    {
        $this->context->smarty->assign(array(
            'link' => $this->context->link,
        ));

        parent::init();
    }

    public function getHighlightImage()
    {
        $images_types = ImageType::getImagesTypes('categories');
        $images = [];
        $ext = pathinfo($this->category->id_image_highlight, PATHINFO_EXTENSION);

        foreach ($images_types as $k => $image_type)
        if (file_exists(_PS_CAT_IMG_DIR_.'highlight/'.$this->category->id.'-'.$image_type['name'].'.'.$ext))
            $images[$image_type['name']] = $this->context->link->getMediaLink('/img/c/highlight/'.$this->category->id.'-'.$image_type['name'].'.'.$ext);

        return $images;
    }

    protected function getTemplateVarCategory()
    {
        $category = $this->objectPresenter->present($this->category);
        $category['image'] = $this->getImage(
            $this->category,
            $this->category->id_image
        );
        $category['image_highlight'] = $this->getHighlightImage();
        $category['best_seller'] = $this->getBestSeller();
        $category['last_wine'] = $this->getLastWine();

        return $category;
    }

    public function getBestSeller()
    {
        $sql = "SELECT P.`id_product`, SUM(OD.`product_quantity`) as mq
            FROM `" . _DB_PREFIX_ . "product` P
            RIGHT JOIN `" . _DB_PREFIX_ . "order_detail` OD ON OD.`product_id`=P.`id_product`
            WHERE P.`active`='1' AND P.`id_category_default`='" . pSQL($this->category->id) . "'
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

        return $presenter->present(
            $productSettings,
            $assembler->assembleProduct($product),
            $this->context->language
        );
    }

    public function getLastWine()
    {
        $sub = [];
        $categories = [$this->category->id];
        $sub_categories = $this->category->getAllChildren();

        if ($sub_categories)
        foreach ($sub_categories as $scat)
            $sub[] = $scat->id;

        $categories = array_merge($categories, $sub);

        $sql = "SELECT p.* FROM `"._DB_PREFIX_."product` p
            LEFT JOIN `"._DB_PREFIX_."category_product` cp ON cp.`id_product`=cp.`id_product`
            WHERE p.`active`='1'
            AND p.`wine`='1'
            AND p.`wine_date` <= NOW()
            AND cp.`id_category` IN (".implode(',', $categories).")
            ORDER BY p.`wine_date` DESC
        ";

        $product = \Db::getInstance()->getRow($sql);

        if (!$product)
            return false;

        $productSettings = $this->getProductPresentationSettings();
        $presenter = $this->getProductPresenter();
        $assembler = new ProductAssembler(Context::getContext());

        return $presenter->present(
            $productSettings,
            $assembler->assembleProduct($product),
            $this->context->language
        );
    }
}
