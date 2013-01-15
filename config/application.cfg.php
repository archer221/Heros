<?php
/**
 * 配置文件－－主配置文件
 * @package main
 * @subpackage configure
 * @author 祝清明
 */

//调试开关
define('DEBUG', false);

//当前发布的静态压缩的文件版本号
define('MIN_VERSION', 1);

//设置时区
date_default_timezone_set('Asia/Chongqing');

//在线活动时间长度,单位：秒
define('ONLINE_TIME_GAP', 600);

//统一分页长度
define('PAGE_NAV_SIZE', 8);

//初始化配置变量
$cfg = array();

$cfg['path']['conf'] = dirname(__FILE__) . '/';
$cfg['path']['root'] = dirname($cfg['path']['conf']) . '/';
$cfg['path']['xml'] = $cfg['path']['root'] . 'xml/';

//加载数据库配置文件
include($cfg['path']['conf'] .'database.cfg.php');

// 配置是否统计表每天生成一个，否则只有一个表
$cfg['db_stat_day'] = false;

//页面信息
$cfg['page'] = array
(
	'charset'			=> 'UTF-8',
	'contentType'		=> 'text/html',
	'title'			=> '',
	'cached'			=> true,
	'engine'			=> 'smarty',
	'css'				=> array(),
	'js'				=> array(),
);
	
//风格
$cfg['theme'] = array
(
	'root'			=> '',
	'current'			=> '',
);
	
//其他路径
$cfg['path'] = array_merge($cfg['path'], array
	(
	'lib'				=> $cfg['path']['root'] . 'lib/',
	'class'			=> $cfg['path']['root'] . 'lib/',
	'common'			=> $cfg['path']['root'] . 'lib/',
	'cache'			=> $cfg['path']['root'] . 'cache/',
	'upload'			=> $cfg['path']['root'] . 'public/upload/',
	'fonts'			=> $cfg['path']['root'] . 'public/fonts/',
	'temp'			=> $cfg['path']['root'] . 'public/temp/',
	'module'		=> $cfg['path']['root'] . 'modules/',
	)
);
    
//cache
$cfg['cache'] = array
(
	'root'			=> $cfg['path']['cache'],  // engine=memcached 时为服务器地址 
	'engine'			=> 'file', //file|memcached
	'port'			=> 11211, //engine=memcached 时才有意义 
	'timeout'			=> 60, //engine=memcached 时才有意义 
);
?>