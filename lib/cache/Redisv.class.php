<?php 

/**
 *  redis 类
 *
 * @package lib
 * @subpackage plugins.cache
 * @author 祝清明
 */

require_once 'Redisent.class.php';

class Redisv
{
	/**
	 * redis服务器缓存对象集合
	 * @var array
	 * @access private
	 */
	private $redises;
	
	/**
	 * Redis服务器哈希环
	 * @var array
	 * @access private
	 */
	private $ring;
	
	/**
	 * 哈希环上的节点
	 * @var array
	 * @access private
	 */
	private $nodes;
	
	/**
	 * 每个服务器哈希环上节点个数
	 * @var integer
	 * @access private
	 */
	private $replicas = 128;
	
	
	/**
	 * @var string $prefix 变量前缀
	 */
	var $prefix = '';
	
	
	/**
     * 数据查询的统计
     *
     * @var Array
     */
    static public $querys = array();
    
    
    /**
     * 数据缓存沲
     *
     * @var Array
     */
    static public $data = array();
	    
    
	/**
	 * 构造函数(兼容PHP4)
	 * @param string $host redis 服务器的主机名或IP地址
	 * @param int $port 端口号
	 * @param int $timeout 超时时间
	 */
	function Redisv($host = 'localhost', $port = 6379, $timeout = 300) 
	{
		if (strpos($host, '$') !== false)
			list($host, $this->prefix) = explode('$', $host, 2);
		
		($this->prefix) && $this->prefix .= '_';
    	$this->__construct($host, $port, $timeout);
    }
	
    
	/**
	 * 构造函数
	 * @param string $host Redis 服务器的主机名或IP地址或者为服务器组相关信息
	 * @param int $port 端口号
	 * @param int $timeout 超时时间
	 */
	function __construct($host = 'localhost', $port = 6379, $timeout = 300) 
	{    	
    	$host = is_array($host) ? $host : array(array('host' => $host, 'port' => $port));    	
    	
    	$this->ring = array();
    	
    	//如果是服务器分组则添加所有的服务器分组
		foreach ($host as $key=>$server) 
		{	
			$this->redises[$key] = new Redisent($server['host'], $server['port']);		
 			for ($replica = 1; $replica <= $this->replicas; $replica++) 
 			{
				$this->ring[sprintf("%u", crc32($server['host'].':'.$server['port'].'-'.$replica))] = $this->redises[$key];
			}
		}
		ksort($this->ring, SORT_NUMERIC);
		$this->nodes = array_keys($this->ring);
    }

    
    /**
	 * __call
	 * @param string $name  收到的redis命令名
	 * @param mix $args     命令详细信息
	 * @return              命令执行
	 */
	function SelectRedis($key = NULL)
	{	
		//寻找服务器
		if ($key == NULL)
		{
			$redis = $this->redises[0];
		}
		else 
		{
			$node = $this->nextNode(sprintf("%u", crc32($key)));
			$redis = $this->ring[$node];	
		}

		//执行命令
    	return $redis;
	}

