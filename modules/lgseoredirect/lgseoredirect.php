<?php
/**
 *  Please read the terms of the CLUF license attached to this module(cf "licences" folder)
 *
 * @author    Línea Gráfica E.C.E. S.L.
 * @copyright Lineagrafica.es - Línea Gráfica E.C.E. S.L. all rights reserved.
 * @license   https://www.lineagrafica.es/licenses/license_en.pdf https://www.lineagrafica.es/licenses/license_es.pdf https://www.lineagrafica.es/licenses/license_fr.pdf
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class LGSEORedirect extends Module
{
    public $bootstrap;
    public function __construct()
    {
        $this->name = 'lgseoredirect';
        $this->tab = 'seo';
        $this->version = '1.2.7';
        $this->author = 'Línea Gráfica';
        $this->module_key = 'f95aace4e5d00f07742643a87be835fe';
        $this->need_instance = 0;
        $this->bootstrap = true;
        parent::__construct();
        $this->displayName = $this->l('301, 302, 303 URL Redirects - SEO');
        $this->description = $this->l('Create an unlimited number of 301, 302 and 303 URL redirects.');

        /* Backward compatibility */
        if (_PS_VERSION_ < '1.5') {
            require(_PS_MODULE_DIR_.$this->name.'/backward_compatibility/backward.php');
        }
    }

    public function install()
    {
        if (! parent::install()) {
            return false;
        }
        $queries = array(
            'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'lgseoredirect` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `url_old` text NOT NULL,
              `url_new` text NOT NULL,
              `redirect_type` varchar(10) NOT NULL,
              `update` datetime NOT NULL,
              `id_shop` int(11) NOT NULL,
              PRIMARY KEY (`id`),
              KEY `redirect_type` (`redirect_type`)
              ) ENGINE='.(defined('ENGINE_TYPE') ? ENGINE_TYPE : 'Innodb').' CHARSET=utf8'
        );

        foreach ($queries as $query) {
            if (! Db::getInstance()->Execute($query)) {
                parent::uninstall();
                return false;
            } else {
                return true;
            }
        }
    }

    public function uninstall()
    {
        Db::getInstance()->Execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'lgseoredirect`');
        return parent::uninstall();
    }

    private function redirects()
    {
        $redirects = Db::getInstance()->ExecuteS(
            'SELECT * FROM '._DB_PREFIX_.'lgseoredirect lg '.
            'INNER JOIN '._DB_PREFIX_.'shop_url su '.
            'WHERE lg.id_shop = su.id_shop '.
            'ORDER BY lg.id DESC'
        );
        return $redirects;
    }

    private function formatBootstrap($text)
    {
        $text = str_replace('<fieldset>', '<div class="panel">', $text);
        $text = str_replace('</fieldset>', '</div>', $text);
        $text = str_replace('<legend>', '<h3>', $text);
        $text = str_replace('</legend>', '</h3>', $text);
        return $text;
    }

    private function fecha($date)
    {
        $datepre  = explode(' ', $date);
        $date1pre = explode('-', $datepre[0]);
        $date2pre  = explode(':', $datepre[1]);
        return $date1pre[2].'/'.$date1pre[1].'/'.$date1pre[0].' '.$date2pre[0].':'.$date2pre[1];
    }

    private function getP()
    {
        $default_lang = $this->context->language->id;
        $lang         = Language::getIsoById($default_lang);
        $pl           = array('es','fr','it');
        if (!in_array($lang, $pl)) {
            $lang = 'en';
        }
        $this->context->controller->addCSS(_MODULE_DIR_.$this->name.'/views/css/publi/style.css');
        $base = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off')  ?
            'https://'.$this->context->shop->domain_ssl :
            'http://'.$this->context->shop->domain);
        if (version_compare(_PS_VERSION_, '1.5.0', '>')) {
            $uri = $base.$this->context->shop->getBaseURI();
        } else {
            $uri = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off')  ?
                    'https://'._PS_SHOP_DOMAIN_SSL_DOMAIN_:
                    'http://'._PS_SHOP_DOMAIN_).__PS_BASE_URI__;
        }
        $path = _PS_MODULE_DIR_.$this->name
            .DIRECTORY_SEPARATOR.'views'
            .DIRECTORY_SEPARATOR.'publi'
            .DIRECTORY_SEPARATOR.$lang
            .DIRECTORY_SEPARATOR.'index.php';
        $object = Tools::file_get_contents($path);
        $object = str_replace('src="/modules/', 'src="'.$uri.'modules/', $object);

        return $object;
    }

    public function getContent()
    {
        if (version_compare(_PS_VERSION_, '1.6.0', '<')) {
            $this->context->controller->addJS(_MODULE_DIR_.$this->name.'/views/js/bootstrap.js');
            $this->context->controller->addJS(_MODULE_DIR_.$this->name.'/views/js/admin15.js');
            $this->context->controller->addCSS(_MODULE_DIR_.$this->name.'/views/css/admin15.css');
        }
        $tokenP = Tools::getAdminTokenLite('AdminPerformance');
        $tokenM = Tools::getAdminTokenLite('AdminModules');
        $this->_html =
        $this->getP().
        '<link type="text/css" rel="stylesheet" href="../modules/'.$this->name.'/views/css/'.$this->name.'.css"
        media="all">
        <h2>'.$this->displayName.'</h2><br>';
        /* check if the FrontController override exists */
        if (!file_exists(_PS_ROOT_DIR_.'/override/classes/controller/FrontController.php') and _PS_VERSION_ >= '1.5') {
            $this->_html .= $this->displayError(
                $this->l('The FrontController.php override is missing.').'&nbsp;'.
                $this->l('Please reset the module or copy the override manually on your FTP').'&nbsp;'.
                $this->l('(copy the file').' /modules/lgseoredirect/override/classes/controller/FrontController.php '.
                $this->l('and paste it into the folder').' /override/classes/controller/).'
            );
        }
        /* check if the overrides are not disabled */
        if ((int)Configuration::get('PS_DISABLE_OVERRIDES') > 0) {
            $this->_html .= $this->displayError(
                $this->l('The overrides are currently disabled on your store.').'&nbsp;'.
                $this->l('Please change the configuration').
                '&nbsp;<a href="index.php?tab=AdminPerformance&token='.$tokenP.'" target="_blank">
                '.$this->l('here').'
                </a>&nbsp;'.
                $this->l('and choose "Disable all overrides: NO".')
            );
        }
        /* check if the native modules are not disabled */
        if ((int)Configuration::get('PS_DISABLE_NON_NATIVE_MODULE') > 0) {
            $this->_html .= $this->displayError(
                $this->l('Non PrestaShop modules are currently disabled on your store.').'&nbsp;'.
                $this->l('Please change the configuration').
                '&nbsp;<a href="index.php?tab=AdminPerformance&token='.$tokenP.'" target="_blank">
                '.$this->l('here').'
                </a>&nbsp;'.
                $this->l('and choose "Disable non PrestaShop module: NO".')
            );
        }
        /* check if the redirect option in the module "Advanced URL" is disabled */
        if ((int)Configuration::get('VIP_ADVANCED_URL_REDIRECT') > 0) {
            $this->_html .= $this->displayError(
                $this->l('The redirects must be disabled inside the module "Advanced URL".').'&nbsp;'.
                $this->l('Please change the configuration').
                '&nbsp;
                <a href="index.php?tab=AdminModules&token='.$tokenM . '&configure=vipadvancedurl" target="_blank">
                '.$this->l('here').'
                </a>&nbsp;'.
                $this->l('and choose "Redirect: none".')
            );
        }
        $shop_id = $this->context->shop->id;
        $shop_ssl = $this->context->shop->domain_ssl;
        $shop_dom = $this->context->shop->domain;
        $shop_domain = (Tools::usingSecureMode() ? 'https://'.$shop_ssl : 'http://'.$shop_dom);
        /* create a redirect */
        if (Tools::isSubmit('newRedirect')) {
            if (stripos(Tools::getValue('url_old'), '/') > 0 or stripos(Tools::getValue('url_old'), '/') === false) {
                $this->_html .= Module::DisplayError(
                    $this->l('The format of the old URL is not valid, the URI must start with "/".').
                    '&nbsp;'.$this->l('Please correct it.')
                );
            } elseif (Tools::substr(Tools::getValue('url_old'), -1) == ' ') {
                $this->_html .= Module::DisplayError(
                    $this->l('The old URL can not end up with a whitespace.').
                    '&nbsp;'.$this->l('Please correct it.')
                );
            } elseif (
                stripos(Tools::getValue('url_new'), 'http') > 0
                or stripos(Tools::getValue('url_new'), 'http') === false
            ) {
                $this->_html .= Module::DisplayError(
                    $this->l('The format of the new URL is not valid, it must start with "http://" or "https://".').
                    '&nbsp;'.$this->l('Please correct it.')
                );
            } elseif (Tools::substr(Tools::getValue('url_new'), -1) == ' ') {
                $this->_html .= Module::DisplayError(
                    $this->l('The new URL can not end up with a whitespace.').
                    '&nbsp;'.$this->l('Please correct it.')
                );
            } else {
                Db::getInstance()->Execute(
                    'INSERT INTO '._DB_PREFIX_.'lgseoredirect '.
                    'VALUES (
                        NULL,
                        \''.pSQL(Tools::getValue('url_old')).'\',
                        \''.pSQL(Tools::getValue('url_new')).'\',
                        \''.pSQL(Tools::getValue('type')).'\',
                        NOW(),
                        \''.$shop_id.'\'
                    )'
                );
                $this->_html .= Module::DisplayConfirmation($this->l('The redirect has been successfully created'));
            }
        }
        /* delete a redirect */
        if (Tools::isSubmit('deleteRedirect')) {
            Db::getInstance()->Execute(
                'DELETE FROM '._DB_PREFIX_.'lgseoredirect '.
                'WHERE id = '.(int)Tools::getValue('id_redirect')
            );
            $this->_html .= Module::DisplayConfirmation($this->l('The redirect has been successfully deleted'));
        }
        /* delete selected redirects */
        if (Tools::isSubmit('deleteSelected')) {
            $redirects = Db::getInstance()->ExecuteS(
                'SELECT * FROM '._DB_PREFIX_.'lgseoredirect '.
                'ORDER BY id ASC'
            );
            foreach ($redirects as $redirect) {
                if (Tools::getValue('checkbox'.$redirect['id']) == 1) {
                    Db::getInstance()->Execute(
                        'DELETE FROM '._DB_PREFIX_.'lgseoredirect '.
                        'WHERE id = '.(int)$redirect['id']
                    );
                }
            }
            $this->_html .=
            Module::DisplayConfirmation($this->l('The selected redirects have been successfully deleted'));
        }
        /* delete all redirects */
        if (Tools::isSubmit('deleteAll')) {
            Db::getInstance()->Execute('TRUNCATE TABLE '._DB_PREFIX_.'lgseoredirect');
            $this->_html .= Module::DisplayConfirmation($this->l('The redirects have been successfully deleted'));
        }
        /* import CSV file */
        if (Tools::isSubmit('newCSV')) {
            $separator = Tools::getValue('separator');
            if ($separator == 2) {
                $sp = ',';
            } else {
                $sp = ';';
            }
            if (is_uploaded_file($_FILES['csv']['tmp_name'])) {
                $type = explode(".", $_FILES['csv']['name']);
                if (Tools::strtolower(end($type)) == 'csv') {
                    if (
                        move_uploaded_file(
                            $_FILES['csv']['tmp_name'],
                            dirname(__FILE__).'/csv/'.$_FILES['csv']['name']
                        )
                    ) {
                        $archivo = $_FILES['csv']['name'];
                        $fp = fopen(dirname(__FILE__).'/csv/'.$archivo, 'r');
                        while (($datos = fgetcsv($fp, 1000, ''.$sp.'')) !== false) {
                            Db::getInstance()->Execute(
                                'INSERT INTO '._DB_PREFIX_.'lgseoredirect '.
                                'VALUES (
                                    NULL,
                                    \''.pSQL($datos[0]).'\',
                                    \''.pSQL($datos[1]).'\',
                                    \''.pSQL($datos[2]).'\',
                                    NOW(),
                                    \''.pSQL($datos[3]).'\'
                                )'
                            );
                        }
                        fclose($fp);
                        $this->_html .=
                        Module::DisplayConfirmation(
                            $this->l('The redirects of the CSV file have been successfully created')
                        );
                    }
                } else {
                    $this->_html .=
                    Module::DisplayError(
                        $this->l('The format of the file is not valid, it must be saved in ".csv" format.').
                        '&nbsp;'.$this->l('Please correct it.')
                    );
                }
            } else {
                $this->_html .= Module::DisplayError($this->l('An error occurred while uploading the CSV file'));
            }
        }
        // export CSV file
        if (Tools::isSubmit('export')) {
            $separator = Tools::getValue('separator');
            if ($separator == 2) {
                $sp = ',';
            } else {
                $sp = ';';
            }
            $ln = "\n";
            $fp = fopen(_PS_ROOT_DIR_.'/modules/'.$this->name.'/csv/saveredirects.csv', 'w');
            $getredirects = Db::getInstance()->ExecuteS(
                'SELECT * FROM '._DB_PREFIX_.'lgseoredirect '.
                'ORDER BY id ASC'
            );
            foreach ($getredirects as $getredirect) {
                fwrite(
                    $fp,
                    utf8_decode($getredirect['url_old'].$sp.$getredirect['url_new']).$sp.
                    $getredirect['redirect_type'].$sp.$getredirect['id_shop'].$ln
                );
            }
            fclose($fp);
            if ($getredirects != false) {
                $this->_html .=
                Module::DisplayConfirmation(
                    $this->l('The redirects have been correctly exported,').
                    '&nbsp;<a href=../modules/'.$this->name.'/csv/saveredirects.csv>&nbsp;'.
                    $this->l('click here to download the CSV file').
                    '</a>.'
                );
            } else {
                $this->_html .= Module::DisplayError($this->l('There are no redirects to export'));
            }
        }
        // pages not found
        if (Tools::isSubmit('pagesNotFound')) {
            $sp = ';';
            $ln = "\n";
            $fp = fopen(_PS_ROOT_DIR_.'/modules/'.$this->name.'/csv/pagesnotfound.csv', 'w');
            $pagesNotFound = Db::getInstance()->ExecuteS(
                'SELECT DISTINCT pnf.request_uri, pnf.id_shop, su.domain '.
                'FROM '._DB_PREFIX_.'pagenotfound as pnf '.
                'LEFT JOIN '._DB_PREFIX_.'lgseoredirect as lsr '.
                'ON pnf.request_uri = lsr.url_old '.
                'INNER JOIN '._DB_PREFIX_.'shop_url as su '.
                'ON pnf.id_shop = su.id_shop '.
                'WHERE lsr.url_old IS NULL '.
                'ORDER BY pnf.date_add DESC'
            );
            $domain_base = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off')  ? 'https://' : 'http://');
            $redirect_type = '301';
            foreach ($pagesNotFound as $pageNotFound) {
                fwrite(
                    $fp,
                    utf8_decode($pageNotFound['request_uri']).''.$sp.$domain_base.$pageNotFound['domain'].
                    $sp.$redirect_type.$sp.$pageNotFound['id_shop'].$ln
                );
            }
            fclose($fp);
            if ($pagesNotFound != false) {
                $this->_html .=
                Module::DisplayConfirmation(
                    $this->l('The list of pages not found has been correctly generated,').
                    '&nbsp;<a href=../modules/'.$this->name.'/csv/pagesnotfound.csv>&nbsp;'.
                    $this->l('click here to download the CSV file').
                    '</a>.'
                );
            } else {
                $this->_html .= Module::DisplayError($this->l('There are no pages not found on your shop'));
            }
        }

        // menu bar
        $this->_html .= '
        <div id="menubar">
            <script>
            $(document).ready(function(){
                    $("#individualredirect").show(); $("#bulkredirects").hide(); $("#listredirects").hide();
                    $("#buttonindividualredirect").removeClass("btn-default").addClass("btn-primary"); 
                    $("#buttonbulkredirects").removeClass("btn-primary").addClass("btn-default"); 
                    $("#buttonlistredirects").removeClass("btn-primary").addClass("btn-default"); 
                $("#buttonindividualredirect").click(function(){
                    $("#individualredirect").show(); $("#bulkredirects").hide(); $("#listredirects").hide();
                    $("#buttonindividualredirect").removeClass("btn-default").addClass("btn-primary"); 
                    $("#buttonbulkredirects").removeClass("btn-primary").addClass("btn-default"); 
                    $("#buttonlistredirects").removeClass("btn-primary").addClass("btn-default"); 
                });
                $("#buttonbulkredirects").click(function(){
                    $("#individualredirect").hide(); $("#bulkredirects").show(); $("#listredirects").hide();
                    $("#buttonindividualredirect").removeClass("btn-primary").addClass("btn-default");  
                    $("#buttonbulkredirects").removeClass("btn-default").addClass("btn-primary"); 
                    $("#buttonlistredirects").removeClass("btn-primary").addClass("btn-default"); 
                });
                $("#buttonlistredirects").click(function(){
                    $("#individualredirect").hide(); $("#bulkredirects").hide(); $("#listredirects").show();
                    $("#buttonindividualredirect").removeClass("btn-primary").addClass("btn-default");  
                    $("#buttonbulkredirects").removeClass("btn-primary").addClass("btn-default"); 
                    $("#buttonlistredirects").removeClass("btn-default").addClass("btn-primary");
                });
            });
            </script>';
        $countredirects = Db::getInstance()->getValue('SELECT COUNT(*) FROM '._DB_PREFIX_.'lgseoredirect');
        $this->_html .= '
            <fieldset>
                <a id="buttonindividualredirect" class="button btn btn-default" style="width:280px;">
                    <i class="icon-plus-square"></i>&nbsp;'.$this->l('Create a redirect').'
                </a>
                <a id="buttonbulkredirects" class="button btn btn-default" style="width:280px;">
                    <i class="icon-cloud-upload"></i>&nbsp;'.$this->l('Import redirects in bulk').'
                </a>
                <a id="buttonlistredirects" class="button btn btn-default" style="width:280px;">
                    <i class="icon-list"></i>&nbsp;'.$this->l('List of created redirects').' ('.$countredirects.')
                </a>
            </fieldset>
        </div>';

        /* Create redirects individually */
        $this->_html .= '
        <div id="individualredirect">
        <fieldset>
            <legend>
                '.$this->l('Create a redirect').
                '&nbsp;
                <a href="../modules/'.$this->name.'/readme/readme_'.$this->l('en').'.pdf#page=4" target="_blank">
                <img src="../modules/'.$this->name.'/views/img/info.png">
                </a>
            </legend>
            <form method="post" action="'.$_SERVER['REQUEST_URI'].'"> 
                <table class="table" style="width:95%">
                    <tr>
                        <td style="width:15%">
                            <label from="url_old">'.$this->l('Old URL:').'</label>
                        </td>
                        <td style="width:20%">
                            <div style="line-height:25px;">'.$shop_domain.'</div>
                        </td>
                        <td style="width:65%">
                            <input type="text" name="url_old" id="url_old" value=""
                            placeholder="'.$this->l('/.../old-page').'" style="width:99%">
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label from="url_new">'.$this->l('New URL:').'</label>
                        </td>
                        <td colspan="2">
                            <input type="text" name="url_new" id="url_new" value=""
                            placeholder="'.$this->l('http://www.domain.com/.../new-page').'" style="width:99%">
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label from="url_new">'.$this->l('Type:').'</label>
                        </td>
                        <td colspan="2">
                            <select name="type">
                                <option value="301">
                                    301 - '.$this->l('URL moved PERMANENTLY').'
                                </option>
                                <option value="302">
                                    302 - '.$this->l('URL moved TEMPORARILY').'
                                </option>
                                <option value="303">
                                    303 - '.$this->l('GET method used to retrieve information').'
                                </option>
                            </select>
                        </td>
                    </tr>
                    <tr> 
                    </tr>
                </table><br>
                <div>
                    <label from="newRedirect"></label>
                    <button class="button btn btn-default" type="submit" name="newRedirect" style="float:left;">
                        <i class="process-icon-new"></i> '.$this->l('Create the redirect').'
                    </button>
                    <label from="deleteAll"></label>
                    <button class="button btn btn-default" type="submit" style="float:right; margin-left:5px;"
                    onclick="return confirm(\'Confirmation\')" name="deleteAll">
                        <i class="icon-trash"></i> '.$this->l('Delete all redirects').'
                    </button>
                    <label from="export"></label>
                    <button class="button btn btn-default" type="submit" name="export"
                    style="float:right; margin-left:5px;">
                        <i class="icon-cloud-download"></i> '.$this->l('Export all redirects').'
                    </button>
                    <label from="pagesNotFound"></label>
                    <button class="button btn btn-default" type="submit" name="pagesNotFound"
                    style="float:right; margin-left:5px;">
                        <i class="icon-frown-o"></i> '.$this->l('Pages not found').'
                    </button>
                    <div style="clear:both;"></div>
                </div>
            </form>
        </fieldset>
        </div>';
        /* Create redirects in bulk */
        $this->_html .= '
        <div id="bulkredirects">
            <fieldset>
                <legend>
                    '.$this->l('Import redirects in bulk').
                    '&nbsp;
                    <a href="../modules/'.$this->name.'/readme/readme_'.$this->l('en').'.pdf#page=6" target="_blank">
                    <img src="../modules/'.$this->name.'/views/img/info.png">
                    </a>
                </legend>
                <form method="post" action="'.$_SERVER['REQUEST_URI'].'" enctype="multipart/form-data">
                    <br>
                    <h3>
                        <label>
                            <i class="icon-exclamation-triangle"></i>&nbsp;'.
                            $this->l('You must respect the following rules to upload the redirects correctly:').'
                        </label>
                    </h3>
                    <table class="table" style="text-align:center; width:50%;" border="1">
                        <tr>
                            <th style="text-align:center;" class="lgupper">
                                '.$this->l('Column').' A
                            </th>
                            <th style="text-align:center;" class="lgupper">
                                '.$this->l('Column').' B
                            </th>
                            <th style="text-align:center;" class="lgupper">
                                '.$this->l('Column').' C
                            </th>
                            <th style="text-align:center;" class="lgupper">
                                '.$this->l('Column').' D
                            </th>
                        <tr>
                        </tr>
                            <td>
                                <span class="toolTip3">
                                <a href="#csv_uploader">'.$this->l('Old URI').'</a>
                                <p class="tooltipDesc3">
                                '.$this->l('In the column A of your CSV file, write the old URI.').
                                '<br>
                                <span class="lgunder">'.$this->l('It must start with "/".').'
                                </span>
                                </p>
                                </span>
                            </td>
                            <td>
                                <span class="toolTip3">
                                <a href="#csv_uploader">'.$this->l('New URL').'</a>
                                <p class="tooltipDesc3">
                                '.$this->l('In the column B of your CSV file, write the new URL.').
                                '<br>
                                <span class="lgunder">'.$this->l('It must start with "http" or "https".').'
                                </span>
                                </p>
                                </span>
                            </td>
                            <td>
                                <span class="toolTip3">
                                <a href="#csv_uploader">
                                '.$this->l('Redirect type').'</a>
                                <p class="tooltipDesc3">
                                '.$this->l('In the column C of your CSV file, add the type of redirect.').
                                '<br>
                                <span class="lgunder">'.$this->l('It must be "301", "302" or "303".').'
                                </span>
                                </p>
                                </span>
                            </td>
                            <td>
                                <span class="toolTip3">
                                <a href="#csv_uploader">'.$this->l('Shop ID').'</a>
                                <p class="tooltipDesc3">
                                '.$this->l('In the column D, add the shop ID for which you want').
                                '&nbsp;'.$this->l('the old URI to apply.').'
                                <br>
                                <span class="lgunder">'.$this->l('Use "1" if you don\'t use the multistore').'
                                </span>
                                </p>
                            </span>
                            </td>
                        </tr>
                    </table>
                    <br>
                    <div class="alert alert-info">
                        - '.$this->l('Move your mouse over the table to get more information.').'<br>
                        </a>
                        - <a href="../modules/'.$this->name.'/csv/redirects.csv">
                        '.$this->l('Click here to download an example of CSV file').
                        '&nbsp;'.$this->l('(you can write your redirects directly in it)').'.
                        </a>
                    </div>
                    <br><br>
                    <h3>
                        <span class="lgfloat">
                        <label>
                            <i class="icon-file-excel-o"></i>
                            &nbsp;'.$this->l('Select your file').'&nbsp;&nbsp;
                        </label>
                        </span>
                        <input type="file" name="csv" id="csv" class="btn btn-default lgfloat"><br>
                    </h3>
                    <div class="alert alert-info">
                    '.$this->l('The file must be in.csv format and respect the structure indicated above').'
                    &nbsp;'.$this->l('(4 columns and one redirect per line).').'
                    </div>
                    <div class="lgclear"></div><br><br>
                    <h3>
                        <span class="lgfloat">
                        <label>
                            <i class="icon-scissors"></i>
                            &nbsp;'.$this->l('Indicate the separator of your CSV file (important)').'&nbsp;&nbsp;
                        </label>
                        </span>
                        <select id="separator" class="lgfloat fixed-width-xl" name="separator">
                            <option value="1">
                                '.$this->l('Semi-colon').'
                            </option>
                            <option value="2">
                                '.$this->l('Comma').'
                            </option>
                        </select>
                    </h3>
                    <div class="alert alert-info">
                        '.$this->l('Open your csv file with a text editor ("Notepad" for example)').'
                        &nbsp;'.$this->l('and check if the elements are separated with a semi-colon or comma.').'
                    </div>
                    <div class="lgclear"></div><br>
                    <div>
                        <label from="newCSV"></label>
                        <button class="button btn btn-default" type="submit" name="newCSV" style="float:left;">
                            <i class="process-icon-import"></i> '.$this->l('Import the redirects').'
                        </button>
                        <label from="deleteAll"></label>
                        <button class="button btn btn-default" type="submit" style="float:right; margin-left:5px;"
                        onclick="return confirm(\'Confirmation\')" name="deleteAll">
                            <i class="icon-trash"></i> '.$this->l('Delete all redirects').'
                        </button>
                        <label from="export"></label>
                        <button class="button btn btn-default" type="submit" name="export"
                        style="float:right; margin-left:5px;">
                            <i class="icon-cloud-download"></i> '.$this->l('Export all redirects').'
                        </button>
                        <label from="pagesNotFound"></label>
                        <button class="button btn btn-default" type="submit" name="pagesNotFound"
                        style="float:right; margin-left:5px;">
                            <i class="icon-frown-o"></i> '.$this->l('Pages not found').'
                        </button>
                        <div style="clear:both;"></div>
                    </div>
                </form>
            </fieldset>
        </div>';
        /* List of created redirects */
        $redirects = $this->redirects();
        if ($redirects) {
            $this->_html .= '
        <div id="listredirects">
            <fieldset>
                <legend>'.$this->l('List of created redirects').' ('.$countredirects.')</legend>
                <form method="post" action="'.$_SERVER['REQUEST_URI'].'">
                <div style="overflow-x:auto;">
                <table class="table" id="tableredirect" width="100%">
                <thead>
                    <tr>
                        <th></th>
                        <th>'.$this->l('ID').'</th>
                        <th>'.$this->l('OLD URL').'</th>
                        <th></th>
                        <th>'.$this->l('NEW URL').'</th>
                        <th>'.$this->l('TYPE').'</th>
                        <th>'.$this->l('DATE').'</th>
                        <th></th>
                    </tr>
                    <tr>
                        <th>
                            <input type="checkbox" id="checkall" value="1" name="4">
                            <b> '.$this->l('All').'</b>
                        </th>
                        <th>
                            <input type="text" name="filterid" id="filterid" style="width:50px;">
                        </th>
                        <th>
                            <input type="text" name="filteroldurl" id="filteroldurl">
                        </th>
                        <th>
                        </th>
                        <th>
                            <input type="text" name="filternewurl" id="filternewurl">
                        </th>
                        <th>
                            <select name="filtertype" id="filtertype">
                                <option value="0" selected>---</option>
                                <option value="301">301</option>
                                <option value="302">302</option>
                                <option value="303">303</option>
                            </select>
                        </th>
                        <th>
                            <input type="text" name="filterdate" id="filterdate">
                        </th>
                        <th>
                            <select name="filtererror" id="filtererror" style="width:120px;">
                                <option value="0" selected>---</option>
                                <option value="1">'.$this->l('Duplicate redirects').'</option>
                                <option value="2">'.$this->l('Wrong redirects').'</option>
                            </select>
                        </th>
                    </tr>
                </thead>
                <tbody>';
            $domain_base = (Tools::usingSecureMode() ? 'https://' : 'http://');
            $checkduplicate = array();
            foreach ($redirects as $redirect) {
                /* ID */
                $this->_html .= '
                    <tr id="'.$redirect['id'].'">
                        <td>
                            <input type="checkbox" name="checkbox'.$redirect['id'].'" value="1">
                        </td>
                        <td>
                            <span id="redid'.$redirect['id'].'">'.$redirect['id'].'</span>
                        </td>
                        <td>
                            <span id="oldurl'.$redirect['id'].'">';
                /* check if the old URI starts with a / */
                $startwith = stripos($redirect['url_old'], '/');
                if ($startwith > 0 or $startwith === false) {
                    $this->_html .= '
                            <input type="hidden" name="wrongformat'.$redirect['id'].'"
                            class="wrongformat'.$redirect['id'].'" value="2">
                            <span class="toolTip1">
                            <img src="../modules/'.$this->name.'/views/img/important.png" />
                            <p class="tooltipDesc1">
                            '.$this->l('Wrong format: the old URI must start with a "/".').'
                            </p>
                            </span>';
                }
                /* check if the old URI ends with a whitespace */
                $endwith = Tools::substr($redirect['url_old'], -1);
                if ($endwith === ' ') {
                    $this->_html .= '
                            <input type="hidden" name="wrongformat'.$redirect['id'].'"
                            class="wrongformat'.$redirect['id'].'" value="2">
                            <span class="toolTip1">
                            <img src="../modules/'.$this->name.'/views/img/important.png" />
                            <p class="tooltipDesc1">
                            '.$this->l('Wrong format: the old URL can not end up with a whitespace.').'
                            </p>
                            </span>';
                }
                /* check if the old URI is duplicated */
                if (@++$checkduplicate[$redirect['url_old']] > 1) {
                    $this->_html .= '
                            <input type="hidden" name="duplicate'.$redirect['id'].'"
                            class="duplicate'.$redirect['id'].'" value="1">
                            <span class="toolTip2">
                            <img src="../modules/'.$this->name.'/views/img/important2.png" />
                            <p class="tooltipDesc2">
                            '.$this->l('Duplicated redirects: several redirects exist for this old URI.').'
                            </p>
                            </span>';
                }
                /* OLD URI */
                $this->_html .= '
                            <a href="'.$domain_base.''.$redirect['domain'].''.$redirect['url_old'].'" target="_blank">'.
                            $domain_base.$redirect['domain'].'<span style="font-weight:bold;">'.
                            urldecode($redirect['url_old']).
                            '</span>
                            </a>
                        </td>
                        <td style="font-size:x-large;">
                            &rarr;
                        </td>
                        <td>
                            <span id="newurl'.$redirect['id'].'">';
                /* check if the new URL starts with a http or https */
                $startwith2 = stripos($redirect['url_new'], 'http');
                if ($startwith2 > 0 or $startwith2 === false) {
                    $this->_html .= '
                            <input type="hidden" name="wrongformat'.$redirect['id'].'"
                            class="wrongformat'.$redirect['id'].'" value="2">
                            <span class="toolTip1" class="wrongformat'.$redirect['id'].'">
                            <img src="../modules/'.$this->name.'/views/img/important.png" />
                            <p class="tooltipDesc1">
                            '.$this->l('Wrong format: the new URL must start with a "http" or "https".').'
                            </p>
                            </span>';
                }
                /* check if the new URL ends with a whitespace */
                $endwith2 = Tools::substr($redirect['url_new'], -1);
                if ($endwith2 === ' ') {
                    $this->_html .= '
                            <input type="hidden" name="wrongformat'.$redirect['id'].'"
                            class="wrongformat'.$redirect['id'].'" value="2">
                            <span class="toolTip1" class="wrongformat'.$redirect['id'].'">
                            <img src="../modules/'.$this->name.'/views/img/important.png" />
                            <p class="tooltipDesc1">
                            '.$this->l('Wrong format: the new URL can not end up with a whitespace.').'
                            </p>
                            </span>';
                }
                /* NEW URL */
                $this->_html .= '
                            '.urldecode($redirect['url_new']).'</span>
                        </td>
                        <td>
                            <input type="hidden" name="type'.$redirect['id'].'" id="type'.$redirect['id'].'"
                            value="'.$redirect['redirect_type'].'">';
                /* check if the type of redirect starts is 301, 302 or 303*/
                $redirect_type = array('301', '302', '303');
                if (!in_array($redirect['redirect_type'], $redirect_type)) {
                    $this->_html .= '
                            <input type="hidden" name="wrongformat'.$redirect['id'].'"
                            class="wrongformat'.$redirect['id'].'" value="2">
                            <span class="toolTip1" class="wrongformat'.$redirect['id'].'">
                            <img src="../modules/'.$this->name.'/views/img/important.png" />
                            <p class="tooltipDesc1">
                            '.$this->l('Wrong format: the type of redirect must be "301", "302" or "303".').'
                            </p>
                            </span>';
                }
                /* TYPE - DATE -DELETE */
                $this->_html .= '
                            '.$redirect['redirect_type'].'
                        </td>
                        <td>
                            <span id="date'.$redirect['id'].'">'.$this->fecha($redirect['update']).'
                        </td>
                        <td>
                            <form method="post" action="'.$_SERVER['REQUEST_URI'].'">
                                <input type="hidden" name="id_redirect" value="'.$redirect['id'].'">
                                <button class="button btn btn-default" type="submit" name="deleteRedirect">
                                    <i class="icon-trash"></i> '.$this->l('Delete').'
                                </button>
                            </form>
                        </td>
                    </tr>';
            }
            $this->_html .= '
                </tbody>
                </table>
                </div>
                <label from="deleteSelected"></label>
                <button class="button btn btn-default" type="submit" onclick="return confirm(\'Confirmation\')"
                name="deleteSelected" style="float:left;">
                    <i class="icon-trash"></i> '.$this->l('Delete selection').'
                </button>
            </form>
            </fieldset>
        </div>';
        }
        $this->_html .= '<script type="text/javascript">

			function smartfilter()
			{
				var fredid = $("#filterid").val();
				var foldurl = $("#filteroldurl").val();
                var fnewurl = $("#filternewurl").val();
                var ftype = $("#filtertype").val();
                var fdate = $("#filterdate").val();
                var ferror = $("#filtererror").val();
				var filas = $("#tableredirect").find("tbody>tr");
				var numlines = filas.length;
				for (i=0;i<numlines;i++)
				{
					$("#"+filas[i].id).css("display","");

					var vredid = $("#redid"+filas[i].id).html();
					if (vredid.toLowerCase().indexOf(fredid.toLowerCase()) == -1)
						$("#"+filas[i].id).css("display","none");

					var voldurl = $("#oldurl"+filas[i].id).html();
					if (voldurl.toLowerCase().indexOf(foldurl.toLowerCase()) == -1)
						$("#"+filas[i].id).css("display","none");

					var vnewurl = $("#newurl"+filas[i].id).html();
					if (vnewurl.toLowerCase().indexOf(fnewurl.toLowerCase()) == -1)
						$("#"+filas[i].id).css("display","none");

					if (ftype == 301)
						if ($("#type"+filas[i].id).val() != 301)
							$("#"+filas[i].id).css("display","none");
					if (ftype == 302)
						if ($("#type"+filas[i].id).val() != 302)
							$("#"+filas[i].id).css("display","none");
					if (ftype == 303)
						if ($("#type"+filas[i].id).val() != 303)
							$("#"+filas[i].id).css("display","none");

                    var vdate = $("#date"+filas[i].id).html();
					if (vdate.toLowerCase().indexOf(fdate.toLowerCase()) == -1)
						$("#"+filas[i].id).css("display","none");

					if (ferror == 1)
						if ($(".duplicate"+filas[i].id).val() != 1)
							$("#"+filas[i].id).css("display","none");
					if (ferror == 2)
						if ($(".wrongformat"+filas[i].id).val() != 2)
							$("#"+filas[i].id).css("display","none");
				}
			}
			$(document).ready(function(){
				$("#filterid").keyup(smartfilter);
				$("#filteroldurl").keyup(smartfilter);
 				$("#filternewurl").keyup(smartfilter);
				$("#filtertype").change(smartfilter);
				$("#filterdate").keyup(smartfilter);
				$("#filtererror").change(smartfilter);
				$("#checkall").click(function(){
                        if ($(this).is(":checked"))
							$("input[type=checkbox]:visible").attr("checked", "checked");
						else
							$("input[type=checkbox]").removeAttr("checked");
				});
			});
			</script>
			';
        if ($this->bootstrap == true) {
            $this->_html = $this->formatBootstrap($this->_html);
        }
        return $this->_html;
    }
}
