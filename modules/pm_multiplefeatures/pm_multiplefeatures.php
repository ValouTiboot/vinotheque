<?php
/**
 * Multiple Features
 *
 * @author    Presta-Module.com <support@presta-module.com> - http://www.presta-module.com
 * @copyright Presta-Module 2017 - http://www.presta-module.com
 * @license   Commercial
 * @version   1.4.0
 *
 *           ____     __  __
 *          |  _ \   |  \/  |
 *          | |_) |  | |\/| |
 *          |  __/   | |  | |
 *          |_|      |_|  |_|
 */

if (!defined('_PS_VERSION_')) {
    exit;
}
class PM_MultipleFeatures extends Module
{
    public static $_module_prefix = 'MF';
    public $_errors = array();
    protected $submitted_tabs;
    protected $_copyright_link = array(
        'link'    => '',
        'img'    => '//www.presta-module.com/img/logo-module.JPG'
    );
    protected $_support_link = false;
    protected $_getting_started = false;
    protected $defaultConfiguration = array(
        'featureSeparator' => ', ',
    );
    public function __construct()
    {
        $this->name = 'pm_multiplefeatures';
        $this->tab = 'front_office_features';
        $this->version = '1.4.0';
        $this->author = 'Presta-Module';
        $this->module_key = 'ba224a2fe8a2bd0c86589860fab0decb';
        $this->need_instance = 0;
        $this->bootstrap = true;
        $this->ps_versions_compliancy = array('min' => '1.5.0.0', 'max' => '1.7.99.99');
        parent::__construct();
        $this->displayName = 'Multiple Features';
        if ($this->onBackOffice()) {
            $this->description = $this->l('Allow to define multiple features values per features');
            $doc_url_tab = array();
            $doc_url_tab['fr'] = '#/fr/multiple-features/';
            $doc_url_tab['en'] = '#/en/multiple-features-2/';
            $doc_url_tab['es'] = '#/es/multiple-features-3/';
            $doc_url = $doc_url_tab['en'];
            if ($this->context->language->iso_code == 'fr') {
                $doc_url = $doc_url_tab['fr'];
            }
            $this->_support_link = array(
                
                array('link' => 'http://addons.prestashop.com/contact-community.php?id_product=6356', 'target' => '_blank', 'label' => $this->l('Support contact')),
            );
            $oldModuleVersion = Configuration::get('PM_'.self::$_module_prefix.'_LAST_VERSION', false);
            if ($oldModuleVersion != false && version_compare($oldModuleVersion, '1.3.0', '<=') || $oldModuleVersion != false && $oldModuleVersion != $this->version) {
                $this->installDatabase();
            }
            Configuration::updateValue('PM_'.self::$_module_prefix.'_LAST_VERSION', $this->version);
        }
    }
    private function installDatabase()
    {
        $result = Db::getInstance()->ExecuteS('SHOW INDEX FROM `' . _DB_PREFIX_ . 'feature_product` WHERE `Key_name` = "PRIMARY"');
        if ($result) {
            Db::getInstance()->Execute('ALTER TABLE `' . _DB_PREFIX_ . 'feature_product` DROP PRIMARY KEY');
        }
        $result = Db::getInstance()->ExecuteS('SHOW INDEX FROM `' . _DB_PREFIX_ . 'feature_product` WHERE `Key_name` = "mf_feature_product"');
        if (!$result || !Db::getInstance()->numRows()) {
            Db::getInstance()->Execute('ALTER TABLE  `' . _DB_PREFIX_ . 'feature_product` ADD INDEX `mf_feature_product` (`id_feature`, `id_product`)');
        }
        $result = Db::getInstance()->ExecuteS('SHOW INDEX FROM `' . _DB_PREFIX_ . 'feature_product` WHERE `Key_name` = "mf_unique"');
        if (!$result || !Db::getInstance()->numRows()) {
            $duplicateRows = Db::getInstance()->ExecuteS('SELECT *, COUNT(*) as count from `' . _DB_PREFIX_ . 'feature_product` GROUP BY id_feature, id_product, id_feature_value HAVING COUNT(*) > 1');
            if ($duplicateRows && self::isFilledArray($duplicateRows)) {
                foreach ($duplicateRows as $duplicateRow) {
                    Db::getInstance()->Execute('DELETE FROM `' . _DB_PREFIX_ . 'feature_product` WHERE `id_feature` = ' . (int)$duplicateRow['id_feature'] . ' AND `id_product` = ' . (int)$duplicateRow['id_product'] . '	AND `id_feature_value` = ' . (int)$duplicateRow['id_feature_value'] . ' LIMIT ' . ((int)$duplicateRow['count'] - 1));
                }
            }
            Db::getInstance()->Execute('ALTER TABLE  `' . _DB_PREFIX_ . 'feature_product` ADD UNIQUE INDEX `mf_unique` (`id_feature`, `id_product`, `id_feature_value`)');
        }
        $this->columnExists('feature_product', 'position', true, 'tinyint(3) unsigned NOT NULL DEFAULT "0"', 'id_feature_value');
        $result = Db::getInstance()->ExecuteS('SHOW INDEX FROM `' . _DB_PREFIX_ . 'feature_product` WHERE `Key_name` = "position"');
        if (!$result || !Db::getInstance()->numRows()) {
            Db::getInstance()->Execute('ALTER TABLE  `' . _DB_PREFIX_ . 'feature_product` ADD INDEX `position` (`position`)');
        }
    }
    private function columnExists($table, $column, $createIfNotExist = false, $type = false, $insertAfter = false)
    {
        $resultset = Db::getInstance()->ExecuteS("SHOW COLUMNS FROM `" . _DB_PREFIX_ . $table . "`", true, false);
        foreach ($resultset as $row) {
            if ($row['Field'] == $column) {
                return true;
            }
        }
        if ($createIfNotExist && Db::getInstance()->Execute('ALTER TABLE `' . _DB_PREFIX_ . $table . '` ADD `' . $column . '` ' . $type . ' ' . ($insertAfter ? ' AFTER `' . $insertAfter . '`' : '') . '')) {
            return true;
        }
        return false;
    }
    public function install()
    {
        $this->installDatabase();
        return (
            parent::install()
            && $this->registerHook('backOfficeTop')
            && $this->registerHook('backOfficeHeader')
            && (
                (version_compare(_PS_VERSION_, '1.7.0.0', '>=') && $this->registerHook('actionGetProductPropertiesBefore')&& $this->registerHook('actionGetProductPropertiesAfter') && $this->registerHook('actionProductUpdate'))
                || (version_compare(_PS_VERSION_, '1.5.0.0', '>=') && version_compare(_PS_VERSION_, '1.7.0.0', '<') && $this->registerHook('displayOverrideTemplate'))
            ));
    }
    private function checkFeatures($languages, $feature_id)
    {
        $rules = call_user_func(array('FeatureValue', 'getValidationRules'), 'FeatureValue');
        $feature = Feature::getFeature(Configuration::get('PS_LANG_DEFAULT'), $feature_id);
        $val = 0;
        foreach ($languages as $language) {
            if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
                $customFeatureKey = 'pm_multiplefeatures_feature_'.$feature_id.'_custom_value_'.$language['id_lang'];
            } else {
                $customFeatureKey = 'custom_'.$feature_id.'_'.$language['id_lang'];
            }
            if ($val = Tools::getValue($customFeatureKey)) {
                $currentLanguage = new Language($language['id_lang']);
                if (Tools::strlen($val) > $rules['sizeLang']['value']) {
                    $this->_errors[] = Tools::displayError('name for feature').' <b>'.$feature['name'].'</b> '.Tools::displayError('is too long in').' '.$currentLanguage->name;
                } elseif (!call_user_func(array('Validate', $rules['validateLang']['value']), $val)) {
                    $this->_errors[] = Tools::displayError('Valid name required for feature.').' <b>'.$feature['name'].'</b> '.Tools::displayError('in').' '.$currentLanguage->name;
                }
                if (sizeof($this->_errors)) {
                    return (0);
                }
                if ($language['id_lang'] == Configuration::get('PS_LANG_DEFAULT')) {
                    return ($val);
                }
            }
        }
        return (0);
    }
    public static function getProductFeaturesStatic($id_product, $id_lang, $custom = true)
    {
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
		SELECT fp.id_feature, fp.id_product, fp.id_feature_value, vl.value, v.custom
		FROM `'._DB_PREFIX_.'feature_product` fp
		LEFT JOIN `'._DB_PREFIX_.'feature_value` v ON (fp.`id_feature_value` = v.`id_feature_value`)
		LEFT JOIN `'._DB_PREFIX_.'feature_value_lang` vl ON (v.`id_feature_value` = vl.`id_feature_value` AND vl.`id_lang` = '.(int)$id_lang.')
		WHERE fp.`id_product` = '.(int)$id_product
        . (!$custom ? ' AND v.`custom` = 0' : '')
        . ' ORDER BY fp.`position` ASC');
    }
    public function getFrontFeatures($id_product, $separator = null, $id_feature = null)
    {
        $id_lang = (int)Context::getContext()->cookie->id_lang;
        if ($separator == null) {
            $config = $this->getModuleConfiguration();
            $separator = $config['featureSeparator'];
        }
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
		SELECT fp.id_feature, vl.value, fl.name
		FROM `'._DB_PREFIX_.'feature_product` fp
		LEFT JOIN `'._DB_PREFIX_.'feature_value` v ON (fp.`id_feature_value` = v.`id_feature_value`)
		LEFT JOIN `'._DB_PREFIX_.'feature_value_lang` vl ON (v.`id_feature_value` = vl.`id_feature_value` AND vl.`id_lang` = '.(int)$id_lang.')
		LEFT JOIN `'._DB_PREFIX_.'feature` f ON (f.`id_feature` = v.`id_feature`)
		'.(Shop::isFeatureActive() && Shop::getContext() == Shop::CONTEXT_SHOP ? Shop::addSqlAssociation('feature', 'f') : '').'
		LEFT JOIN `'._DB_PREFIX_.'feature_lang` fl ON (fl.`id_feature` = f.`id_feature` AND fl.`id_lang` = '.(int)$id_lang.')
		WHERE fp.`id_product` = '.(int)$id_product
        . ($id_feature != null && $id_feature ? ' AND f.`id_feature` = '.(int)$id_feature : '')
        . ' ORDER BY f.`position` ASC, fp.`position` ASC');
        $return = array();
        if ($result && is_array($result) && sizeof($result)) {
            foreach ($result as $row) {
                $return[$row['id_feature']]['values'][] = $row['value'];
                $return[$row['id_feature']]['name'] = $row['name'];
                $return[$row['id_feature']]['id_feature'] = $row['id_feature'];
            }
            foreach ($return as $key => $row) {
                $return[$key]['value'] = implode($separator, $row['values']);
            }
        }
        if ($id_feature != null && $id_feature && isset($return[$id_feature])) {
            return $return[$id_feature]['value'];
        } else {
            return $return;
        }
    }
    protected function getCurrentProductId()
    {
        if (is_object($this->context->controller) && preg_match('/^ProductController/i', get_class($this->context->controller))) {
            if (method_exists($this->context->controller, 'getProduct')) {
                $product = $this->context->controller->getProduct();
                if (!Validate::isLoadedObject($product)) {
                    $id_product = (int)Tools::getValue('id_product');
                    if (Validate::isUnsignedId($id_product) && $id_product > 0) {
                        return $id_product;
                    }
                }
            } else {
                $id_product = (int)Tools::getValue('id_product');
                if (Validate::isUnsignedId($id_product) && $id_product > 0) {
                    return $id_product;
                }
            }
            if (Validate::isLoadedObject($product)) {
                return $product->id;
            }
        }
        return false;
    }
    public function hookActionGetProductPropertiesBefore($params)
    {
        if (version_compare(_PS_VERSION_, '1.7.0.0', '>=') && version_compare(_PS_VERSION_, '1.7.1.0', '<')) {
            if (MFProduct::$preventHookLoop) {
                return;
            }
            $id_product = $this->getCurrentProductId();
            if ($id_product && !empty($params['product']) && !empty($params['product']['id_product']) && $params['product']['id_product'] == $id_product) {
                MFProduct::setProductProperties($params['id_lang'], $params['product'], $this->getFrontFeatures($params['product']['id_product']));
            }
        }
    }
    public function hookActionGetProductPropertiesAfter($params)
    {
        if (version_compare(_PS_VERSION_, '1.7.1.0', '>=')) {
            $params['product']['features'] = $this->getFrontFeatures($params['product']['id_product']);
        }
    }
    public function hookDisplayOverrideTemplate($params)
    {
        if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
            return;
        }
        $id_product = $this->getCurrentProductId();
        if ($id_product !== false) {
            $this->context->smarty->assign('features', $this->getFrontFeatures($id_product));
        } elseif (isset($this->context->controller) && is_object($this->context->controller) && isset($this->context->controller->php_self) && $this->context->controller->php_self == 'products-comparison') {
            $ids = null;
            if (($product_list = Tools::getValue('compare_product_list')) && ($postProducts = (isset($product_list) ? rtrim($product_list, '|') : ''))) {
                $ids = array_unique(explode('|', $postProducts));
            } elseif (isset($this->context->cookie->id_compare)) {
                $ids = CompareProduct::getCompareProducts($this->context->cookie->id_compare);
            }
            $listFeatures = array();
            foreach ($ids as $id) {
                $curProduct = new Product((int)$id, true, $this->context->language->id);
                if (Validate::isLoadedObject($curProduct) && $curProduct->active && $curProduct->isAssociatedToShop()) {
                    foreach ($curProduct->getFrontFeatures($this->context->language->id) as $feature) {
                        $listFeatures[$curProduct->id][$feature['id_feature']] = $this->getFrontFeatures($curProduct->id, null, $feature['id_feature']);
                    }
                }
            }
            if (sizeof($listFeatures)) {
                $this->context->smarty->assign(array(
                    'product_features' => $listFeatures,
                ));
            }
        }
    }
    protected function renderForm()
    {
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitModuleConfiguration';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
        .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getModuleConfiguration(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
            );
        return $helper->generateForm(array($this->getConfigForm()));
    }
    public function getContent()
    {
        if (((bool)Tools::isSubmit('submitModuleConfiguration')) == true) {
            $this->postProcess();
            if (empty($this->context->controller->errors)) {
                $this->context->controller->confirmations[] = $this->l('Configuration has successfully been saved');
            }
        }
        $this->context->smarty->assign(array(
            'module_dir' => $this->_path,
        ));
        return $this->showRating(true) . $this->renderForm() . $this->displaySupport();
    }
    private function postProcess()
    {
        if (Tools::getIsset('submitModuleConfiguration') && Tools::isSubmit('submitModuleConfiguration')) {
            $config = $this->getModuleConfiguration();
            foreach (array('featureSeparator') as $configKey) {
                $config[$configKey] = Tools::getValue($configKey);
            }
            $this->setModuleConfiguration($config);
        }
    }
    public function getConfigForm()
    {
        return array(
            'form' => array(
                'input' => array(
                    array(
                        'type' => 'html',
                        'html_content' => '<h2>'. $this->l('Configuration') .'</h2>',
                        'name' => '',
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Features separator'),
                        'desc' => $this->l('Define here the character used to separate multiple features values'),
                        'name' => 'featureSeparator',
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }
    protected function displaySupport()
    {
        $pm_addons_products = $this->getAddonsModulesFromApi();
        $pm_products = $this->getPMModulesFromApi();
        if (!is_array($pm_addons_products)) {
            $pm_addons_products = array();
        }
        if (!is_array($pm_products)) {
            $pm_products = array();
        }
        $this->shuffleArray($pm_addons_products);
        if (is_array($pm_addons_products)) {
            if (!empty($pm_products['ignoreList']) && is_array($pm_products['ignoreList']) && sizeof($pm_products['ignoreList'])) {
                foreach ($pm_products['ignoreList'] as $ignoreId) {
                    if (isset($pm_addons_products[$ignoreId])) {
                        unset($pm_addons_products[$ignoreId]);
                    }
                }
            }
            $addonsList = $this->getPMAddons();
            if ($addonsList && is_array($addonsList) && sizeof($addonsList)) {
                foreach (array_keys($addonsList) as $moduleName) {
                    foreach ($pm_addons_products as $k => $pm_addons_product) {
                        if ($pm_addons_product['name'] == $moduleName) {
                            unset($pm_addons_products[$k]);
                            break;
                        }
                    }
                }
            }
        }
        $vars = array(
            'support_links' => (is_array($this->_support_link) && sizeof($this->_support_link) ? $this->_support_link : array()),
            'copyright_link' => (is_array($this->_copyright_link) && sizeof($this->_copyright_link) ? $this->_copyright_link : false),
            'pm_module_version' => $this->version,
            'pm_data' => $this->getPMdata(),
            'pm_products' => $pm_products,
            'pm_addons_products' => $pm_addons_products,
        );
        return $this->fetchTemplate('core/support.tpl', $vars);
    }
    protected function showRating($show = false)
    {
        $dismiss = (int)Configuration::getGlobalValue('PM_'.self::$_module_prefix .'_DISMISS_RATING');
        if ($show && $dismiss != 1 && $this->getNbDaysModuleUsage() >= 3) {
            return $this->fetchTemplate('core/rating.tpl');
        }
        return '';
    }
    protected function getAddonsModulesFromApi()
    {
        $modules = Configuration::get('PM_' . self::$_module_prefix  . '_AM');
        $modules_date = Configuration::get('PM_' . self::$_module_prefix  . '_AMD');
        if ($modules && strtotime('+2 day', $modules_date) > time()) {
            return Tools::jsonDecode($modules, true);
        }
        $jsonResponse = $this->doHttpRequest();
        if (empty($jsonResponse->products)) {
            return array();
        }
        $dataToStore = array();
        foreach ($jsonResponse->products as $addonsEntry) {
            $dataToStore[(int)$addonsEntry->id] = array(
                'name' => $addonsEntry->name,
                'displayName' => $addonsEntry->displayName,
                'url' => $addonsEntry->url,
                'compatibility' => $addonsEntry->compatibility,
                'version' => $addonsEntry->version,
                'description' => $addonsEntry->description,
            );
        }
        Configuration::updateValue('PM_' . self::$_module_prefix  . '_AM', Tools::jsonEncode($dataToStore));
        Configuration::updateValue('PM_' . self::$_module_prefix  . '_AMD', time());
        return Tools::jsonDecode(Configuration::get('PM_' . self::$_module_prefix  . '_AM'), true);
    }
    protected function getPMModulesFromApi()
    {
        $modules = Configuration::get('PM_' . self::$_module_prefix  . '_PMM');
        $modules_date = Configuration::get('PM_' . self::$_module_prefix  . '_PMMD');
        if ($modules && strtotime('+2 day', $modules_date) > time()) {
            return Tools::jsonDecode($modules, true);
        }
        $jsonResponse = $this->doHttpRequest(array('list' => $this->getPMAddons()), 'presta-module', 'api-addons');
        if (empty($jsonResponse)) {
            return array();
        }
        Configuration::updateValue('PM_' . self::$_module_prefix  . '_PMM', Tools::jsonEncode($jsonResponse));
        Configuration::updateValue('PM_' . self::$_module_prefix  . '_PMMD', time());
        return Tools::jsonDecode(Configuration::get('PM_' . self::$_module_prefix  . '_PMM'), true);
    }
    protected function getPMAddons()
    {
        $pmAddons = array();
        $result = Db::getInstance()->ExecuteS('SELECT DISTINCT name FROM '._DB_PREFIX_.'module WHERE name LIKE "pm_%"');
        if ($result && is_array($result) && sizeof($result)) {
            foreach ($result as $module) {
                $instance = Module::getInstanceByName($module['name']);
                if ($instance && isset($instance->version)) {
                    $pmAddons[$module['name']] = $instance->version;
                }
            }
        }
        return $pmAddons;
    }
    protected function shuffleArray(&$a)
    {
        if (is_array($a) && sizeof($a)) {
            $ks = array_keys($a);
            shuffle($ks);
            $new = array();
            foreach ($ks as $k) {
                $new[$k] = $a[$k];
            }
            $a = $new;
            return true;
        }
        return false;
    }
    protected function fetchTemplate($tpl, $customVars = array(), $configOptions = array())
    {
        $this->context->smarty->assign(array(
            'ps_major_version' => Tools::substr(str_replace('.', '', _PS_VERSION_), 0, 2),
            'module_name' => $this->name,
            'module_path' => $this->_path,
            'current_iso_lang' => $this->context->language->iso_code,
            'current_id_lang' => (int)$this->context->language->id,
            'options' => $configOptions,
        ));
        if (sizeof($customVars)) {
            $this->context->smarty->assign($customVars);
        }
        return $this->context->smarty->fetch(_PS_MODULE_DIR_ . $this->name . '/views/templates/admin/' . $tpl);
    }
    protected function doHttpRequest($data = array(), $c = 'prestashop', $s = 'api.addons')
    {
        $data = array_merge(array(
            'version' => _PS_VERSION_,
            'iso_lang' => Tools::strtolower($this->context->language->iso_code),
            'iso_code' => Tools::strtolower(Country::getIsoById(Configuration::get('PS_COUNTRY_DEFAULT'))),
            'module_key' => $this->module_key,
            'method' => 'contributor',
            'action' => 'all_products',
        ), $data);
        $postData = http_build_query($data);
        $context = stream_context_create(array(
            'http' => array(
                'method' => 'POST',
                'content' => $postData,
                'header' => 'Content-type: application/x-www-form-urlencoded',
                'timeout' => 15,
            )
        ));
        $response = Tools::file_get_contents('https://' . $s . '.' . $c . '.com', false, $context);
        if (empty($response)) {
            return false;
        }
        $responseToJson = Tools::jsonDecode($response);
        if (empty($responseToJson)) {
            return false;
        }
        return $responseToJson;
    }
    protected function isTabSubmitted($tab_name)
    {
        if (!is_array($this->submitted_tabs)) {
            $this->submitted_tabs = Tools::getValue('submitted_tabs');
        }
        if (is_array($this->submitted_tabs) && in_array($tab_name, $this->submitted_tabs)) {
            return true;
        }
        return false;
    }
    protected function deleteTabSubmitted($tab_name)
    {
        if (!is_array($this->submitted_tabs)) {
            $this->submitted_tabs = Tools::getValue('submitted_tabs');
        }
        if (is_array($this->submitted_tabs) && in_array($tab_name, $this->submitted_tabs)) {
            unset($_POST['submitted_tabs'][array_search($tab_name, $_POST['submitted_tabs'])]);
        }
    }
    private function _initBackOfficeAssets($idProduct)
    {
        $idLang = (int)$this->context->cookie->id_lang;
        $obj = new Product($idProduct);
        if (Validate::isLoadedObject($obj)) {
            $selectedFeaturesList = array();
            $selectedFeatures = self::getProductFeaturesStatic($obj->id, (int)$idLang, false);
            foreach ($selectedFeatures as $feature) {
                if (!isset($selectedFeaturesList[(int)$feature['id_feature']])) {
                    $selectedFeaturesList[(int)$feature['id_feature']] = array();
                }
                $selectedFeaturesList[(int)$feature['id_feature']][] = (int)$feature['id_feature_value'];
            }
            if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
                Media::addJsDef(
                    array(
                        'pm_FeatureList' => $selectedFeaturesList,
                        'pm_FeatureAvailableListTitle' => $this->l('Available features:'),
                        'pm_FeatureAvailableListSearchTitle' => $this->l('Available features (%nbResults% found):'),
                        'pm_FeatureSearchInputPlaceHolder' => $this->l('Search'),
                        'pm_FeatureSelectedListTitle' => $this->l('Selected features:'),
                        'pm_FeatureAddAllButtonLabel' => $this->l('Add all'),
                        'pm_FeatureRemoveAllButtonLabel' => $this->l('Remove all'),
                    )
                );
            } else {
                echo '
                <script type="text/javascript">
                    var pm_FeatureList = '. Tools::jsonEncode($selectedFeaturesList) .';
                    var pm_FeatureAvailableListTitle = '. Tools::jsonEncode($this->l('Available features:')) .';
                    var pm_FeatureAvailableListSearchTitle = '. Tools::jsonEncode($this->l('Available features (%nbResults% found):')) .';
                    var pm_FeatureSearchInputPlaceHolder = '. Tools::jsonEncode($this->l('Search')) .';
                    var pm_FeatureSelectedListTitle = '. Tools::jsonEncode($this->l('Selected features:')) .';
                    var pm_FeatureAddAllButtonLabel = '. Tools::jsonEncode($this->l('Add all')) .';
                    var pm_FeatureRemoveAllButtonLabel = '. Tools::jsonEncode($this->l('Remove all')) .';
    			</script>
    			';
            }
            if (version_compare(_PS_VERSION_, '1.5.0.0', '>=') && version_compare(_PS_VERSION_, '1.7.0.0', '<')) {
                echo '
				<script type="text/javascript">
					$(document).ready(function() {
						// Init select
						pmTransformSelect();
					});
				</script>
				';
            }
        }
    }
    private static $preventHookLoop = false;
    public function hookActionProductUpdate($params)
    {
        if (self::$preventHookLoop) {
            return;
        }
        if (version_compare(_PS_VERSION_, '1.7.0.0', '>=') && ($this->context->controller instanceof AdminProductsControllerCore || $this->context->controller instanceof AdminProductsController) && Tools::getValue('id_product')) {
            if (Validate::isLoadedObject($product = new Product((int)(Tools::getValue('id_product'))))) {
                $product->deleteFeatures();
                $languages = Language::getLanguages(false);
                $hasCustom = array();
                foreach ($_POST as $key => $val) {
                    if (preg_match('/^pm_multiplefeatures_feature_([0-9]+)_custom_value_([0-9]+)/i', $key, $match)) {
                        if ($match[2] == Configuration::get('PS_LANG_DEFAULT') && $val) {
                            $hasCustom[(int)$match[1]] = true;
                            if ($default_value = $this->checkFeatures($languages, $match[1])) {
                                $id_value = $product->addFeaturesToDB($match[1], 0, 1);
                                foreach ($languages as $language) {
                                    if ($cust = Tools::getValue('pm_multiplefeatures_feature_'.$match[1].'_custom_value_'.(int)$language['id_lang'])) {
                                        $product->addFeaturesCustomToDB($id_value, (int)$language['id_lang'], $cust);
                                    } else {
                                        $product->addFeaturesCustomToDB($id_value, (int)$language['id_lang'], $default_value);
                                    }
                                }
                            }
                        }
                    }
                }
                foreach ($_POST as $key => $val) {
                    if (preg_match('/^pm_multiplefeatures_feature_([0-9]+)_value/i', $key, $match)) {
                        if (empty($hasCustom[(int)$match[1]]) && preg_match('/^pm_multiplefeatures_feature/i', $key) && ((is_array($val) && sizeof($val)) || (!is_array($val) && $val))) {
                            if (is_array($val)) {
                                foreach ($val as $pos => $val2) {
                                    $id_feature = (int)$match[1];
                                    $id_feature_value = (int)$val2;
                                    if (empty($id_feature) || empty($id_feature_value)) {
                                        continue;
                                    }
                                    $row = array(
                                        'id_feature' => (int)$id_feature,
                                        'id_product' => (int)$product->id,
                                        'id_feature_value' => (int)$id_feature_value,
                                        'position' => (int)$pos,
                                    );
                                    Db::getInstance()->insert('feature_product', $row);
                                }
                            } else {
                                $product->addFeaturesToDB($match[1], $val);
                            }
                        }
                    }
                }
                self::$preventHookLoop = true;
                $product->save();
                self::$preventHookLoop = false;
            }
        }
    }
    public function hookBackOfficeHeader($params)
    {
        if (!$this->active) {
            return;
        }
        if (isset($this->context->controller->controller_name) && $this->context->controller->controller_name == 'AdminModules') {
            $this->context->controller->addCSS($this->_path . 'views/css/admin.css');
            if (version_compare(_PS_VERSION_, '1.6.0.0', '<')) {
                $this->context->controller->addCSS($this->_path . 'views/css/admin15.css');
            }
        }
        if (version_compare(_PS_VERSION_, '1.7.0.0', '>=') && isset($this->context->controller->controller_name) && $this->context->controller->controller_name == 'AdminProducts') {
            global $kernel;
            $request = $kernel->getContainer()->get('request');
            $idProduct = (int)$request->get('id');
            $this->context->controller->addJquery();
            $this->context->controller->addJqueryUI('ui.core');
            $this->context->controller->addJqueryUI('ui.widget');
            $this->context->controller->addJqueryUI('ui.mouse');
            $this->context->controller->addJqueryUI('ui.draggable');
            $this->context->controller->addJqueryUI('ui.sortable');
            $this->context->controller->addJqueryUI('ui.droppable');
            $this->context->controller->addCSS($this->_path . 'views/css/connected-list/connected-list.min.css', 'all');
            $this->context->controller->addJS($this->_path . 'views/js/connected-list/connected-list.min.js');
            $this->context->controller->addJS($this->_path . 'views/js/product-tab-17.js');
            $this->_initBackOfficeAssets($idProduct);
        } elseif (version_compare(_PS_VERSION_, '1.5.0.0', '>=') && (Tools::getValue('controller') == 'adminproducts' || Tools::getValue('controller') == 'AdminProducts') && Tools::getValue('id_product')) {
            $this->context->controller->addJquery();
            $this->context->controller->addJqueryUI('ui.core');
            $this->context->controller->addJqueryUI('ui.widget');
            $this->context->controller->addJqueryUI('ui.mouse');
            $this->context->controller->addJqueryUI('ui.draggable');
            $this->context->controller->addJqueryUI('ui.sortable');
            $this->context->controller->addJqueryUI('ui.droppable');
            $this->context->controller->addCSS($this->_path . 'views/css/connected-list/connected-list.min.css', 'all');
            $this->context->controller->addJS($this->_path . 'views/js/connected-list/connected-list.min.js');
            $this->context->controller->addJS($this->_path . 'views/js/product-tab.js');
        }
        if (version_compare(_PS_VERSION_, '1.5.0.0', '>=') && ((Tools::getValue('controller') == 'adminproducts' || Tools::getValue('controller') == 'AdminProducts') && Tools::getValue('id_product') && (Tools::getIsset('updateproduct') || Tools::getIsset('addproduct')) && Tools::getIsset('ajax') && Tools::getValue('action') == 'Features')) {
            $this->_initBackOfficeAssets(Tools::getValue('id_product'));
        } elseif (version_compare(_PS_VERSION_, '1.5.0.0', '>=') && $this->isTabSubmitted('Features')) {
            if (Validate::isLoadedObject($product = new Product((int)(Tools::getValue('id_product'))))) {
                $product->deleteFeatures();
                $languages = Language::getLanguages(false);
                foreach ($_POST as $key => $val) {
                    if (preg_match('/^(?:feature|custom)_([0-9]+)_(value|[0-9]+)/i', $key, $match)) {
                        if (preg_match('/^feature/i', $key) && ((is_array($val) && sizeof($val)) || (!is_array($val) && $val))) {
                            if (is_array($val)) {
                                foreach ($val as $pos => $val2) {
                                    $id_feature = (int)$match[1];
                                    $id_feature_value = (int)$val2;
                                    if (empty($id_feature) || empty($id_feature_value)) {
                                        continue;
                                    }
                                    $row = array(
                                        'id_feature' => (int)$id_feature,
                                        'id_product' => (int)$product->id,
                                        'id_feature_value' => (int)$id_feature_value,
                                        'position' => (int)$pos,
                                    );
                                    Db::getInstance()->insert('feature_product', $row);
                                }
                                SpecificPriceRule::applyAllRules(array((int)$this->id));
                            } else {
                                $product->addFeaturesToDB($match[1], $val);
                            }
                        } elseif (preg_match('/^custom/i', $key) && $match[2] == Configuration::get('PS_LANG_DEFAULT') && $val) {
                            if ($default_value = $this->checkFeatures($languages, $match[1])) {
                                $id_value = $product->addFeaturesToDB($match[1], 0, 1);
                                foreach ($languages as $language) {
                                    if ($cust = Tools::getValue('custom_'.$match[1].'_'.(int)$language['id_lang'])) {
                                        $product->addFeaturesCustomToDB($id_value, (int)$language['id_lang'], $cust);
                                    } else {
                                        $product->addFeaturesCustomToDB($id_value, (int)$language['id_lang'], $default_value);
                                    }
                                }
                            }
                        }
                    }
                }
                $this->deleteTabSubmitted('Features');
            }
        } elseif (Tools::getValue('key_tab') == 'Features') {
            $_GET['key_tab_onload'] = 'Features';
            unset($_GET['key_tab']);
        }
    }
    public function hookBackOfficeTop($params)
    {
        if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
            return;
        }
        if ((Tools::getValue('tab') == 'AdminCatalog' && Tools::getValue('id_product') && (Tools::getIsset('updateproduct') || Tools::getIsset('addproduct')))
            || (Tools::getValue('controller') == 'adminproducts' || Tools::getValue('controller') == 'AdminProducts') && Tools::getValue('id_product') && (Tools::getIsset('updateproduct') || Tools::getIsset('addproduct')) && !Tools::getIsset('ajax')) {
            if (Tools::getValue('key_tab_onload') == 'Features') {
                return '
				<script type="text/javascript">
					$(window).load(function() {
						$(".productTabs #link-Features").trigger("click");
					});
				</script>
				';
            }
        }
    }
    public static function isFilledArray($array)
    {
        return ($array && is_array($array) && sizeof($array));
    }
    protected static function getDataSerialized($data, $type = 'base64')
    {
        if (is_array($data)) {
            return array_map($type . '_encode', array($data));
        } else {
            return current(array_map($type . '_encode', array($data)));
        }
    }
    protected static function getDataUnserialized($data, $type = 'base64')
    {
        if (is_array($data)) {
            return array_map($type . '_decode', array($data));
        } else {
            return current(array_map($type . '_decode', array($data)));
        }
    }
    
    private function getPMdata()
    {
        $param = array();
        $param[] = 'ver-'._PS_VERSION_;
        $param[] = 'current-'.$this->name;
        
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('SELECT DISTINCT name FROM '._DB_PREFIX_.'module WHERE name LIKE "pm_%"');
        if ($result && self::isFilledArray($result)) {
            foreach ($result as $module) {
                $instance = Module::getInstanceByName($module['name']);
                if ($instance && isset($instance->version)) {
                    $param[] = $module['name'].'-'.$instance->version;
                }
            }
        }
        return urlencode(self::getDataSerialized(implode('|', $param)));
    }
    private static function getNbDaysModuleUsage()
    {
        $sql = 'SELECT DATEDIFF(NOW(),date_add)
                FROM '._DB_PREFIX_.'configuration
                WHERE name = \''.pSQL('PM_'.self::$_module_prefix.'_LAST_VERSION').'\'
                ORDER BY date_add ASC';
        return (int)Db::getInstance()->getValue($sql);
    }
    protected function onBackOffice()
    {
        if (isset($this->context->cookie->id_employee) && Validate::isUnsignedId($this->context->cookie->id_employee)) {
            return true;
        }
        return false;
    }
    protected function getModuleConfiguration()
    {
        $conf = Configuration::get('PM_' . self::$_module_prefix . '_CONF');
        if (!empty($conf)) {
            return Tools::jsonDecode($conf, true);
        } else {
            return $this->defaultConfiguration;
        }
    }
    public static function getModuleConfigurationStatic()
    {
        $conf = Configuration::get('PM_' . self::$_module_prefix . '_CONF');
        if (!empty($conf)) {
            return Tools::jsonDecode($conf, true);
        } else {
            return array();
        }
    }
    protected function setModuleConfiguration($newConf)
    {
        Configuration::updateValue('PM_' . self::$_module_prefix . '_CONF', Tools::jsonEncode($newConf));
    }
    protected function setDefaultConfiguration()
    {
        if (!is_array($this->getModuleConfiguration()) || !sizeof($this->getModuleConfiguration())) {
            Configuration::updateValue('PM_' . self::$_module_prefix . '_CONF', Tools::jsonEncode($this->defaultConfiguration));
        }
        return true;
    }
}
if (version_compare(_PS_VERSION_, '1.7.0.0', '>=') && version_compare(_PS_VERSION_, '1.7.1.0', '<')) {
    class MFProduct extends Product
    {
        public static $preventHookLoop = false;
        public static function setProductProperties($id_lang, $row, $features)
        {
            self::$preventHookLoop = true;
            $row = parent::getProductProperties($id_lang, $row);
            self::$preventHookLoop = false;
            $id_product_attribute = $row['id_product_attribute'] = (!empty($row['id_product_attribute']) ? (int)$row['id_product_attribute'] : null);
            $row['allow_oosp'] = Product::isAvailableWhenOutOfStock($row['out_of_stock']);
            if (Combination::isFeatureActive() && $id_product_attribute === null
                && ((isset($row['cache_default_attribute']) && ($ipa_default = $row['cache_default_attribute']) !== null)
                    || ($ipa_default = Product::getDefaultAttribute($row['id_product'], !$row['allow_oosp'])))) {
                $id_product_attribute = $row['id_product_attribute'] = $ipa_default;
            }
            if (!Combination::isFeatureActive() || !isset($row['id_product_attribute'])) {
                $id_product_attribute = $row['id_product_attribute'] = 0;
            }
            $usetax = !Tax::excludeTaxeOption();
            $cache_key = $row['id_product'].'-'.$id_product_attribute.'-'.$id_lang.'-'.(int)$usetax;
            if (isset($row['id_product_pack'])) {
                $cache_key .= '-pack'.$row['id_product_pack'];
            }
            $row['features'] = $features;
            self::$producPropertiesCache[$cache_key] = $row;
        }
    }
}
