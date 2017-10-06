{*
* 2007-2017 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2017 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<!--<script type="text/javascript" src="{$module_dir|escape:'htmlall':'UTF-8'}views/js/tinymce/tinymce.min.js"></script>-->
<!--<script type="text/javascript" src="{$module_dir|escape:'htmlall':'UTF-8'}views/js/jscolor.js"></script>-->
<script type="text/javascript">
{literal}
$(function() {
    tinymce.init({
        selector: "textarea",
        plugins: ["image", "table", "textcolor"],
        file_browser_callback: function(field_name, url, type, win) {
            if(type=='image') $('#my_form input').click();
        },
        toolbar1: "link image forecolor backcolor"
    });
});
{/literal}
var tpl = 0;
</script>
<style>
input.color {
    width: 10em;
    padding: 3px 0;
    border: 1px solid black;
    text-align: center;
    cursor: pointer;
}
</style>
<div id="backgroundModal" style="width:100%;height:100%;position:absolute;background-color:black;opacity: 0.9;display:none; z-index: 10;" onClick="closePreview();">&nbsp;</div>
<div id="myModal" style="display: none; position: absolute; width: 1024px; height: auto; z-index: 15; border: 1px solid black; margin :1500px 22%;">
    <span class="btn btn-lg glyphicon glyphicon-remove-sign white" style="float: right; margin-right: 25px;" onClick="closePreview();"></span>
    <div id="modalContent">
        &nbsp;
    </div>
</div>
<p><img src="{$module_dir|escape:'htmlall':'UTF-8'}img/logo.png" alt="Cart Abandonment" border="0" /><br /></p>

<div class="row">
    <div class="col-sm-12">
        <div class="tabbable">
            <ul class="nav nav-tabs tab-padding tab-space-3 tab-blue" id="myTab4">
                <li class="onglet active">
                    <a data-toggle="tab" href="#panel_overview">
                        {l s='Configuration' mod='cartabandonmentpro'}
                    </a>
                </li>
                <li class="onglet">
                    <a data-toggle="tab" href="#panel_edit_account">
                        {l s='Statistics' mod='cartabandonmentpro'}
                    </a>
                </li>
            </ul>
            <div class="tab-content">
                <div id="panel_overview" class="tab-pane active">
                    <div class="row">
                        <div class="col-md-12">
                                <h3>
                                    Bienvenue dans l'interface de paramétrage de vos paniers abandonnés.
                                </h3>
                                <br>
                                <h5>
                                    Cette interface va vous permettre de paramétrer des relances de panier suivant la langue de vos prospects.
                                    <br>Une fois la langue définie, vous choisirez tout d'abord les délais de relance de panier, puis le template et enfin les promotins pour chacune de ces relances.
                                </h5>
                                <br><br>
                                {include file="./conf2.tpl"}
                        </div>
                    </div>
                </div>
                <div id="panel_edit_account" class="tab-pane">
                    fezfzf
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Addons notice -->
<div class="row">
    <div class="col-sm-12">
        {include file="./addons.tpl"}
    </div>
</div>

<script type="text/javascript" src="{$module_dir|escape:'htmlall':'UTF-8'}views/js/cartabandonment.js"></script>
<script>
{literal}
$(".onglet").click(
    function(){
        $('.onglet').removeClass('active');
        $(this).addClass('active');
    }
);
function addTemplate(){
    $("#newTemplate").show();
    $("#edit_template").hide();
}
function selectModel(id_model, id_remind){
    $(".models").hide();
    $("#model_"+id_model+"_"+id_remind).show();
    $("#model"+id_remind).val(id_model);
    tpl = id_model;
}

{/literal}
</script>
