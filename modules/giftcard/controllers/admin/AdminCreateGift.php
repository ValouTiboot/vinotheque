<?php
/**
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    FMM Modules
*  @copyright 2017 FMM Modules
*  @license   FMM Modules
*  @version   1.4.1
*/

class AdminCreateGiftController extends ModuleAdminController
{
    public $msg = 0;
    public $gift = '';
    public function __construct()
    {
        parent::__construct();
        $this->table = 'gift_card';
        $this->className = 'Gift';
        $this->identifier = 'id_gift_card';
        $this->context = Context::getContext();
        $this->deleted = false;
        $this->bootstrap = true;
        $this->shopLinkType = 'shop';
    }

    public function postProcess()
    {
        $id_lang = $this->context->cookie->id_lang;
        $id_gift_card = (int)Tools::getValue('id_gift_card');

        if (Tools::isSubmit('Edit') && $id_gift_card) {
            $id_product = (int)Tools::getValue('id_product');
            $this->gift = Gift::getGiftCard($id_product, $id_gift_card, $id_lang);
        }

        if (Tools::isSubmit('SaveGift')) {
            $this->addData();
        }
        parent::postProcess();
    }

    public function renderList()
    {
        $this->addRowAction('view');
        $this->addRowAction('delete');
        $back = Tools::safeOutput(Tools::getValue('back', ''));
        if (empty($back)) {
            $back = self::$currentIndex.'&token='.$this->token;
        }

        $this->toolbar_btn = array(
            'save' => array(
            'href' => self::$currentIndex.'&configure='.$this->className.'&save'.$this->className.'&token='.$this->token,
            'desc' => $this->l('Save'),
            ),
        );

        $this->fields_form['submit'] = array(
            'title' => $this->l(' Save '),
            'class' => 'button'
        );

        $product = (Tools::getValue('id_product'))? new Product(Tools::getValue('id_product'), true): null;
        if ($product) {
            $cover = Product::getCover($product->id);
            $product = (array)$product;
            $product['id_cover'] = $cover['id_image'];
        }
        $currencies = Currency::getCurrencies(false, true, true);
        $module = new GiftCard();
        $languages = Language::getLanguages();
        $this->context->smarty->assign('current_lang', $this->context->language->id);
        $this->context->smarty->assign('languages', $languages);
        $this->context->smarty->assign('module', $module);

        $iso_tiny_mce = $this->context->language->iso_code;
        $iso_tiny_mce = (file_exists(_PS_JS_DIR_.'tiny_mce/langs/'.$iso_tiny_mce.'.js') ? $iso_tiny_mce : 'en');

        $shops = '';
        $selected_shops = '';
        if (Shop::isFeatureActive()) {
            $shops = $this->renderShops($product['id']);
            $selected_shops = (Gift::getShopsByProduct($product['id']))? implode(',', Gift::getShopsByProduct($product['id'])) : '';
        }
        $link = ($this->context->link)? $this->context->link : new Link();
        $this->context->smarty->assign(array('shops' => $shops, 'selected_shops' => $selected_shops));
        $this->context->smarty->assign(array(
            'iso_tiny_mce'              => $iso_tiny_mce,
            'Gift'                      => $this->gift,
            'currentIndex'              => self::$currentIndex,
            'currentToken'              => $this->token,
            'id_lang'                   => $this->context->language->id,
            'id_currency'               => $this->context->cookie->id_currency,
            'msg'                       => $this->msg,
            'link'                      => $link,
            'token'                     => $this->token,
            'current_id_tab'            => (int)$this->context->controller->id,
            'version'                   => _PS_VERSION_,
            'currencies'                => $currencies,
            'product'                   => $product,
            'tax_exclude_taxe_option'   => Tax::excludeTaxeOption(),
            'tax_rules_groups'          => TaxRulesGroup::getTaxRulesGroups(true),
            'ad'                        => __PS_BASE_URI__.basename(_PS_ADMIN_DIR_),
            'default_currency'          => Configuration::get('PS_CURRENCY_DEFAULT')
        ));
        parent::renderList();
        return $this->context->smarty->fetch(dirname(__FILE__).'/../../views/templates/admin/_add/helpers/form/form.tpl');
    }

