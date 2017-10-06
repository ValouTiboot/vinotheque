<?php

ini_set('default_socket_timeout', -1);

require_once(_PS_MODULE_DIR_ . 'wservices/classes/RedisConnect.php');

class WservicestestModuleFrontController extends ModuleFrontController
{

	public function initContent()
	{
		parent::initContent();
		echo '<pre>';
		$redis_connect = new RedisConnect();
		$redis = $redis_connect->connect();
		// var_dump($redis->zAdd('mt:toto', 10, 'test-publish-10'));
		// var_dump($redis->publish('toto', '{"string": "Hello titi"}'));

		// print_r($redis->config('get', 'databases'));
		// print_r($redis->info('keyspace'));
		print_r($redis->keys('*'));
		$redis->zDeleteRangeByScore('mt:CM_Site1_CLT', 326, 326);
		print_r($redis->zRange('mt:CM_Site1_CLT', 0, 200));
		print_r($redis->zRange('mt:CM_Site1_PRD', 0, 200));
		// print_r($redis->info('all'));
		// print_r($redis->subscribe(array('CanalTest_1', 'CanalTest_2'), array($this, 'receiver')));
		// var_dump($redis->subscribe(array('toto'), array($this, 'receiver')));
		// print_r($redis->pubSub("mt:CM_Site1_TRF"));
		die();
	}
}

