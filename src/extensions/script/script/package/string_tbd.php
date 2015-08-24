<?php
//------------------------------------------------------------------
// package request : functions to manipulate strings
//------------------------------------------------------------------

class oneScriptPackageString extends One_Script_Package
{
	function substr($string, $start, $length='')
	{
		if ( !$length ) {$length = strlen($string);}
		return substr($string, $start, $length);
	}

	function stristr ( $haystack, $needle, $before_needle=false )
	{
		return stristr ($haystack, $needle, $before_needle);
	}

	function strpos ( $haystack, $needle, $offset= 0 )
	{
		return strpos($haystack, $needle, $offset);
	}

	function strrpos ( $haystack, $needle, $offset= 0 )
	{
		return strrpos($haystack, $needle, $offset);
	}

	function nl2br ( $string )
	{
		return nl2br($string);
	}

	function strtolower   ( $string )
	{
		return strtolower($string);
	}

	function ucfirst   ( $string )
	{
		return ucfirst($string);
	}

	function trim( $str, $charlist )
	{
		return trim( $str, $charlist );
	}

	function rtrim( $str, $charlist )
	{
		return rtrim( $str, $charlist );
	}

	function ltrim( $str, $charlist )
	{
		return ltrim( $str, $charlist );
	}

	function str_replace( $search, $replace, $subject )
	{
		return str_replace( $search, $replace, $subject );
	}

	function cleanWordwrap( $string, $total = 250, $showHellip = true )
	{
		preg_match( '/^(.{0,' . intval( $total ) . '})(\s|$)/', $string, $matches );

		$wrapped = $matches[ 1 ];
		if( strlen( $string ) > strlen( $wrapped ) && $showHellip )
			$wrapped .= '&hellip;';

		return $wrapped;
	}
}
