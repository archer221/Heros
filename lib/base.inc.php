<?php
/**
 * 通用全局函数
 * @package lib
 * @author 祝清明
 */

define("DOC_ROOT", dirname( dirname(__FILE__)) . '/');

require(DOC_ROOT . 'config/application.cfg.php');
require(DOC_ROOT . 'config/database.cfg.php');
require(DOC_ROOT . 'config/server.cfg.php');
require(DOC_ROOT . 'lib/common.php');
// 全局缓存
$global_pool = array();

/**
 * 导入文件
 * @param string $classString 导入文件路径字符串,可以用"."代替"/"
 * @param string $fileType 导入文件类型的扩展名(带"."号),也可以是class/inc(简写方式)
 * @return Exception 如果导入成功则返回true，否则返回异常对象
 * 
 * @example 
 * 		import('interface.Account') => include_once('interface/Account.class.php');
 */
function import($classString, $fileType = 'class') 
{
	$filename = DOC_ROOT . strtr($classString, '.', '/');
	switch ($fileType) {
		//导入类文件 
		case 'class': $filename .= '.class.php'; break;
		//导入包含文件
		case 'inc': $filename .= '.inc.php'; break;
		//自定义导入文件的扩展名
		default: $filename .= $fileType; break;
	}
	if (is_file($filename)) {
		include_once($filename);
	} else {
		exit('file "' . $filename . '" is not found.');
	}
}


/**
 * 导入模块文件
 * @param string $classString 导入文件路径字符串,可以用"."代替"/"
 * @param string $fileType 导入文件类型的扩展名(带"."号),也可以是class/inc(简写方式)
 * @return Exception 如果导入成功则返回true，否则返回异常对象
 * 
 * @example 
 * 		importModule('gapi.Account') => include_once('modules/Account.class.php');
 */
function importModule($classString, $fileType = 'class') 
{
	$filename = DOC_ROOT . 'modules/' . strtr($classString, '.', '/');
	switch ($fileType) 
	{
		//导入类文件 
		case 'class': $filename .= '.class.php'; break;
		//导入包含文件
		case 'inc': $filename .= '.inc.php'; break;
		//自定义导入文件的扩展名
		default: $filename .= $fileType; break;
	}
	
	if (is_file($filename))
		include_once($filename);
	else 
		exit('file "' . $filename . '" is not found.');
}


/**
 * 根据用户的id来散列获取分表名称
 * 
 * @param String $id 记录id
 * @return String 0x00 - 0xFF
 */
function table($id)
{
	$id = (string)$id;
    //return sprintf('%02x', intval($id) % 256);
	$len = 0;
	for ($i = 0; $i < strlen($id); $i++)
	{
		$len += pow(ord($id[$i]), 3);
	}
	$len = sprintf("%02x", $len % 256);
	return $len;
}


/**
 * 根据用户的id来散列获取分库名称
 * 
 * @param integer $id 记录id
 * @return String 0x00-0x08
 */
function database($id)
{
	$id = (string)$id;
	if (!empty($id)) 
	{ //如果有用户id，则要拆表
		$len = 0;
		for ($i = 0; $i < strlen($id); $i++)
		{
			$len += pow(ord($id[$i]), 3); 
		}
		$len = sprintf("%02x", $len % 8);
	}
	return $len;
}

/**
 * 抛出异常处理
 *
 * @param string $msg
 * @param string $code
 */
function throwException($msg, $code) {
	trigger_error($msg . '(' . $code . ')');
}

/**
 * 返回application的orm对象,第一次调用时会自动根据配置文件自动创建实例
 * 如：$db_user_r = orm($cfg['db_user_r'])->query();
 * 
 * @param string $params 连接参数
 * @param array $options 选项
 * @return OrmQuery
 */
