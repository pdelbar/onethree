<?php
//-------------------------------------------------------------------------------------------------
// oneScrOne_Script_Node_Do
//-------------------------------------------------------------------------------------------------

class One_Script_Node_Do extends One_Script_Node_Abstract
{
	function execute( &$data, &$parent )
	{
		$expr = $this->evaluateExpression( $this->data, $data, $parent );
		return "";
	}

}
