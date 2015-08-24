<?php
//------------------------------------------------------------------
// package request : functions to access the request
//------------------------------------------------------------------

class oneScriptPackageRequest extends One_Script_Package
{
	public function get( $variable, $fromGlobal = "request", $default = NULL )
	{
		$from = false;
		switch($fromGlobal)
		{
			case 'get' :
				$from = $_GET;
				break;
			case 'post' :
				$from = $_POST;
				break;
			case 'cookie' :
				$from = $_COOKIE;
				break;
			case 'server' :
				$from = $_SERVER;
				break;
			case 'session' :
				$from = $_SESSION;
				break;
			case 'request' :
			default :
				$from = $_REQUEST;
				break;
		}

		$parts = explode('.', $variable);
		$value = $from;
		foreach($parts as $part)
		{
			if(isset($value[$part])) {
				$value = $value[$part];
			}
			else
			{
				$value = $default;
				break;
			}
		}

		return $value;
	}
}
