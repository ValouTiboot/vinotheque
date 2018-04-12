<?php

class AdminTranslationsController extends AdminTranslationsControllerCore
{
	/**
     * Get all informations on : languages, theme and the translation type.
     */
    public function getInformations()
    {
        // Get all Languages
        $this->languages = Language::getLanguages(false);

        // Get all iso_code of languages
        foreach ($this->languages as $language) {
            $this->all_iso_lang[] = $language['iso_code'];
        }

        // Get folder name of theme
        $theme = null;
        if (Tools::getIsset('selected-theme'))
            $theme = Tools::getValue('selected-theme');
        else if (Tools::getIsset('theme'))
            $theme = Tools::getValue('theme');

        if (!is_null($theme) && !is_array($theme)) {
            $theme_exists = $this->theme_exists($theme);
            if (!$theme_exists) {
                throw new PrestaShopException(sprintf($this->trans('Invalid theme "%s"', array(), 'Admin.International.Notification'), Tools::safeOutput($theme)));
            }
            $this->theme_selected = Tools::safeOutput($theme);
        }
        
        // Set the path of selected theme
        if ($this->theme_selected) {
            define('_PS_THEME_SELECTED_DIR_', _PS_ROOT_DIR_.'/themes/'.$this->theme_selected.'/');
        } else {
            define('_PS_THEME_SELECTED_DIR_', '');
        }

        // Get type of translation
        if (($type = Tools::getValue('type')) && !is_array($type)) {
            $this->type_selected = strtolower(Tools::safeOutput($type));
        }

        // Get selected language
        if (Tools::getValue('lang') || Tools::getValue('iso_code')) {
            $iso_code = Tools::getValue('lang') ? Tools::getValue('lang') : Tools::getValue('iso_code');

            if (!Validate::isLangIsoCode($iso_code) || !in_array($iso_code, $this->all_iso_lang)) {
                throw new PrestaShopException(sprintf($this->trans('Invalid iso code "%s"', array(), 'Admin.International.Notification'), Tools::safeOutput($iso_code)));
            }

            $this->lang_selected = new Language((int)Language::getIdByIso($iso_code));
        } else {
            $this->lang_selected = new Language((int)Language::getIdByIso('en'));
        }

        // Get all information for translations
        $this->getTranslationsInformations();
    }
}