<?php

class AdvansedwishlistOverride extends Advansedwishlist
{
    public function hookDisplayProductListFunctionalButtons($params)
    {
        $cookie = $params['cookie'];

        $this->smarty->assign(array(
            'id_product' => $params['product']['id_product'],
            'minimal_quantity' => $params['product']['minimal_quantity'],
            'cache_default_attribute' => $params['product']['cache_default_attribute'],
            'logged' => $this->context->customer->isLogged(true),
        ));

        if (isset($cookie->id_customer)) {
        	$default_list = Ws_WishList::getDefault($cookie->id_customer);
        	$id_default_wishlist = $default_list[0]['id_wishlist'];
        	
            $this->smarty->assign(array(
                'wishlists' => Ws_WishList::getByIdCustomer($cookie->id_customer),
                'issetProduct' => Ws_WishList::issetProduct($id_default_wishlist, $params['product']['id_product']),
                'static_token' => Tools::getToken(false),
                'id_default_wishlist' => $id_default_wishlist,
            ));
        }

        return $this->display(__FILE__, 'buttonwishlist.tpl');
    }
}