    public function addData()
    {
        $flag               = 1;
        $now                = date('Y-m-d H:i:s');
        $id_lang            = $this->context->cookie->id_lang;

        //** fetching form values
        $card_name          = Tools::getValue('card_name');
        $length             = (int)Tools::getValue('length');
        $vcode_type         = (string)Tools::getValue('vcode_type');
        $card_qty           = (int)Tools::getValue('qty');
        $active             = (int)Tools::getValue('status');
        $free_ship          = (int)Tools::getValue('free_shipping');
        $from               = (string)Tools::getValue('from');
        $to                 = (string)Tools::getValue('to');
        $value_type         = (string)Tools::getValue('value_type');
        $card_value         = Tools::getValue('card_value');
        $red_type           = (string)Tools::getValue('apply_discount');
        $reduction_product  = (int)Tools::getValue('reduction_product');
        $apply_discount_to  = (string)Tools::getValue('apply_discount_to');
        $id_product         = (int)Tools::getValue('id_product');
        $min                = (int)Tools::getValue('min');
        $max                = (int)Tools::getValue('max');
        $min_percent        = (float)Tools::getValue('min_percent');
        $max_percent        = (float)Tools::getValue('max_percent');
        $id_attribute       = 0;
        $img_name           = (string)$_FILES['giftimage']['name'];
        $reduction_currency = (int)Tools::getValue('reduction_currency');
        $price              = 0.0;
        $id_tax_rules_group = (int)Tools::getValue('id_tax_rules_group');

        //** old values
        $gid = (int)Tools::getValue('gid');

        $g_card = new Gift();
        if ($gid) {
            $product = new Product($id_product, true);
        } else {
            $product = new Product();
        }

        //** setting value of gift card product
        if ($value_type == 'fixed') {
            $card_value = (float)$card_value;
            $price      = (float)$card_value;
        }
        if ($value_type == 'range') {
            $card_value = $min.','.$max;
        }

        //** Initializing gift card product
        $languages = Language::getLanguages(false);
        foreach ($languages as $language) {
            if (empty(Tools::getValue('card_name_'.$language['id_lang']))) {
                $this->errors[] = sprintf($this->l('Enter card name for language: %s'), Language::getIsoById($language['id_lang']));
            } elseif (!Validate::isCatalogName(Tools::getValue('card_name_'.$language['id_lang']))) {
                $this->errors[] = $this->l('Invalid card name in ').$language['name'];
            } else {
                $product->name[$language['id_lang']] = (string)Tools::getValue('card_name_'.$language['id_lang']);
                $product->link_rewrite[$language['id_lang']] = Tools::str2url((string)Tools::getValue('card_name_'.$language['id_lang']));
            }

            if (!Validate::isCleanHtml(Tools::getValue('product_description_'.$language['id_lang']))) {
                $this->errors[] = $this->l('Invalid description in ').$language['name'];
            } else {
                $product->description[$language['id_lang']] = (string)Tools::getValue('product_description_'.$language['id_lang']);
            }
        }

        $product->quantity            = $card_qty;
        $product->active              = $active;
        $product->available_date      = $from;
        $product->date_add            = $now;
        $product->is_virtual          = true;
        $product->price               = $price;
        $product->id_category_default = (Configuration::get('PS_HOME_CATEGORY'))? (int)Configuration::get('PS_HOME_CATEGORY') : 2;
        $product->redirect_type       = '404';
        $product->id_tax_rules_group  = $id_tax_rules_group;

        $categories = new Category($product->id_category_default, $id_lang);
        $product->category = $categories->name;

        if ($red_type == 'amount') {
            $reduction_amount   = $card_value;
            $reduction_tax      = (int)Tools::getValue('reduction_tax');
        } elseif ($red_type == 'percent') {
            if ($value_type == 'range') {
                $reduction_amount   = $min_percent.','.$max_percent;
            } elseif ($value_type == 'dropdown') {
                $val1 = explode(',', $card_value);
                $val2 = explode(',', Tools::getValue('reduction_percent_dropdown'));
                $val1 = count($val1);
                $val2 = count($val2);
                if ($val1 != $val2) {
                    $this->errors[] = $this->l('No.of values of card price does not match No of values discount type.');
                    $flag = 0;
                } else {
                    $reduction_amount   = Tools::getValue('reduction_percent_dropdown');
                }
            } elseif ($value_type == 'fixed') {
                $reduction_amount = (int)Tools::getValue('reduction_percent_fixed');
            }

            $reduction_tax  = 0;
        }

        //** setting default voucher code length
        if (empty($length)) {
            $length = 14;
        }

        //** checking field values and displaying error messages respectively
        if (empty($card_qty) || $card_qty < 1 || !Validate::isInt($card_qty))
        {
            $this->errors[] = $this->l('Invalid Card quantity');
            $flag = 0;
        }
        if ($length < 4 || $length > 30 || !Validate::isInt($length))
        {
            $this->errors[] = $this->l('Invalid Code length');
            $flag = 0;
        }
        if (empty($from) || empty($to) || $from > $now || $to < $now || $from == $to)
        {
            $this->errors[] = $this->l('Invalid Validation date');
            $flag = 0;
        }
        if (($red_type == 'amount' || $red_type == 'percent') && empty($reduction_amount))
        {
            $this->errors[] = $this->l('Invalid discount amount/percentage');
            $flag = 0;
        }
        if (($red_type == 'amount' || $red_type == 'percent') && ($apply_discount_to == 'specific' && empty($reduction_product)))
        {
            $this->errors[] = $this->l('Please specificy a discount product');
            $flag = 0;
        }
        if ((($value_type == 'dropdown' || $value_type == 'fixed' || $value_type == 'fixed') && empty($card_value)))
        {
            $this->errors[] = $this->l('Invalid Gift card price');
            $flag = 0;
        }
        if (($value_type == 'range') && (empty($min) || empty($max) || $min < 1 || $max < 1 || $min >= $max))
        {
            $this->errors[] = $this->l('Invalid Gift card price');
            $flag = 0;
        }
        if (($value_type == 'range' && $red_type == 'percent') && (empty($min_percent) || empty($max_percent) || $min_percent < 1 || $max_percent < 1 || $min_percent >= $max_percent))
        {
            $this->errors[] = $this->l('Invalid range discount percent');
            $flag = 0;
        }

        if ($flag == 1 && count($this->errors) <= 0) {
            if ($gid) {
                if (!empty($img_name) && $img_name != null) {
                    $this->setImage($id_product, $product->link_rewrite);
                }

                Db::getInstance()->delete('product_shop', 'id_product = 0');
                $g_card->updateGiftCard($gid, $id_product, $card_name, $card_qty, $to, $from, $active, $length, $card_value, $value_type, $free_ship, $reduction_product, $red_type, $reduction_amount, $reduction_tax, $reduction_currency, $vcode_type);
                $g_card->setProductPrice($id_product, $price, $card_name, $id_lang);
                $g_card->updateProductQty($id_product, $card_qty);
                if ($product->update()) {
                    $this->msg = 3;
                }
            } else {
                $product->add();
                $g_card->setCategory($product->id);
                $this->setImage($product->id, $product->link_rewrite);
                $g_card->insertGiftCard($product->id, $card_name, $card_qty, $to, $from, $active, $length, $card_value, $value_type, $free_ship, $reduction_product, $red_type, $reduction_amount, $reduction_tax, $id_attribute, $reduction_currency, $vcode_type);
                StockAvailable::setQuantity($product->id, $id_attribute, $card_qty);
                $this->msg = 1;
            }

            if (Shop::isFeatureActive()) {
                Gift::removeAssocShops($product->id);
                if ($shops = Tools::getValue('checkBoxShopAsso_gift_card')) {
                    foreach ($shops as $shop) {
                        Gift::updateGiftShops($product->id, (int)$shop, $product->id_category_default, $product->id_tax_rules_group, $product->active, $price);
                    }
                }
            }
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminGift').'&msg='.$this->msg);
        } else {
            return $this->errors;
        }
    }

