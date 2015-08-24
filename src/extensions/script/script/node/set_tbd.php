<?php
//-------------------------------------------------------------------------------------------------
// One_Script_Node_Set
//-------------------------------------------------------------------------------------------------

class One_Script_Node_Set extends One_Script_Node_Abstract
{

	function execute( &$data, &$parent )
	{
		//print "Expression = " . $this->data;
		$expr = $this->evaluateExpression( $this->data, $data, $parent );
		$toSet = $this->args;

		// if it is a string, it is an error message ...

//		print "Setting $toSet to $expr";
		$parent->variables[$toSet] = $expr;
		$data[$toSet] = $expr;

		return "";
	}

}

?>