<?php
/**
 * 配置文件－－服务器端相关数据配置文件
 * 
 * @author 祝清明
 */

//队列服务器的IP和端口
$queue_server_ip = array
(
	0 => array
	(
		'server' => array(
			array('host' => '','port' => 30001)
		),
		'mckey' 	=> 'SNG_Data_Mining',			//队列服务器key
		'timeout'	=> 30,			//脚本执行超时时间
		'thread'	=> 3,			//执行该队列的进程数
		'memory'	=> 1024000,		//内存限制大小 单位字节 bytes
	),
);

?>