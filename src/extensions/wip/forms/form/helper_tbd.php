<?php
/**
 * Class that contains some helper functions
 *


  * @TODO review this file and clean up historical code/comments
ONEDISCLAIMER

 **/
Class One_Form_Helper
{
	/**
	 * Translate all non-standard characters to their standard characters
	 *
	 * @param string $str
	 * @return string
	 */
	public static function iso2ascii($str)
	{
		$tmp = utf8_decode($str);

		$from = utf8_decode("ŠŒŽšœžŸ¥µÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝßàáâãäåæçèéêëìíîïðñòóôõöøùúûüýÿ");
		$to   = "SOZsozYYuAAAAAAACEEEEIIIIDNOOOOOOUUUUYsaaaaaaaceeeeiiiionoooooouuuuyy";

		$tmp = strtr($tmp, $from, $to);

		return $tmp;
	}

	/**
	 * Process a string to a string without whitespaces or special characters
	 *
	 * @param string $input
	 * @return string
	 */
	public static function mangle( $input )
	{
		// Create an array with words that need to be stripped
		$elim  = "en:van:bij:tot:u:uw:de:een:door:voor:het:in:is:et:la:le:un:une:du:n:est:ce:ca:par:les:d:a:l:op:sur:des:mijn:mon";
		$kill = explode( ":", $elim );

 		$ss = preg_replace(array("/&amp;/", "/&/", "/\?/", "/'/",'/"/', "/`/", "/'/", "/â€™/", "/â€™/", "/’/")," ",$input);
		$ss = preg_replace("/@/"," at ",$ss);

		// iso2ascii must be done prior to strtolower to avoid problems due to encoding
		$ss = One_Form_Helper::iso2ascii($ss);

		// 20080922 TR removed lowercasing because of saving-problems with tablenames with uppercase letters
		// $ss = strtolower($ss)." ";

		// Replace unacceptable characters
		$ss = preg_replace( "@&#(\d+);@e", 'chr(\1)', $ss);
		$ss = preg_replace( "@(&\w+;)@e", 'html_entity_decode("\1")', $ss);

		$ss = preg_replace("/\W/"," ",$ss);

		// Strip earlier defined words
		foreach( $kill as $w)
		{
			$ss = preg_replace( "/(^|\s)$w\s/"," ",$ss);
		}

		$ss = preg_replace("/(\s)+/","_",trim($ss));

		if (substr($ss,strlen($ss)-1,1) == "_") $ss = substr($ss,0,strlen($ss)-1);

		return $ss;
	}

	/**
	 * Creates a token of the given length
	 *
	 * @param int $length
	 * @return string
	 */
	public static function createToken( $length = 32 )
	{
		static $chars	=	'0123456789abcdef';
		$max			=	strlen( $chars ) - 1;
		$token			=	'';
		$name 			=  session_name();
		for( $i = 0; $i < $length; ++$i ) {
			$token .=	$chars[ (rand( 0, $max )) ];
		}

		return md5($token.$name);
	}
}
