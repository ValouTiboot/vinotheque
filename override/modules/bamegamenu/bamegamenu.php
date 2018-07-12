<?php

class BAMegaMenuOverride extends BAMegaMenu
{
    public function getSubMenuLink($items = array())
    {
        die('OK');
        if (empty($items)) {
            return false;
        }

        $sub = array();
        foreach ($items as $v)
            $sub[$this->getLinkTitleMenu($v)] = $v;

        ksort($sub);

        $html = '';
        $html .="<ul class='menulink clearfix'>";
        foreach ($sub as $v) 
        {
            $link=$this->getLinkMenu($v);
            if (empty($link)) {
                continue;
            }
            $html .="<li>";
            $html .="<a href='" . $this->getLinkMenu($v) . "' title='" . $this->getLinkTitleMenu($v)
            . "'><span class='menu-item-link-text'>" . $this->getLinkTitleMenu($v) . "</span></a>";
            $html .="</li>";
        }
        
        $html .="</ul>";
        return $html;
    }

	public function getSubMenuProduct($id, $list = 0)
    {
        if (is_null($id) || empty($id) || !$id)
            return false;

        if (Tools::version_compare(_PS_VERSION_, '1.7', '>='))
        {
            $assembler = new ProductAssembler($this->context);
            $presenterFactory = new ProductPresenterFactory($this->context);
            $productSettings = $presenterFactory->getPresentationSettings();
            $presenter = new ProductListingPresenter(
                new ImageRetriever(
                    $this->context->link
                ),
                $this->context->link,
                new PriceFormatter(),
                new ProductColorsRetriever(),
                $this->context->getTranslator()
            );

            $product = $presenter->present(
                $productSettings,
                $assembler->assembleProduct(array('id_product' => $id)),
                $this->context->language
            );
            
            // $html = "";
            // if ($list == 0)
            //     $html .= "<ul class='menuproduct clearfix'>";

            // $oldprice= (isset($product['has_discount']) && !empty($product['has_discount']) ? 1 : 0);

            // $html .= "<li>";
            // $html .= "<a href='" . $product['url'] . "' title='" . Tools::HtmlEntitiesUTF8($product['name']) . "'><span class='menu-item-link-text'>";
            // $html .= "<img src='" . $product['cover']['small']['url'] . "' alt='" . Tools::HtmlEntitiesUTF8($product['name']) . "'>";
            // $html .= '<span class="product-category-name">' . Tools::HtmlEntitiesUTF8($product['category_name']) . '</span>';
            // $html .= "<span class='name'>" . Tools::HtmlEntitiesUTF8($product['name']) . "</span>";
            // $html .= isset($product['features'][10]) && isset($product['features'][16]) ? '<span class="product-features">' . Tools::HtmlEntitiesUTF8($product['features'][10]['value']) . ' - ' . Tools::HtmlEntitiesUTF8($product['features'][16]['value']) . '</span>' : '';
            // $html .= "<span class='price' style='" . (!$oldprice ? 'width:100%;' : '') . "'>" . $product['price'] . "</span>";
            
            // if (isset($product['has_discount']) && !empty($product['has_discount'])) {
            //     $html .= "<span class='old_price'>". Tools::displayPrice(round($product['regular_price_amount'])) . "</span>";
            // }

            // $html .= "</span>";
            // $html .= "</a>";
            // $html .= "</li>";
            
            // if ($list == 0)
            //     $html .= "</ul>";

            // return $html;
        

            $oldprice= (isset($product['has_discount']) && !empty($product['has_discount']) ? 1 : 0);

            $html = "";
            if ($list == 0)
                $html .= "<ul class='menuproduct clearfix'>";

            $oldprice= (isset($product['has_discount']) && !empty($product['has_discount']) ? 1 : 0);

            $html .= '<li class="product-miniature">
                <div class="product-thumbnail-box">';
            $html .= '<a href="' . $product['url'] . '" class="thumbnail product-thumbnail" title="' . Tools::HtmlEntitiesUTF8($product['name']) . '">';
            $html .= '<div>
                        <img src="' . $product['cover']['medium']['url'] . '" alt="' . $product['name'] . '" data-full-size-image-url="' . $product['cover']['large']['url'] . '" />
                    </div>
                </a>';
                // {hook h='displayProductPictos' mod='pictogram' product=$product}
            $html .= '<div class="category-name">' . Tools::HtmlEntitiesUTF8($product['category_name']) . '</div>';
            $html .= '<div class="name">
                <h1 itemprop="name"><a href="' . $product['url'] . '">' . Tools::HtmlEntitiesUTF8($product['name']) . '</a></h1>
                <span class="feature-name">
                    ' . isset($product['features'][5]['value']) && isset($product['features'][6]['value']) ? $product['features'][5]['value'] . ' - ' . $product['features'][6]['value'] : '' . '
                </span>
            </div>';
            $html .= isset($product['features'][10]) && isset($product['features'][16]) ? '<span class="product-features">' . Tools::HtmlEntitiesUTF8($product['features'][10]['value']) . ' - ' . Tools::HtmlEntitiesUTF8($product['features'][16]['value']) . '</span>' : '';
            $html .= '<div class="product-price-and-shipping">' . $this->trans('A partir de', array(), 'Shop.Theme.Actions');
            
            if (isset($product['has_discount']) && !empty($product['has_discount'])) {
                $html .= '<span class="regular-price">'. Tools::displayPrice(round($product['regular_price'])) . '</span>';
            }
            $html .= '<span itemprop="price" class="price">' . Tools::displayPrice($product['price']) . '</span>';

            $html .= "</div>";
            $html .= "</div>";
            $html .= "</li>";
            
            if ($list == 0)
                $html .= "</ul>";

            return $html;
        }
        else
        {       
            $id_lang = (int) $this->context->language->id;
            $product = new Product((int) $id, true, (int) $id_lang);
            if ($product->active==0) {
                return '';
            }
            $image = Image::getCover($id);
            $link = new Link();
            // image formatted name
            $ImageFormattedName = '';
            if (Tools::version_compare(_PS_VERSION_, '1.7', '>=')) {
                $ImageFormattedName = ImageType::getFormattedName('home');
            } else {
                $ImageFormattedName = ImageType::getFormatedName('home');
            }
            
            $imagePath=$link->getImageLink($product->link_rewrite, $image['id_image'], $ImageFormattedName);
            $html = "";
            if ($list == 0) {
                $html .="<ul class='menuproduct clearfix'>";
            }
            $html .="<li>";
            $html .="<a href='" . Tools::HtmlEntitiesUTF8($product->getLink()) . "' title='" . $product->name . "'>
            <span class='menu-item-link-text'>";
            $html .="<img src='//" . $imagePath . "' alt='" . $product->name . "'>";
            $html .="<span class='name'>" . $product->name . "</span>";
            $oldprice=(!isset($product->specificPrice['reduction']) || $product->specificPrice['reduction']<=0)?false:true;
            $html .="<span class='price' style='" . ($oldprice==false? 'width:100%;' : '')
            . "'>" . Tools::displayPrice($product->getPrice(true, null, 2)) . "</span>";
            if (isset($product->specificPrice['reduction']) && $product->specificPrice['reduction'] > 0) {
                $PriceWithoutReduct = Tools::displayPrice(round($product->getPriceWithoutReduct(false, null), 2));
                $html .="<span class='old_price'>". $PriceWithoutReduct
                . "</span>";
            }
            $html .="</span>";
            $html .="</a>";
            $html .="</li>";
            if ($list == 0) {
                $html .="</ul>";
            }
            return $html;
        }
    }
}
