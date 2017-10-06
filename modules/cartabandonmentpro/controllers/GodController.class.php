<?php
/**
* 2007-2017 PrestaShop
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
* @author    PrestaShop SA <contact@prestashop.com>
* @copyright 2007-2017 PrestaShop SA
* @license   http://addons.prestashop.com/en/content/12-terms-and-conditions-of-use
* International Registered Trademark & Property of PrestaShop SA
*/

class GodController
{
    public function __construct()
    {
        if (Tools::getValue('edit') == 1) {
            $reminderController = new ReminderController();
            $reminderController->edit();
            $this->redirect();
        }
        if (Tools::getValue('conf') == 1) {
            ConfController::setMaxDateReminder();
            $this->redirect();
        }
    }

    public static function getTemplate()
    {
        if (self::isDebug()) {
            return 'views/templates/admin/debug.tpl';
        } else {
            return 'views/templates/admin/configuration.tpl';
        }
    }
    private static function isFirstTime()
    {
        return false;
    }

    private static function isDebug()
    {
        return false;
    }
}
