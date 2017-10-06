<?php

@ini_set('memory_limit','256M');
class LoyaltyUpdate extends Module
{


	/* @var boolean error */
	protected $error = false;
	
	public function __construct()
	{
	 	$this->name = 'loyaltyupdate';
		$this->module_key = 'c8bb36f51f8a60b5309309fbd1321cad';
	 if(_PS_VERSION_ > "1.4.0.0" && _PS_VERSION_ < "1.5.0.0"){
		$this->tab = 'pricing_promotion';
		$this->author = 'RSI';
		$this->need_instance = 0;
		}
		elseif(_PS_VERSION_ > "1.5.0.0"){
				$this->tab = 'pricing_promotion';
		$this->author = 'RSI';
			}
		
		else{
		$this->tab = 'Tools';
		}
		 if (_PS_VERSION_ > '1.6.0.0') {
            $this->bootstrap = true;
        }
	 	$this->version = '1.5.0';

	 	parent::__construct();

        $this->displayName = $this->l('Custom Loyalty Rewards');
        $this->description = $this->l('Edit loyalty points');
		$this->confirmUninstall = $this->l('Are you sure you want to delete all the data ?');
	}
	
	public function install()
	{
		
		if (parent::install() == false)
		return false;
	
	
		if(ini_get("allow_url_fopen") == "0"){
		ini_set("allow_url_fopen", "1");
		}

		
		return true;
	}
	
	
	public function uninstall()
	{
	
	

return true;

	}
	
	
	
	
	public function getContent()
    {
		$this->_html = '';
		if (_PS_VERSION_ < '1.6.0.0')
		$this->_html = '<h2>'.$this->displayName.'</h2>
		<script type="text/javascript" src="'.$this->_path.'loyaltyupdate.js"></script>';
			
		/* Add a link */
		if (isset($_POST['send']))
		{
		//$sql='UPDATE '._DB_PREFIX_.'loyalty SET `points`= \''.Tools::getValue('points').'\' WHERE `id_loyalty` = '.Tools::getValue('id_customer').';';		
					if (!Db::getInstance()->Execute('UPDATE '._DB_PREFIX_.'loyalty SET `points`= \''.Tools::getValue('points').'\' WHERE `id_customer` = '.Tools::getValue('id_customer').' AND `id_order` = '.Tools::getValue('id_order').';'))
			return false;
		//$this->_html .= $this->displayConfirmation($this->l('points of user '.Tools::getValue('email').' updated'));
		$this->_html .= $this->displayConfirmation($this->l('points of order '.Tools::getValue('id_order').' for the user '.Tools::getValue('email').' have been updated'));
		}
		if (_PS_VERSION_ > '1.6.0.0')
		return $this->_displayInfo().$this->_displayForm().$this->_html.$this->_displayAdds();
		else
       return  $this->_displayForm().$this->_html;
    }
	 private function _displayInfo()
    {
        return $this->display(
            __FILE__,
            'views/templates/hook/infos.tpl'
        );
    }

    private function _displayAdds()
    {
        return $this->display(
            __FILE__,
            'views/templates/hook/adds.tpl'
        );
    }
	private function _displayForm()
	{
		
	$output = '';	
		if (_PS_VERSION_ > "1.4.0.0")
	{
			$output .= '	<script type="text/javascript" src="../modules/loyaltyupdate/accordion.ui.js"></script>
			<script type="text/javascript">
	
$(function() {
		
		$( "#accordion" ).accordion({ autoHeight: false });
	});

	</script>';
		}
		$output .= '
		
	
		';
		 	if (_PS_VERSION_ < "1.5.0.0")
	{
			$output .= '
		';
	}
			$output .= '
	
		
	
		<style type="text/css">
		.intext {width: 390px; height: 24px; 
		font-family:arial, sans-serif; font-size:12px; padding:3px; xvisibility:hidden;
		}
		.layouts { padding: 50px; font-family: Georgia, serif; }
		.layout-slider { margin-bottom: 5px; width: 50%; }
		.layout-slider-settings { font-size: 12px; padding-bottom: 10px; }
		.layout-slider-settings pre { font-family: Courier; }
		.tile2 	{ 
		position:absolute; border:1px solid silver; background-color:white;
		filter:alpha(opacity=50); -moz-opacity:0.50; opacity:0.50;
		font-family:arial, sans-serif; font-size:12px; padding:3px;
		}
		.links  {position:absolute; left:0px; top:0px; width: 390px; height: 24px; 
		font-family:arial, sans-serif; font-size:12px; padding:3px; visibility:hidden;
		}
		.alts 	{position:absolute; left:0px; top:30px; width: 390px; height: 24px; 
		font-family:arial, sans-serif; font-size:12px; padding:3px; visibility:hidden;
		}
		.tools  {
		font-family:arial,sans-serif; font-size:12px; line-height:30px 
		}
		.toolbtn {width:150px;line-height:20px}
		
		
		#iconselect {
		background: url(../modules/loyaltyupdate/images/select-bg.gif) no-repeat;
		height: 25px;
		width: 250px;
		font: 13px Arial, Helvetica, sans-serif;
		padding-left: 15px;
		padding-top: 4px;
		}
		
