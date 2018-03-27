<?php

class Colissimo_simpliciteOverride extends Colissimo_simplicite 
{
	/**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        if (isset($this->context->controller->page_name) && $this->context->controller->page_name == "checkout") {
        	\Media::addJsDef(array('soCarrierId' => \Configuration::get('COLISSIMO_CARRIER_ID')));
            $this->context->controller->addJS($this->_path.'/views/js/front.js');
            $this->context->controller->addJqueryPlugin(array('fancybox'));
        } else {
            $this->context->controller->addJS($this->_path.'/views/js/redirect.js');
        }
        $this->context->controller->addCSS($this->_path.'/views/css/front.css');
    }
}
