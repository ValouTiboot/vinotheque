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
