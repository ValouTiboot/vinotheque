<?php
/**
 * NOTICE OF LICENSE.
 *
 * This source file is subject to a commercial license from Agence Malttt SAS
 * Use, copy, modification or distribution of this source file without written
 * license agreement from the Agence Malttt SAS is strictly forbidden.
 * INFORMATION SUR LA LICENCE D'UTILISATION
 * L'utilisation de ce fichier source est soumise a une licence commerciale
 * concedee par la societe Agence Malttt SAS
 * Toute utilisation, reproduction, modification ou distribution du present
 * fichier source sans contrat de licence ecrit de la part d'Agence Malttt SAS est expressement interdite.
 *
 * @author    Matthieu Deroubaix
 * @copyright Copyright (c) 2015-2016 Agence Malttt SAS - 90 Rue faubourg saint martin - 75010 Paris
 * @license   Commercial license
 * Support by mail  :  support@agence-malttt.fr
 * Phone : +33.972535133
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class AutoGooglePlace extends Module
{

    public function __construct()
    {
        $this->name = 'autogoogleplace';
        $this->tab = 'front_office_features';
        $this->bootstrap = true;
        $this->version = '1.1.1';
        $this->author = 'Agence Malttt';

        $this->lang = true;
        $this->need_instance = 0;
        $this->ps_version_compliancy['min'] = '1.5.2.0';
        $this->ps_version_compliancy['max'] = _PS_VERSION_;
        $this->module_key = 'a10a69130ef93bcf7d1be6f3e11a4a42';

        $this->displayName = $this->l('Google Address Suggest');
        $this->description = $this->l('Give users an automatic suggestion and completion when they type their address !');
        parent::__construct();
    }

    public function install()
    {
        if (parent::install() && $this->registerHook('displayHeader')) {
            return true;
        } else {
            return false;
        }
    }

    public function uninstall()
    {
        return parent::uninstall();
    }

    public function getContent()
    {

        $return = "";
        $results = array();

        if (Tools::isSubmit('SubmitAutogoogleplace')) {
            $meta = '';
            if (Tools::getValue('autogoogleplace_meta')) {
                $meta .= implode(', ', Tools::getValue('autogoogleplace_meta'));
            }
            $results[] = Configuration::updateValue('AUTOGOOGLEPLACE_ENABLED_LINKS', $meta);

            if (Tools::getValue('autogoogleplace_key')) {
                $results[] = Configuration::updateValue('AUTOGOOGLEPLACE_KEY', Tools::getValue('autogoogleplace_key'));
            }

            if (Tools::getValue('autogoogleplace_force_15')) {
                $results[] = Configuration::updateValue('AUTOGOOGLEPLACE_FORCE_15', Tools::getValue('autogoogleplace_force_15'));
            } else {
                // Store false
                $results[] = Configuration::updateValue('AUTOGOOGLEPLACE_FORCE_15', '0');
                
            }

        }

        $this->context->smarty->assign(
            array(
                'link_form' => './index.php?tab=AdminModules&configure=autogoogleplace&token='.Tools::getAdminTokenLite('AdminModules').'&tab_module='.$this->tab.'&module_name=autogoogleplace',
                'metas' => Meta::getMetasByIdLang((int) $this->context->cookie->id_lang),
                'included_metas' => explode(',', Configuration::get('AUTOGOOGLEPLACE_ENABLED_LINKS')),
                'key' => Configuration::get('AUTOGOOGLEPLACE_KEY'),
                'results' => $results,
            )
        );

        return $this->display(__FILE__, 'views/templates/admin/configuration.tpl');
    }

    public function hookDisplayHeader($params)
    {
        $metas = Configuration::get('AUTOGOOGLEPLACE_ENABLED_LINKS');

        if (!empty($metas)) {
            $page_name = $this->getPageName();

            $req = 'SELECT page FROM `'._DB_PREFIX_.'meta` WHERE `id_meta` IN ( '.pSQL($metas).') AND page="'.pSQL($page_name).'"';
            $sql = Db::getInstance()->getRow($req);

            if (!empty($sql)) {
                $this->context->controller->addJS(_MODULE_DIR_.$this->name.'/views/js/'.$this->name.'.js');
                
                if( (bool) Configuration::get('AUTOGOOGLEPLACE_FORCE_15') == true || version_compare(_PS_VERSION_, "1.6.0.2", "<")) {
                    echo "<script type='text/javascript'>var mapsapikey = '".Configuration::get('AUTOGOOGLEPLACE_KEY')."';</script>";
                } else {   
                    Media::addJsDef(
                        array(
                            'mapsapikey' => Configuration::get('AUTOGOOGLEPLACE_KEY')
                        )
                    );
                }

            }
        }
    }

    public function getPageName()
    {
        $context = Context::getContext();
        $smarty = $context->smarty;

        if (!empty($smarty->tpl_vars['page_name']->value)) {
            $page_name = $smarty->tpl_vars['page_name']->value;
        } elseif (!empty($this->page_name)) {
            $page_name = $this->page_name;
        } elseif (!empty($this->php_self)) {
            $page_name = $this->php_self;
        } elseif (preg_match('#^'.preg_quote($context->shop->physical_uri, '#').'modules/([a-zA-Z0-9_-]+?)/(.*)$#', $_SERVER['REQUEST_URI'], $m)) {
            $page_name = 'module-'.$m[1].'-'.str_replace(array('.php', '/'), array('', '-'), $m[2]);
        } else {
            $page_name = Dispatcher::getInstance()->getController();
            $page_name = (preg_match('/^[0-9]/', $page_name) ? 'page_'.$page_name : $page_name);
        }

        return $page_name;
    }
}
