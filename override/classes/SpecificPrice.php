<?php

class SpecificPrice extends SpecificPriceCore
{
    public $id_specific_price_dubos;

    public function __construct()
    {
        SpecificPrice::$definition['fields']['id_specific_price_dubos'] = array('type' => self::TYPE_STRING, 'validate' => 'isString');

        parent::__construct();
    }

}