	/**
	 * 寻找正确的节点
	 * @param integer $needle Redis命令的哈希值
	 * @return Redis  与哈希值对应的redis服务器节点
	 */
	private function nextNode($needle) 
	{
		$haystack = $this->nodes;
		while (count($haystack) > 2) 
		{
			$try = floor(count($haystack) / 2);
			if ($haystack[$try] == $needle) 
			{
				return $needle;
			}
			if ($needle < $haystack[$try]) 
			{
				$haystack = array_slice($haystack, 0, $try + 1);
			}
			if ($needle > $haystack[$try]) 
			{
				$haystack = array_slice($haystack, $try + 1);
			}
		}
		return array_pop($haystack);
	}
		
    
	/**
	 * 在cache中设置键为$key的项的值，如果该项不存在，则新建一个项
	 * @param string $key 键值
	 * @param mix $val 值
	 * @return bool 如果成功则返回 TRUE，失败则返回 FALSE。
	 * @access public
	 */
    function set($key, $val) 
    {
		global $global;
		$key = $this->prefix . $key;
		
		$s_val = serialize($val);
		
		if (DEBUG)
			self::$querys[] = "set " . $key . ' ' . $s_val;
				
		return $this->SelectRedis($key)->set($key, $s_val);
	}
	
	
	/**
	 * 在cache中获取键为$key的项的值
	 * @param string $key 键值
	 * @return mix 如果该项不存在，则返回false
	 * @access public
	 */
    function get($key) 
    {
    	global $global;
    	$key = (empty($this->prefix)) ? $key : $this->prefix . $key;
		
		$s_key = is_array($key) ? serialize($key) : $key;

		if (DEBUG)
			self::$querys[] = "get " . (is_array($key) ? implode(',', $key) : $key);
		       		
		$var = $this->SelectRedis($s_key)->get($s_key);
		return unserialize($var);
	}
	
	
	/**
	 * 在MC中获取为$key的自增ID
	 *
	 * @param string $key	 自增$key键值
	 * @param integer $count 自增量,默认为1
	 * @return 				 成功返回自增后的数值,失败返回false
	 */
	function incr($key, $count = 1) 
	{
		return $this->SelectRedis($key)->incrby($key, $count);
	}
	
	
	/**
	 * 清空cache中所有项
	 * @return 如果成功则返回 TRUE，失败则返回 FALSE。
	 * @access public
	 */
    function flushAll() 
    {
		return $this->SelectRedis()->flushall();
	}
	
	
	/**
	 * 删除在cache中键为$key的项的值
	 * @param string $key 键值
	 * @return 返回删除的数量。
	 * @access public
	 */
    function delete($key) 
    {
		return $this->SelectRedis($key)->del($key);
	}
	
	
	/**
	 * 在cache中向键为$key的list的头部插入
	 * @param string $key 键值
	 * @param mix $val
	 * @return 如果成功则返回 TRUE，失败则返回 FALSE。
	 * @access public
	 */
    function lPush($key, $val) 
    {
    	$s_val = serialize($val);
    	
    	if (DEBUG)
			self::$querys[] = "lPush " . $key . ' ' . $s_val;
				
		return $this->SelectRedis($key)->lpush($key, $s_val);
	}
	
	
	/**
	 * 在cache中向键为$key的list的尾部插入
	 * @param string $key 键值
	 * @param mix $val
	 * @return 如果成功则返回 TRUE，失败则返回 FALSE。
	 * @access public
	 */
    function rPush($key, $val) 
    {
    	$s_val = serialize($val);
    	
    	if (DEBUG)
			self::$querys[] = "rPush " . $key . ' ' . $s_val;
    	
		return $this->SelectRedis($key)->rpush($key, $s_val);
	}
	
	
	/**
	 * pop在cache中向键为$key的list的头部
	 * @param string $key 键值
	 * @return 如果成功则返回 pop的值，失败则返回 FALSE。
	 * @access public
	 */
    function lPop($key) 
    {
    	$result = $this->SelectRedis($key)->lpop($key);
		if (!$result)
		{
			return false;		
		}
		return unserialize($result);
	}
	
	
	/**
	 * pop在cache中向键为$key的list的尾部
	 * @param string $key 键值
	 * @return 如果成功则返回 pop的值，失败则返回 FALSE。
	 * @access public
	 */
    function rPop($key) 
    {
    	$result = $this->SelectRedis($key)->rpop($key);
		if (!$result)
		{
			return false;		
		}
		return unserialize($result);
	}
	
	
	/**
	 * 查看在cache中向键为$key的list长度
	 * @param string $key 键值
	 * @return 如果成功则返回 长度，失败则返回 FALSE。
	 * @access public
	 */
    function lSize($key) 
    {
		return $this->SelectRedis($key)->llen($key);
	}
	
	
	/**
	 * 取得在cache中键为$key的list的一定范围
	 * @param string $key 键值
	 * @param int $start $end 范围的索引，负数代表从尾端数
	 * @return 如果成功则返回 所截取的范围，失败则返回 FALSE。
	 * @access public
	 */
    function lGetRange($key, $start, $end) 
    {	
    	$list = $this->SelectRedis($key)->lrange($key, $start, $end);
    	foreach ($list as &$val)
    	{
    		$val = unserialize($val);
    	}
    	return $list;
	}
	
	
	/**
	 * 截断在cache中键为$key的list为指定范围
	 * @param string $key 键值
	 * @param int $start $end 范围的索引，负数代表从尾端数
	 * @return 如果成功则返回 TRUE，失败则返回 FALSE。
	 * @access public
	 */
    function listTrim($key, $start, $end) 
    {
    	return $this->SelectRedis($key)->ltrim($key, $start, $end);
	}
	
	
	/**
	 * 删除在cache中键为$key的list中指定项
	 * @param string  $key    键值
	 * @param mix     $val    value值
	 * @param integer $count  删除的数量，负数从尾端开始删除，0表示删除所有值为$val项
	 * @return 返回删除的数量。
	 * @access public
	 */
    function lRemove($key, $val, $count = 0) 
    {
    	$s_val = serialize($val);
    	return $this->SelectRedis($key)->lrem($key, $count, $s_val);
	}
	
	
	/**
	 * 取得在cache中键为$key的list中指定索引项
	 * @param string  $key    键值
	 * @param string  $index  list中索引，负数为从尾端开始
	 * @return 如果成功则返回 索引的值，失败则返回 FALSE。
	 * @access public
	 */
    function lGet($key, $index) 
    {
    	$result = $this->SelectRedis($key)->lindex($key, $index);
    	if (!$result)
		{
			return false;		
		}
		return unserialize($result);
	}
	
	
	/**
	 * 检查在cache中键为$key的项是否存在
	 * @param string  $key    键值
	 * @return 如果存在则返回 TRUE，不存在则返回 FALSE。
	 * @access public
	 */
    function exists($key) 
    {
    	return $this->SelectRedis($key)->exists($key);
	}
	
	
	/**
	 * 取得在cache中键为$key的项的类型
	 * @param string  $key    键值
	 * @return 返回类型，为：Redis::REDIS_STRING、Redis::REDIS_LIST、Redis::REDIS_SET、Redis::REDIS_NOT_FOUND。
	 * @access public
	 */
    function type($key) 
    {
    	return $this->SelectRedis($key)->type($key);
	}
	
	/**
	 * ping
	 * @return 返回类型，redis正常连接返回PONG，否则出错。
	 * @access public
	 */
    function ping() 
    {
    	foreach ($this->redises as $redis)
    	{
    		$ping = @$redis->ping();
    		if (!$ping)
    		{
    			return false;
    		}
    	}
    	return true;
	}
}
?>