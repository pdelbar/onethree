<?php
//-------------------------------------------------------------------------------------------------
// One_Script_Node_Closecurlybraces
//-------------------------------------------------------------------------------------------------

class One_Script_Node_Closecurlybraces extends One_Script_Node_Abstract
{

	function execute( &$data, &$parent )
	{
		return "}";
	}

	function compile( $hash, &$parent )
	{
		return "?>}<?php\n";
	}

}

?>