function orm($params=NULL, $options=NULL) {
	global $global_pool, $cfg;
	
	if (empty($global_pool['orm'])) {
		$global_pool['orm'] = array();
	}
	
	$key = md5(serialize($params)); 
	
	if (!isset($global_pool['orm'][$key])) {
		import("lib.orm.OrmQuery", "class");
		
		($params === NULL) && $params = $cfg['MySqlCfg']['params'];
		($options === NULL) && $options = $cfg['MySqlCfg']['options'];
		$global_pool['orm'][$key] = new OrmQuery($params, $options);
	}
	
	return $global_pool['orm'][$key];
}

/**
 * 返回application的cache对象,第一次调用时会自动根据配置文件自动创建实例.
 * 如：$mc = cache("memcached", $cfg['memcache']);
 * 
 * @param string $engine Page引擎,(memcached|eacache|filecache)默认为memcached
 * @return Memcached
 */
function cache($engine=NULL, $path=NULL, $port=NULL, $timeout=NULL) {
	global $global_pool, $cfg;
	
	if (empty($global_pool['cache'])) {
		$global_pool['cache'] = array();
	}
	$key = md5($engine . '_' . serialize($path) . '_' . $port); 
	if (!isset($global_pool['cache'][$key])) 
	{
		($engine === NULL) && $engine = "memcached";
		($path === NULL) && $path = $cfg['memcache'];
		($port === NULL) && $port = 11211;
		($timeout === NULL) && $timeout = 60;
		
		$engine = strtolower($engine);
		switch ($engine)
		{
			case 'memcached':
				$className = 'Memcached';
				break;
			case 'eacache':
				$className = 'eAcache';
				break;
			default:
				$className = 'FileCache';
		}
		
		import('lib.cache.' . $className);
		
		$global_pool['cache'][$key] = new $className($path, $port, $timeout);
	}
	
	return $global_pool['cache'][$key];
}

/**
 * 返回application的log对象
 * 
 * @param string $path 日志路径,在未指定文件名时,将自动使用路径的最后一部分作为文件名
 * @param array|string $data 日志数据
 * @param string $sep 日志数据分割符
 * @return Log
 */
function getLog($path = "", $data = array(), $sep = "\t") {
	import('lib.Log');
	return new Log($path, $data, $sep);
}

/**
 * 根据输入的文本返回与当前设置相同的版本的文本
 *
 * @param String	$text
 * @return String	返回设置的语言的相应的文本
 */
function _T($text)
{
	global $cfg;
	
	//检查当前的语言是否已经加载
	if (!empty($GLOBALS['lang'][$cfg['lang']][$text]))
		return $GLOBALS['lang'][$cfg['lang']][$text];
		
	$cache = cache('FileCache');
	$lang = $cache->get('lang/' . $cfg['lang']);
	if (empty($lang) || !is_array($lang))
	{
		$db = orm($cfg['db_w'])->query();
		$data = $db->addTable('Lang')->addField($cfg['lang'])->addField('Key')->addField('Id')->getArray();
		if (!empty($data) && is_array($data))
		{
			$lang = array();
			foreach ($data as $row)
			{
				$lang[$row['Key']] = $row[$cfg['lang']];
			}
			
			$cache->set('lang/' . $cfg['lang'], $lang, 0);
			$GLOBALS['lang'][$cfg['lang']] = $lang;
		}
	}
	else
		$GLOBALS['lang'][$cfg['lang']] = $lang;
	
	return empty($lang[$text])? $text : $lang[$text];
}
/**
	 * 获取客户端IP
	 * @return string
	 * @static
	 */
function getIp() {
		if (isset($_SERVER)) {
		if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		$realip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
		$realip = $_SERVER['HTTP_CLIENT_IP'];
		} else {
		$realip = $_SERVER['REMOTE_ADDR'];
		}
		} else {
		if (getenv("HTTP_X_FORWARDED_FOR")) {
		$realip = getenv( "HTTP_X_FORWARDED_FOR");
		} elseif (getenv("HTTP_CLIENT_IP")) {
		$realip = getenv("HTTP_CLIENT_IP");
		} else {
		$realip = getenv("REMOTE_ADDR");
		}
		}
return $realip;
	}
?>