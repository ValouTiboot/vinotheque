<?php

/**
* 
*/
class ChronopostOverride extends Chronopost
{
	public function hookHeader($params)
    {
        // check if on right page

        $file = Tools::getValue('controller');
        if (!in_array($file, array('order-opc', 'order', 'orderopc'))) {
            return;
        }

        $module_uri = _MODULE_DIR_.$this->name;
        $this->context->controller->addCSS($module_uri.'/views/css/chronorelais.css', 'all');
        $this->context->controller->addCSS($module_uri.'/views/css/chronordv.css', 'all');
        $this->context->controller->addJS($module_uri.'/views/js/chronorelais.js');
        $this->context->controller->addJS($module_uri.'/views/js/chronordv.js');
        
        if (\Tools::getIsset('newAddress'))
            return;
        return '<script async defer src="https://maps.googleapis.com/maps/api/js?key='. Configuration::get('CHRONOPOST_MAP_APIKEY') .'"></script>';
    }
}