    public function setImage($id_product, $legend)
    {
        $image = new Image();
        $image->id_product = (int)$id_product;
        $image->position = Image::getHighestPosition($id_product) + 1;
        Image::deleteCover((int)$id_product);
        $image->cover = true;

        $languages = Language::getLanguages();
        foreach ($languages as $language) {
            $image->legend[$language['id_lang']] = $legend[$language['id_lang']];
        }

        $image->id_image = $image->id;
        $image->add();
        $tmp_name = tempnam(_PS_PROD_IMG_DIR_, 'PS');
        move_uploaded_file($_FILES['giftimage']['tmp_name'], $tmp_name);

        $new_path = $image->getPathForCreation();
        ImageManager::resize($tmp_name, $new_path.'.'.$image->image_format);
        $images_types = ImageType::getImagesTypes('products');
        foreach ($images_types as $imageType) {
            ImageManager::resize($tmp_name, $new_path.'-'.Tools::stripslashes($imageType['name']).'.'.$image->image_format, $imageType['width'], $imageType['height'], $image->image_format);
        }
    }

    public function renderShops($id)
    {
        $this->fields_form = array(
            'form' => array(
                'id_form' => 'field_shops',
                'input' => array(
                    array(
                        'type' => 'shop',
                        'label' => $this->l('Shop association:'),
                        'name' => 'checkBoxShopAsso',
                    ),
                )
            )
        );
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $this->fields_form = array();
        $helper->id = (int)$id;
        $helper->identifier = $this->identifier;
        $helper->tpl_vars = array_merge(array(
                //'fields_value' => $fields_value,
                'languages' => $this->getLanguages(),
                'id_language' => $this->context->language->id
            ));
        return $helper->renderAssoShop();
    }

    public function ajaxProcess()
    {
        if (Tools::isSubmit('reductionProductFilter')) {
            $products = Product::searchByName($this->context->language->id, trim(Tools::getValue('q')));
            die(Tools::jsonEncode($products));
        }
    }

    public function setMedia()
    {
        parent::setMedia();
        $this->addjQueryPlugin(array('date'));
        $this->addJqueryUI(array(
                'ui.slider',
                'ui.datepicker'
            )
        );

        $this->addJS(array(
            _PS_JS_DIR_.'tiny_mce/tiny_mce.js',
            _PS_JS_DIR_.'admin/tinymce.inc.js',
            _PS_JS_DIR_.'admin/tinymce_loader.js',
            _PS_JS_DIR_.'admin.js',
            _PS_JS_DIR_.'admin/product.js',
            )
        );

        $this->addJS(array(_PS_JS_DIR_.'jquery/plugins/timepicker/jquery-ui-timepicker-addon.js'));
        $this->addCSS(array(_PS_JS_DIR_.'jquery/plugins/timepicker/jquery-ui-timepicker-addon.css'));
        $this->addCSS(array(_PS_JS_DIR_.'jquery/plugins/autocomplete/jquery.autocomplete.css'));
        $this->addJS(array(_PS_JS_DIR_.'jquery/plugins/autocomplete/jquery.autocomplete.js'));
    }
}
