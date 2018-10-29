<?php

class AutoGooglePlaceOverride extends AutoGooglePlace
{
	public function hookDisplayHeader($params)
    {
        $metas = Configuration::get('AUTOGOOGLEPLACE_ENABLED_LINKS');

        if (!empty($metas)) 
        {
            $page_name = $this->getPageName();

            $req = 'SELECT page FROM `'._DB_PREFIX_.'meta` WHERE `id_meta` IN ( '.pSQL($metas).') AND page="'.pSQL($page_name).'"';
            $sql = Db::getInstance()->getRow($req);

            if (!empty($sql)) 
            {
                $this->context->controller->addJS(_MODULE_DIR_.$this->name.'/views/js/'.$this->name.'.js');

                // if ($page_name == 'order' && !Tools::getIsset('newAddress'))
                //     return;
                
                if( (bool) Configuration::get('AUTOGOOGLEPLACE_FORCE_15') == true || version_compare(_PS_VERSION_, "1.6.0.2", "<"))
                    echo "<script type='text/javascript'>var mapsapikey = '".Configuration::get('AUTOGOOGLEPLACE_KEY')."';</script>";
                else
                    Media::addJsDef(array('mapsapikey' => Configuration::get('AUTOGOOGLEPLACE_KEY')));
            }
        }
    }
}