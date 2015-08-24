<?php
//-------------------------------------------------------------------------------------------------
// One_Script_Node_Opencurlybraces
//-------------------------------------------------------------------------------------------------

class One_Script_Node_Opencurlybraces extends One_Script_Node_Abstract
{

	function execute( &$data, &$parent )
	{
		return "{";
	}

	function compile( $hash, &$parent )
	{
		return "?>{<?php\n";
	}

}

?>