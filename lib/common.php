<?php
class common
{   
	public $cfg = null;
	public $app = null;
	function __construct()
	{	
		global $cfg; 
		if( !$cfg ) 
		{
			require(DOC_ROOT . 'config/application.cfg.php');
			require(DOC_ROOT . 'config/database.cfg.php');
			require(DOC_ROOT . 'config/server.cfg.php');
		}
		$this->cfg = $cfg;
		//$this->app = new Application(); 
	}
	function __destruct(){}
}
?>