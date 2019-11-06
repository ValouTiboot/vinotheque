<?php


class Ets_megamenuOverride extends Ets_megamenu
{
	public function hookDisplayHeader()
    {
        $this->addGoogleFonts();
        $this->context->controller->addCSS($this->_path.'views/css/font-awesome.css');
        if($this->is17)
        {
            $this->addCss17('megamenu','main');
            $this->addCss17('fix17','fix17');
        }
        else
        {
            $this->context->controller->addCSS($this->_path.'views/css/megamenu.css');
            $this->context->controller->addCSS($this->_path.'views/css/fix16.css');
        }
        $this->context->controller->addCSS($this->_path.'views/css/animate.css');
        $this->context->controller->addJS($this->_path.'views/js/megamenu.js');
        $this->context->controller->addJS($this->_path.'views/js/jquery.countdown.min.js');
        $this->context->controller->addJS($this->_path.'views/js/clock.js');
        Media::addJsDef([
        	'mm_vp_link' => $this->context->link->getCMSLink(new CMS(7)),
        	'mm_customer_logged' => $this->context->customer->isLogged(),
        ]);
        $config = new MM_Config();
        $this->context->smarty->assign(array(
            'mm_config' => $config->getConfig(),
            'ETS_MM_ACTIVE_BG_GRAY' => Configuration::get('ETS_MM_ACTIVE_BG_GRAY')
        ));
        if(Configuration::get('ETS_MM_CACHE_ENABLED'))
        {
            if(@file_exists(dirname(__FILE__).'/views/css/cache.css') || !@file_exists(dirname(__FILE__).'/views/css/cache.css') && @file_put_contents(dirname(__FILE__).'/views/css/cache.css',$this->getCSS()))
            {
                if($this->is17)
                    $this->addCss17('cache','cache');
                else
                    $this->context->controller->addCSS($this->_path.'views/css/cache.css');
            }
            else
                return $this->displayDynamicCss();
        }
        else
            return $this->displayDynamicCss();
    }
}