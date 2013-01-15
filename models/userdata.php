<?php
//require_once("../lib/base.inc.php");
require( dirname(__FILE__).'./'.'../lib/base.inc.php' );
class userdata extends common
{
	public $user_uuid = 0;
	public $user_accountid='';
	public $user_ticket = '';
	public $user_type=0;
	public $user_ip='127.0.0.1';
	public $user_createdate = '2013-01-01';
	public $user_lastactivedate = '2013-01-01';
	public $user_gmlevel =0;
	public $user_flag = 0;
	public $user_yellowflag = 0;
	public $user_yellowlevel = 0;
	public $user_gold = 0;
	public $user_paypoint = 0;
	public $user_godpoint = 0;
	public $user_InstanceMapDist = 0;
	public $user_SurviceLayer = 0;
	//public static $usertable = 'users';
	public function userdata()
	{	
		$this->__construct();
	}
	function __construct() {
		common::__construct();
	}
	public static function usertable()
	{
		return 'users';
	}
	public static function getSqlindex()
	{
		static $sqlindx = 0;
		$beforeadd = $sqlindx;
		$sqlindx++;
		return $beforeadd;
	}
	public static  function getFromMemchached( $data )
	{
		if( $data )
		{
			$ret = new userdata();
			$ret->user_uuid = $data['user_uuid'];
			$ret->user_accountid= $data['user_accountid'];
			$ret->user_ticket = $data['user_ticket'];
			$ret->user_type=$data['user_type'];
			$ret->user_ip= $data['user_ip'];
			$ret->user_createdate = $data['user_createdate'];
			$ret->user_lastactivedate = $data['user_lastactivedate'];
			$ret->user_gmlevel =$data['user_gmlevel'];
			$ret->user_flag = $data['user_flag'];
			$ret->user_yellowflag = $data['user_yellowflag'];
			$ret->user_yellowlevel = $data['user_yellowlevel'];
			$ret->user_gold = $data['user_gold'];
			$ret->user_paypoint = $data['user_paypoint'];
			$ret->user_godpoint = $data['user_godpoint'];
			$ret->user_InstanceMapDist = $data['user_InstanceMapDist'];
			$ret->user_SurviceLayer = $data['user_SurviceLayer'];
			return $ret;
		}
		return null;
	}
	
	public function updatesql()
	{
		$sql = '';
		$db_r = orm($this->cfg['MySqlCfg']['params'])->query();
		$db_r->addTable(userdata::usertable())
						->addValue('user_uuid', $this->user_uuid)
						->addValue('user_accountid', $this->user_accountid)
						->addValue('user_ticket', $this->user_ticket)
						->addValue('user_type', $this->user_type)
						->addValue('user_ip', $this->user_ip)
						->addValue('user_lastactivedate', $this->user_lastactivedate)
						->addValue('user_flag', $this->user_flag)
						->addValue('user_yellowlevel', $this->user_yellowlevel)
						->addValue('user_gold', $this->user_gold)
						->addValue('user_paypoint', $this->user_paypoint)
						->addValue('user_godpoint', $this->user_godpoint)
						->addValue('user_InstanceMapDist', $this->user_InstanceMapDist)
						->addValue('user_SurviceLayer', $this->user_SurviceLayer);
		$sql = $db_r->asSql();
		return $sql;
	}
	public function loadrole($account,$ticket,$time)
	{
		try {
			$db_r = orm($this->cfg['MySqlCfg']['params'])->query();
			$time = time();
			$db_r->addTable(userdata::usertable())->addWhere('user_accountid', $account);
			$retdata = $db_r->getValue($db_r->asSql());
			if( $retdata )
			{
				if( !empty($retdata) ){
					$retticket = $retdata[2];
					if( $retticket == $ticket )
					{
						$user_uuid = $retdata[0];
						$user_accountid= $retdata[1];
						$user_ticket = $retdata[2];
						$user_type=$retdata[3];
						$user_ip= $retdata[4];
						$user_createdate = $retdata[5];
						$user_lastactivedate = $retdata[6];
						$user_gmlevel =$retdata[7];
						$user_flag = $retdata[8];
						$user_yellowflag = $retdata[9];
						$user_yellowlevel = $retdata[10];
						$user_gold = $retdata[11];
						$user_paypoint = $retdata[12];
						$user_godpoint = $retdata[13];
						$user_InstanceMapDist = $retdata[14];
						$user_SurviceLayer = $retdata[15];
						return $this;
					}
				}
			}
			return null;
		} catch (Exception $e) {
		}
		
	}
	function __destruct(){}
	
}

?>