<?php

ini_set('default_socket_timeout', -1);

require_once(_PS_MODULE_DIR_ . 'wservices/classes/RedisConnect.php');

class WservicestestModuleFrontController extends ModuleFrontController
{
	protected $channels = array('mt:CM_Site1_CLT','mt:CM_Site1_PRD','mt:CM_Site1_TRF');

	public function initContent()
	{
		parent::initContent();

		$shop_domain = Context::getContext()->link->protocol_link . Configuration::getValue('PS_SHOP_DOMAIN');

		$redis_connect = new RedisConnect();
		$redis = $redis_connect->connect();

		foreach($this->channels as $channel)
		{
			$list = $redis->zRange($channel, 0, 200);
			foreach ($list as $json)
			{
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $shop_domain '/index.php?fc=module&module=wservices&controller=ws');
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
				curl_exec($ch);
				curl_close($ch);
			}
		}
		
		$redis->close();

		die();
	}
}

