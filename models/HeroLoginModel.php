<?php
require_once(dirname(__FILE__).'./'.'../config/ServerInfoCode.php');
require_once("userdata.php");
class HeroLoginModel extends common
{
	public function login( $account,$ticket,$time )
	{
		$mc = cache("memcached", $this->cfg['memcache']);
		//$roledata = json_decode($mc->get($account));//userdata::getFromMemchached();
		$roledata = new userdata();
		$roledata = $roledata->loadrole($account, $ticket, $time);
		if( $roledata )
		{
			$roledata->user_lastactivedata = date('Y-m-d H:i:s',time());
			$mc->set($account,json_encode($roledata));
			$mc->set('sqlop'.userdata::getSqlindex(),json_encode($roledata->updatesql()));
			return ServerInfoCode::$loginok;
		}
		else {
			return ServerInfoCode::$loginerro;
		}
		return ServerInfoCode::$loginerro;
	}
	function __construct()
	{
		common::__construct();
	}
	function __destruct(){}
}

?>