		.selectitems {
		width:230px;
		height:auto;
		border-bottom: dashed 1px #ddd;
		padding-left:10px;
		padding-top:2px;
		}
		.selectitems span {
		margin-left: 5px;
		}
		#iconselectholder {
		width: 250px;
		overflow: auto;
		display:none;
		position:absolute;
		background-color:#FFF5EC;
		
		}
		.hoverclass{
		background-color:#FFFFFF;
		curson:hand;}
		.selectedclass{
		background-color:#FFFF99;
		}
		.box{
		background: #fff;
		margin:5px
		}
		.boxholder{
		clear: both;
		padding: 5px;
		background: #8DC70A;
		}
		.tabm{
		float: left;
		height: 32px;
		width: 102px;
		margin: 0 1px 0 0;
		text-align: center;
		background: #8DC70A url(../modules/loyaltyupdate/images/greentab.jpg) no-repeat;
		
		}
		.tabtxt{
		margin: 0;
		color: #fff;
		font-size: 12px;
		font-weight: bold;
		padding: 9px 0 0 0;
		}
		-->
		</style>
		
			<fieldset style="width:100%; margin:19px 0; border: 1px solid #ccc">
		<div id="accordion">
	
	
				
		
		
		

		';
		
			
		

		$output .= '
		<h3 style="padding-left:40px; line-height: auto;
    height: auto;margin:0">
		<img src="'.$this->_path.'search.png" alt="" title="" width="20" />'.$this->l('Search by email').'</h3>
			
			<form method="post" action="'.$_SERVER['REQUEST_URI'].'" style="padding:10px">
			<input name="email" type="text" value="" />
		
			<input name="search" type="submit" value="Search" />
			<p>'.$this->l('leave empty to find all customers').'</p>
			</form>

		<h3 style="padding-left:40px;line-height: auto;
    height: auto;margin:0">
		<img src="'.$this->_path.'logo.png" alt="" title="" width="20"/>'.$this->l('List of points').'</h3>
							<div  style="padding:10px">';
							
		
							
							
		$output .='
		<table class="table">
		<tr>
		<th>'.$this->l('ID').'</th>
		<th>'.$this->l('First name').'</th>
		<th>'.$this->l('Last name').'</th>
		<th>'.$this->l('Email').'</th>
		<th>'.$this->l('Points').'</th>

	

		</tr>';

