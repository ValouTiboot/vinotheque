<?php

class CategoryController extends CategoryControllerCore
{
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

        return $category;
    }

    public function getBestSeller()
    {
    	$sql = "SELECT P.`id_product`, SUM(OD.`product_quantity`) as mq
    		FROM `" . _DB_PREFIX_ . "product` P
    		RIGHT JOIN `" . _DB_PREFIX_ . "order_detail` OD ON OD.`product_id`=P.`id_product`
    		WHERE P.`active`='1' AND P.`id_category_default`='" . pSQL($this->category->id) . "'
    		GROUP BY OD.`product_id`
    		ORDER BY mq DESC";

    	$products = Db::getInstance()->executeS($sql);

    	if (!count($products))
    		return false;

    	foreach ($products as $key => $product)
    	{
    		if ($key == '0')
    			continue;

    		if ($products[$key-1]['mq'] > $product['mq'])
    			unset($products[$key]);
    	}

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
}