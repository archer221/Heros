<?php
/*
 * 这个类主要做linux的tick，为了延时存储数据库
 * 
 * */
set_time_limit(0);
ini_set('memory_limit', '512M');
require_once("lib/base.inc.php");

class liuxcrontab extends common{
	private static $exeindx = 0;
	function index()
	{
		$executeNum = 200;
		// 使用缓存
		echo '<pre>每条命令执行'.$executeNum.'条记录';
		$mc = cache("memcached", $this->cfg['memcache']);
		$backexeindx = liuxcrontab::$exeindx;
		for( $curexeindx = $backexeindx;$curexeindx < $backexeindx +$executeNum;$curexeindx++)
		{
			$sqlkey = 'sqlop'.$curexeindx;
			$singleop = json_decode($mc->get($sqlkey));
			if($singleop)
			{
				$sqltable = $singleop['sqltable'];
				$sqlsubopary = $singleop['sqlsubop'];
				if( $sqlsubopary )
				{
					$db_r = orm($this->cfg['mysql']['params'])->query();
					$db_r->addTable($sqltable)->exec($sqlsubopary);
					$mc->delete($sqlkey);
					liuxcrontab::$exeindx++;
				}
			}
		}

	}
}

$Server = new liuxcrontab();
$action = $_REQUEST['action'];
if(empty($action)){
	$action = 'index';
}
$Server->$action();
?>