/**
 * 2011 - 2017 StorePrestaModules SPM LLC.
 *
 * MODULE blockreviews
 *
 * @author    SPM <spm.presto@gmail.com>
 * @copyright Copyright (c) permanent, SPM
 * @license   Addons PrestaShop license limitation
 * @version   1.6.6
 * @link      http://addons.prestashop.com/en/2_community-developer?contributor=790166
 *
 * NOTICE OF LICENSE
 *
 * Don't use this module on several shops. The license provided by PrestaShop Addons
 * for all its modules is valid only once for a single shop.
 */

$(document).ready(function(){

if ($('.owl_blockreviews_reviews_type_carousel ul').length > 0) {


    if ($('.owl_blockreviews_reviews_type_carousel ul').length > 0) {



        if (typeof $('.owl_blockreviews_reviews_type_carousel ul').owlCarousel === 'function') {


            $('.owl_blockreviews_reviews_type_carousel ul').owlCarousel({
                items: 1,

                loop: true,
                responsive: true,
                nav: true,
                navRewind: false,
                margin: 20,
                dots: true,
                navText: [,],

                lazyLoad: true,
                lazyFollow: true,
                lazyEffect: "fade",
            });
        }
    }

}



    if ($('.owl_blockreviews_home_reviews_type_carousel ul').length > 0) {

        if ($('.owl_blockreviews_home_reviews_type_carousel ul').length > 0) {

            if (typeof $('.owl_blockreviews_home_reviews_type_carousel ul').owlCarousel === 'function') {

                $('.owl_blockreviews_home_reviews_type_carousel ul').owlCarousel({
                    items: blockreviews_number_home_reviews_slider,

                    loop: true,
                    responsive: true,
                    nav: true,
                    navRewind: false,
                    margin: 20,
                    dots: true,

                    lazyLoad: true,
                    lazyFollow: true,
                    lazyEffect: "fade",
                });

            }
        }

    }

});