if(@$_POST['email'] == NULL){
		$sq = Db::getInstance()
                        ->ExecuteS('SELECT l.*, c.*
		FROM '._DB_PREFIX_.'loyalty l
		LEFT JOIN '._DB_PREFIX_.'customer c ON (l.id_customer = c.id_customer)
		'.((_PS_VERSION_ > "1.5.0.0") ? "
		WHERE id_shop = ".$this->context->shop->id : '').' ORDER BY `email` ASC');
}
else
{
		$sq = Db::getInstance()
                        ->ExecuteS('SELECT l.*, c.*
		FROM '._DB_PREFIX_.'loyalty l
		LEFT JOIN '._DB_PREFIX_.'customer c ON (l.id_customer = c.id_customer)
		'.((_PS_VERSION_ > "1.5.0.0") ? "
		WHERE id_shop = ".$this->context->shop->id : '').' AND `email` = \''.$_POST['email'].'\'');
	}
	//echo $sq;
		 $sqll= $sq;
		 
	  $rpp         = 40; // results per page

        $adjacents   = 2;

        

        $page = intval(@$_GET["page"]);

        if ($page <= 0)

            $page = 1;

        

        $reload = $_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING'];

        // count total number of appropriate listings:

        @$tcount = mysql_num_rows($sqll);

        

        // count number of pages:

        $tpages = ($tcount) ? ceil($tcount / $rpp) : 1; // total pages, last page number

        

        $count = 0;

        $i     = ($page - 1) * $rpp;

     
		
		
		
	    while (($count < $rpp) && ($i < $tcount)) {

            mysqli_data_seek($sqll, $i);

            $query = mysqli_fetch_array($sqll);
		
	
		$this->_html .= '<div style="border:1px solid #ccc">
		<tr>
		<td><strong>ID Order: </strong>'.$query['id_order'].' - </td>
		<td><strong>ID Customer: </strong>'.$query['id_customer'].' - </td>
		<td><strong>Name: </strong>'.$query['firstname'].' - </td>
		<td><strong>Lastname: </strong>'.$query['lastname'].' - </td>
		<td><strong>Email: </strong>'.$query['email'].'</td>
		<td><strong>Loyalty points: </strong></td>
		<td><form method="post" action="'.$_SERVER['REQUEST_URI'].'" style="display:inline"><input name="points" type="text" value="'.$query['points'].'" /><input name="id_customer" type="hidden" id="id_customer" value="'.$query['id_customer'].'" /><input name="id_order" type="hidden" id="id_order" value="'.$query['id_order'].'" /><input name="email" type="hidden" id="email" value="'.$query['email'].'" /><input name="send" type="submit" value="Update" /></form></td>
		</td>
		</tr></div>
		
	';
	        $i++;
	  $count++;
	}
		
		
		
		
		$output .= '
		</table><br/>';
			$output .= LoyaltyUpdate::paginate_one($reload, $page, $tpages, $adjacents);
		$output .= '</div>';
		
		
		
		
		if (_PS_VERSION_ < '1.6.0.0'){
		$output .= '
</div>
		<h3 style="padding-left:40px;line-height: auto;
    height: auto;margin:0">
		<img src="'.$this->_path.'module.png" alt="" title="" width="20"/>'.$this->l('Contribute').'</h3>
		<div>
				<form action="https://www.paypal.com/cgi-bin/webscr" method="post">	<p class="clear">'.$this->l('You can contribute with a donation if our free modules and themes are usefull for you. Clic on the link and support us!').'</p>
				<p class="clear">'.$this->l('For more modules & themes visit: www.catalogo-onlinersi.com.ar').'</p>
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" value="HMBZNQAHN9UMJ">
<input type="image" src="https://www.paypalobjects.com/WEBSCR-640-20110401-1/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
<img alt="" border="0" src="https://www.paypalobjects.com/WEBSCR-640-20110401-1/en_US/i/scr/pixel.gif" width="1" height="1">

</form></div>
		
		';
		$output .= '

		<h3 style="padding-left:40px;line-height: auto;
    height: auto;margin:0">
		<img src="'.$this->_path.'pdf.png" alt="" title="" width="20"/>'.$this->l('Help').'</h3>
		<div>
		<br/>
<center><img src="../modules/loyaltyupdate/views/img/readme.png"  /><a href="../modules/loyaltyupdate/moduleinstall.pdf">README</a>  /  
						<img src="../modules/loyaltyupdate/views/img/terms.png" /><a href="../modules/loyaltyupdate/termsandconditions.pdf">TERMS</a></center><br/>
		 <center>  <p>Follow  us:</p></center>
     <center><p><a href="https://www.facebook.com/ShackerRSI" target="_blank"><img src="../modules/loyaltyupdate/views/img/facebook.png" style="  width: 64px;margin: 5px;" /></a>
        <a href="https://twitter.com/prestashop_rsi" target="_blank"><img src="../modules/loyaltyupdate/views/img/twitter.png" style="  width: 64px;margin: 5px;" /></a>
         <a href="https://www.pinterest.com/prestashoprsi/" target="_blank"><img src="../modules/loyaltyupdate/views/img/pinterest.png" style="  width: 64px;margin: 5px;" /></a>
           <a href="https://plus.google.com/+shacker6/posts" target="_blank"><img src="../modules/loyaltyupdate/views/img/googleplus.png" style="  width: 64px;margin: 5px;" /></a>
            <a href="https://www.linkedin.com/profile/view?id=92841578" target="_blank"><img src="../modules/loyaltyupdate/views/img/linkedin.png" style="  width: 64px;margin: 5px;" /></a></p></center>
			<br/>
			<p>Video:</p>
		<iframe width="640" height="360" src="https://www.youtube.com/embed/jEvuPD8vO7Q?rel=0&amp;showinfo=0" frameborder="0" allowfullscreen></iframe><br/>
			
			<p>Recommended:</p>
<object type="text/html" data="http://catalogo-onlinersi.net/modules/productsanywhere/images.php?idproduct=&desc=yes&buy=yes&type=home_default&price=yes&style=false&color=10&color2=40&bg=ffffff&width=800&height=310&lc=000000&speed=5&qty=15&skip=29,14,42,44,45&sort=1" width="800" height="310" style="border:0px #066 solid;"></object>	';
		}
		$output .= '
		 </fieldset>';
		 return $output;
	}
	
		public function getLinks()
	{
		$result = array();
		/* Get id and url */
		if (!$links = Db::getInstance()->ExecuteS('SELECT l.*, c.*
		FROM '._DB_PREFIX_.'loyalty l
		LEFT JOIN '._DB_PREFIX_.'customer c ON (l.id_customer = c.id_customer)
		'.((_PS_VERSION_ > "1.5.0.0") ? "
		WHERE id_shop = ".$this->context->shop->id : '').' ORDER BY `email` ASC GROUP BY c.id_customer'))
		return false;
		$i = 0;
		foreach ($links AS $link)
		{
		$result[$i]['id'] = $link['id_customer'];
		
		$result[$i]['firstname'] = $link['firstname'];
		$result[$i]['lastname'] = $link['lastname'];
		$result[$i]['id_loyalty'] = $link['id_loyalty'];
		$result[$i]['id_loyalty'] = $link['id_loyalty'];
		$result[$i]['email'] = $link['email'];
			$result[$i]['id_order'] = $link['id_order'];
		$result[$i]['points'] = $link['points'];
		
		$i++;
		
		}
		
		return $result;
	}
	  function paginate_one($reload, $page, $tpages)

    {

        $firstlabel = $this->l('First');

        $prevlabel  = $this->l('Prev');

        $nextlabel  = $this->l('Next');

        $lastlabel  = $this->l('Last');

        

        $out     = "<div class=\"pagin\">\n";

        $reload2 = preg_replace("/\&page=[0-9]/", "", $reload);

        // first

        if ($page > 1) {

            $out .= "<a href=\"" . $reload2 . "\">" . $firstlabel . "</a>\n";

        } else {

            $out .= "<span>" . $firstlabel . "</span>\n";

        }

        

        // previous

        if ($page == 1) {

            $out .= "<span>" . $prevlabel . "</span>\n";

        } elseif ($page == 2) {

            $out .= "<a href=\"" . $reload2 . "\">" . $prevlabel . "</a>\n";

        } else {

            $out .= "<a href=\"" . $reload2 . "&amp;page=" . ($page - 1) . "\">" . $prevlabel . "</a>\n";

        }

        

        // current

        $out .= "<span class=\"current\">" . $this->l('Page') . " " . $page . " of " . $tpages . "</span>\n";

        

        // next

        if ($page < $tpages) {

            $out .= "<a href=\"" . $reload2 . "&amp;page=" . ($page + 1) . "\">" . $nextlabel . "</a>\n";

        } else {

            $out .= "<span>" . $nextlabel . "</span>\n";

        }

        

        // last

        if ($page < $tpages) {

            $out .= "<a href=\"" . $reload2 . "&amp;page=" . $tpages . "\">" . $lastlabel . "</a>\n";

        } else {

            $out .= "<span>" . $lastlabel . "</span>\n";

        }

        

   

        

        return $out;

    }
}
