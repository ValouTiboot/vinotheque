<?php

class RedisConnect
{
	public $redis;

	private static $host = 'vps364479.ovh.net';
	
	private static $port = '16379';
	
	private static $pass = '8Fy&,k5XnQy4WV<8c45>z*{Q';

	public function __construct()
	{
		$this->redis = new Redis();
	}

	public function connect()
	{
		try{
			if( $socket = fsockopen( self::$host, self::$port, $errorNo, $errorStr )){
			  	if( $errorNo ){
			  		throw new RedisException("Socket cannot be opened " . $errorStr);
			  	}	
			}
		}catch( Exception $e ){
		  echo $e -> getMessage();
		}

		$this->redis->connect(self::$host, self::$port, '30');
		$this->redis->auth(self::$pass);

		return $this->redis;
	}

	public function close()
	{
		$this->redis->close();
	}
}