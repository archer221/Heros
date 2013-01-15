<?php
/**
 * 配置文件－－数据库配置文件
 * @author 祝清明
 */

//Memcached分组相关配置信息
$cfg['memcache'] = array(
    array('host' => '127.0.0.1','port' => 11211),
    array('host' => '127.0.0.1','port' => 10002)    
);

//Memcached分组相关配置信息
$cfg['memcacheq'] = array(
    array('host' => '127.0.0.1','port' => 30001)    
);

$cfg['hjgame'] = array(
	'params'   => array('driver'=> 'mysql', 'host'=> '', 'name'=> '', 'user'=> '','password'=> ''),
	'options'  => array('persistent'=> false, 'tablePrefix' => '','charset'=>'latin1')
);		



$cfg['test'] = array(
	'params'   => array('driver'=> 'oracle', 'host'=> '', 'name'=> '', 'user'=> '', 'password'=> ''),
	'options'  => array('persistent'=> false, 'tablePrefix' => '','charset'=>'utf8')
);	

$cfg['MySqlCfg'] = array(
	'params'   => array('driver'=> 'mysql', 'host'=> '127.0.0.1', 'name'=> 'heros', 'user'=> 'root', 'password'=> '1234'),
	'options'  => array('persistent'=> false, 'tablePrefix' => '','charset'=>'gbk')
);	
