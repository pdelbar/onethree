<?php
//-------------------------------------------------------------------------------------------------
// One_Script_Node_If
//
// Represents a conditional node.
//-------------------------------------------------------------------------------------------------

class One_Script_Node_If extends One_Script_Node_Abstract
{

	function execute( &$data, &$parent )
	{
		// JL 13MAR2009 - fix "{if (x or y)}" and "{if (x and y)}" constructions
		$pattern = array("/ or /i", "/ and /i");
		$replacement = array(" || ", " && ");
		$this->data = preg_replace($pattern, $replacement, $this->data);


		$condition = $this->evaluateExpression( $this->data, $data, $parent );
		// if it is a string, it is an error message ...

		//echo "IF ($condition)";
		if ($condition)
			return $this->executeChain( $this->chain, $data, $parent );
		else
			return $this->executeChain( $this->altChain, $data, $parent );
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

		$s = '  if (' . $expr . ") {\n";
		foreach ($this->chain as $n) $s .= $n->compile( $hash, $parent );
		$s .= "    } else {\n";
		foreach ($this->altChain as $n) $s .= $n->compile( $hash, $parent );
		$s .= "  }\n";

		return $s;
	}

}

?>