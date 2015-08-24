<?php
//-------------------------------------------------------------------------------------------------
// nsNodeEval
//-------------------------------------------------------------------------------------------------

class One_Script_Node_Evaluate extends One_Script_Node_Abstract
{

	function execute( &$data, &$parent )
	{
		//print "<br>Expression = " . $this->data;
		$expr = $this->evaluateExpression( $this->data, $data, $parent );
		//print "<br>Evaluate to $expr";

		return $expr;
	}

	function compile( $hash, &$parent )
	{
		if (is_null($this->parsedExpression))
		{
			$expr = $this->parseExpression( $expression, $data );
			if ($this->error) return $this->error;
			$this->parsedExpression = $expr;
		}
		else
		{
			$expr = $this->parsedExpression;
		}
		return '  echo ' . $expr . ';';
	}

}

?>