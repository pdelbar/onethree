<?php
//-------------------------------------------------------------------------------------------------
// oneScriptNodeWhile
//-------------------------------------------------------------------------------------------------

class oneScriptNodeWhile extends One_Script_Node_Abstract
{

	function execute( &$data, &$parent )
	{
		$s = "";

		$max = 10000;
		while ($this->evaluateExpression( $this->data, $data, $parent )) {
			if (!$max--) return $s;
//			$s .= '*';
			$s .= $this->executeChain( $this->chain, $data, $parent );
		}

		return $s;
	}

}

?>