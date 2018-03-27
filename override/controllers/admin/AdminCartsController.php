<?php
/**
 *  Order Fees
 *
 *  @author    motionSeed <ecommerce@motionseed.com>
 *  @copyright 2017 motionSeed. All rights reserved.
 *  @license   https://www.motionseed.com/en/license-module.html
 */
class AdminCartsController extends AdminCartsControllerCore
{
    /*
    * module: orderfees
    * date: 2018-03-27 19:30:03
    * version: 1.8.9
    */
    public function setHelperDisplay(Helper $helper)
    {
        if (isset($this->tpl_view_vars['cart'])) {
            Hook::exec('actionAdminCartsControllerHelperDisplay', array(
                'controller' => &$this,
                'helper' => &$helper
            ));
        }
        
        parent::setHelperDisplay($helper);
    }
}
